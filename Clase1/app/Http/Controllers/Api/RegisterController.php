<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PendingUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Services\EmailService; // servicio dedicado para correos

class RegisterController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        // inyectar el servicio de correo para poderlo reemplazar en tests si
        // fuese necesario, o cambiar su implementación sin tocar el
        // controlador.
        $this->emailService = $emailService;
    }

    /**
     * Registrar nuevo usuario (sin confirmar email aún)
     */
    public function register(Request $request)
    {
        try {
            // Validación de datos (aún no se escribe en `users`)
            $validator = Validator::make($request->all(), [
                'nombres' => 'required|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
                'apellidos' => 'required|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
                'email' => 'required|email|max:191',
                'password' => 'required|string|min:8',
                'password_confirmation' => 'required|string|same:password',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // controlador de duplicados: pendientes y usuarios existentes
            $existingPending = PendingUser::where('email', $validated['email'])->first();
            if ($existingPending) {
                // si expiró, borrar y permitir crear nuevo
                if ($existingPending->email_verification_expires_at &&
                    Carbon::now()->isAfter($existingPending->email_verification_expires_at)) {
                    $existingPending->delete();
                } else {
                    return response()->json([
                        'message' => 'El correo ya está pendiente de verificación. Revisa tu bandeja o solicita reenvío.',
                    ], 422);
                }
            }

            if (User::where('email', $validated['email'])->exists()) {
                return response()->json([
                    'message' => 'El correo ya está registrado',
                    'errors' => ['email' => ['El correo ya está en uso']],
                ], 422);
            }

            // Guardar en tabla temporal pending_users
            $token = Str::random(64);
            $pending = PendingUser::create([
                'nombres' => $validated['nombres'],
                'apellidos' => $validated['apellidos'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verification_token' => $token,
                'email_verification_expires_at' => Carbon::now()->addHours(24),
            ]);

            // Enviar correo de confirmación al registro pendiente
            $this->emailService->sendVerification($pending, $token);

            // construimos la URL de verificación para devolverla en el
            // json cuando estamos en entorno local o usando el driver "log",
            // lo cual facilita las pruebas manuales sin tener que leer el
            // fichero de logs.
            // los correos deben dirigir al frontend para que la SPA maneje la
            // animación y la redirección a MapView
            $verificationUrl = env('APP_FRONTEND_URL', 'http://localhost:5174') .
                               '/verify-email?token=' . $token;

            $payload = [
                'message' => 'Usuario registrado. Verifica tu correo electrónico.',
                'pending_id' => $pending->id,
                'email' => $pending->email,
            ];

            if (app()->isLocal() || app()->environment('testing') || config('mail.default') === 'log') {
                $payload['verification_url'] = $verificationUrl;
            }

            return response()->json($payload, 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validación fallida',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en registro: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirmar email del usuario
     */
    public function verifyEmail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string|size:64',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Token inválido',
                    'errors' => $validator->errors(),
                ], 422);
            }

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
                $user = User::create([
                    'name' => trim($pending->nombres . ' ' . $pending->apellidos),
                    'email' => $pending->email,
                    'password' => $pending->password,
                    'email_verified_at' => Carbon::now(),
                    'status' => 'activo',
                ]);

                // borrar pendiente
                $pending->delete();

                return response()->json([
                    'message' => 'Email verificado exitosamente',
                    'user' => $user,
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