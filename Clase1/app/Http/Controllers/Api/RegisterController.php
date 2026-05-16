<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Models\User;
use App\Models\PendingUser;
use App\Models\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Services\EmailService; // servicio dedicado para correos
use App\Services\SecureKeyService;

class RegisterController extends Controller
{
    protected EmailService $emailService;
    protected SecureKeyService $secureKeyService;

    public function __construct(EmailService $emailService, SecureKeyService $secureKeyService)
    {
        $this->emailService = $emailService;
        $this->secureKeyService = $secureKeyService;
    }

   public function register(Request $request)
{
    Log::info('=== INICIO REGISTRO SIMPLE ===');
    
    try {
        $validated = $request->validate([
            'nombres' => 'required|string|max:191',
            'apellidos' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'secure_email' => 'required|email|max:191|different:email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ]);

        $existingPending = PendingUser::where('email', $validated['email'])->first();
        if ($existingPending) {
            if ($existingPending->email_verification_expires_at &&
                Carbon::now()->isAfter($existingPending->email_verification_expires_at)) {
                $existingPending->delete();
            } else {
                return response()->json([
                    'message' => 'El correo ya está pendiente de verificación.',
                ], 422);
            }
        }

        if (User::where('email', $validated['email'])->exists()) {
            return response()->json([
                'message' => 'El correo ya está registrado',
            ], 422);
        }

        $token = Str::random(64);
        $statusInactivo = DB::table('user_status')->where('nombre', 'inactivo')->value('id');
        
        $pending = PendingUser::create([
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'email' => $validated['email'],
            'secure_email' => $validated['secure_email'],
            'password' => Hash::make($validated['password']),
            'email_verification_token' => $token,
            'email_verification_expires_at' => Carbon::now()->addHours(24),
            'status_id' => $statusInactivo ?? 2,
        ]);

        Log::info('Usuario pendiente creado: ' . $pending->email);

        // Responder inmediatamente al frontend
        $response = response()->json([
            'message' => 'Usuario registrado. Verifica tu correo electrónico.',
            'pending_id' => $pending->id,
            'email' => $pending->email,
        ], 201);

        // Enviar email DESPUÉS de responder (evita timeout)
        $emailService = $this->emailService;
        $pendingId = $pending->id;
        $pendingEmail = $pending->email;
        
        register_shutdown_function(function() use ($emailService, $pending, $token, $pendingEmail) {
            try {
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }
                $emailService->sendVerification($pending, $token);
                Log::info('Email enviado a: ' . $pendingEmail);
            } catch (\Exception $e) {
                Log::warning('Error enviando email: ' . $e->getMessage());
            }
        });

        return $response;

    } catch (\Exception $e) {
        Log::error('Error en registro: ' . $e->getMessage());
        
        return response()->json([
            'message' => 'Error al registrar usuario',
            'error' => app()->isLocal() ? $e->getMessage() : 'Error interno del servidor',
        ], 500);
    }
}
    /**
     * Confirmar email del usuario
     */
    public function verifyEmail(Request $request)
    {
        try {
            $token = $request->input('token');

            // Primero buscar en usuarios pendientes
            $pending = PendingUser::where('email_verification_token', $token)->first();

            if ($pending) {
                if ($pending->email_verification_expires_at &&
                    Carbon::now()->isAfter($pending->email_verification_expires_at)) {
                    return response()->json([
                        'message' => 'Token de verificación expirado',
                    ], 422);
                }

                // Crear registro definitivo en users
                // Obtener el ID del status 'activo'
                $statusActivo = DB::table('user_status')->where('nombre', 'activo')->value('id');
                
                $user = User::create([
                    'nombres' => $pending->nombres,
                    'apellidos' => $pending->apellidos,
                    'email' => $pending->email,
                    'secure_email' => $pending->secure_email,
                    'password' => $pending->password,
                    'email_verified_at' => Carbon::now(),
                    'secure_key_hash' => $this->secureKeyService->buildSecureKeyHash($pending->email, $pending->secure_email),
                    'secure_key_generated_at' => Carbon::now(),
                    'status_id' => $statusActivo ?? 1,
                ]);

                // borrar pendiente
                $pending->delete();

                return response()->json([
                    'message' => 'Email verificado exitosamente',
                    'user' => $user,
                    'secure_key_generated' => true,
                ], 200);
            }

            // si no está en pendientes, quizá ya existe un usuario
            $user = User::where('email_verification_token', $token)->first();
            if (!$user) {
                return response()->json([
                    'message' => 'Token de verificación no encontrado',
                ], 404);
            }

            if ($user->email_verification_expires_at &&
                Carbon::now()->isAfter($user->email_verification_expires_at)) {
                return response()->json([
                    'message' => 'Token de verificación expirado',
                ], 422);
            }

            $user->update([
                'email_verified_at' => Carbon::now(),
                'email_verification_token' => null,
                'email_verification_expires_at' => null,
            ]);

            return response()->json([
                'message' => 'Email verificado exitosamente',
                'user' => $user,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en verificación de email: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al verificar email',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reenviar correo de verificación
     */
    public function resendVerification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Email inválido',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // buscar en pendientes primero
            $pending = PendingUser::where('email', $request->input('email'))->first();

            if ($pending) {
                $token = Str::random(64);
                $pending->update([
                    'email_verification_token' => $token,
                    'email_verification_expires_at' => Carbon::now()->addHours(24),
                ]);
                $this->emailService->sendVerification($pending, $token);

                $response = [
                    'message' => 'Correo de verificación reenviado',
                ];
                if (app()->isLocal() || app()->environment('testing') || config('mail.default') === 'log') {
                    $response['verification_url'] = env('APP_FRONTEND_URL', 'http://localhost:5174') .
                                                 '/verify-email?token=' . $pending->email_verification_token;
                }
                return response()->json($response, 200);
            }

            // si no hay pendiente, quizás ya exista el usuario final
            $user = User::where('email', $request->input('email'))
                        ->whereNull('email_verified_at')
                        ->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado o ya verificado',
                ], 404);
            }

            $token = Str::random(64);
            $user->update([
                'email_verification_token' => $token,
                'email_verification_expires_at' => Carbon::now()->addHours(24),
            ]);

            $this->emailService->sendVerification($user, $token);

            $payload = [
                'message' => 'Correo de verificación reenviado',
            ];
            if (app()->isLocal() || app()->environment('testing') || config('mail.default') === 'log') {
                $payload['verification_url'] = env('APP_URL', 'http://localhost') .
                                             '/verify-email?token=' . $token;
            }
            return response()->json($payload, 200);

        } catch (\Exception $e) {
            Log::error('Error al reenviar verificación: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al reenviar correo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}