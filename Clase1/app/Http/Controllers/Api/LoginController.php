<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();

        // Si el usuario NO existe, retornar error genérico sin bloquear
        if (!$user) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        // Verificar si está bloqueado (solo para usuarios existentes)
        $attempt = DB::table('login_attempts')->where('email', $email)->first();
        
        if ($attempt && $attempt->blocked_until && Carbon::now()->lt($attempt->blocked_until)) {
            $remainingSeconds = Carbon::now()->diffInSeconds($attempt->blocked_until);
            return response()->json([
                'message' => 'Cuenta temporalmente bloqueada por múltiples intentos fallidos',
                'blocked' => true,
                'remaining_seconds' => $remainingSeconds,
            ], 429);
        }

        // Verificar contraseña
        if (!Hash::check($request->password, $user->password)) {
            // Incrementar intentos fallidos solo para usuarios existentes
            $this->recordFailedAttempt($email);
            
            $attempt = DB::table('login_attempts')->where('email', $email)->first();
            $remainingAttempts = 5 - $attempt->attempts;
            
            if ($remainingAttempts <= 0) {
                return response()->json([
                    'message' => 'Cuenta bloqueada por 15 minutos debido a múltiples intentos fallidos',
                    'blocked' => true,
                    'remaining_seconds' => 900,
                ], 429);
            }
            
            return response()->json([
                'message' => 'Credenciales incorrectas',
                'remaining_attempts' => $remainingAttempts,
            ], 401);
        }

        // Validar estados
        if ($user->status === 'bloqueado') {
            return response()->json([
                'message' => 'Tu cuenta ha sido bloqueada. Contacta al administrador.',
            ], 403);
        }

        if ($user->status === 'eliminado') {
            return response()->json([
                'message' => 'Esta cuenta ha sido eliminada.',
            ], 403);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Debes verificar tu correo electrónico primero',
            ], 403);
        }

        // Actualizar estado a activo al iniciar sesión
        $user->status = 'activo';
        $user->save();

        // Login exitoso - limpiar intentos
        DB::table('login_attempts')->where('email', $email)->delete();
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
            ],
        ], 200);
    }

    private function recordFailedAttempt($email)
    {
        $attempt = DB::table('login_attempts')->where('email', $email)->first();
        
        if (!$attempt) {
            DB::table('login_attempts')->insert([
                'email' => $email,
                'attempts' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $newAttempts = $attempt->attempts + 1;
            $blockedUntil = null;
            
            // Bloquear por 15 minutos después de 5 intentos
            if ($newAttempts >= 5) {
                $blockedUntil = Carbon::now()->addMinutes(15);
            }
            
            DB::table('login_attempts')->where('email', $email)->update([
                'attempts' => $newAttempts,
                'blocked_until' => $blockedUntil,
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        
        // Cambiar estado a inactivo al cerrar sesión
        $user->status = 'inactivo';
        $user->save();
        
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Sesión cerrada exitosamente',
        ], 200);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name, // usa el accessor
                'nombres' => $user->nombres,
                'apellidos' => $user->apellidos,
                'email' => $user->email,
            ],
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        // Validar solo los campos que se envían
        $rules = [];
        
        if ($request->has('name')) {
            $rules['name'] = 'required|string|max:255';
        }
        
        if ($request->has('email')) {
            $rules['email'] = 'required|email|unique:users,email,' . $user->id;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Actualizar nombre (directo)
        if ($request->has('name')) {
            // Si envían 'name', dividirlo en nombres y apellidos
            $parts = explode(' ', $request->name, 2);
            $user->nombres = $parts[0] ?? '';
            $user->apellidos = $parts[1] ?? '';
            $user->save();
        }
        
        if ($request->has('nombres')) {
            $user->nombres = $request->nombres;
            $user->save();
        }
        
        if ($request->has('apellidos')) {
            $user->apellidos = $request->apellidos;
            $user->save();
        }
        
        // Cambio de email requiere verificación
        if ($request->has('email')) {
            $newEmail = $request->email;
            
            // Generar código de 6 dígitos
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Guardar código temporal
            \Illuminate\Support\Facades\DB::table('email_change_codes')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'new_email' => $newEmail,
                    'code' => $code,
                    'expires_at' => \Carbon\Carbon::now()->addMinutes(15),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            
            // Enviar código al nuevo email
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Tu código de verificación para cambiar el email es: {$code}\n\nEste código expira en 15 minutos.",
                    function ($message) use ($newEmail) {
                        $message->to($newEmail)
                                ->subject('Verificar cambio de email - ITFIP Maps');
                    }
                );
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error al enviar código de verificación',
                ], 500);
            }
            
            return response()->json([
                'message' => 'Código de verificación enviado al nuevo email',
                'requires_verification' => true,
                'new_email' => $newEmail,
            ], 200);
        }

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'nombres' => $user->nombres,
                'apellidos' => $user->apellidos,
                'email' => $user->email,
                'status' => $user->status,
            ],
        ], 200);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
        // Guardar datos antes de modificar
        $originalEmail = $user->email;
        $userName = $user->name;
        
        // Modificar el email para liberar el original
        $timestamp = time();
        $user->email = "deleted_{$timestamp}_{$originalEmail}";
        
        // Cambiar estado a eliminado
        $user->status = 'eliminado';
        $user->save();
        
        // Cerrar todas las sesiones
        $user->tokens()->delete();
        
        // Soft delete (marca deleted_at)
        $user->delete();
        
        // Enviar email de notificación al correo original
        try {
            \Illuminate\Support\Facades\Mail::to($originalEmail)
                ->send(new \App\Mail\AccountDeletedEmail($userName, $originalEmail));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando email de cuenta eliminada: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Cuenta eliminada exitosamente',
        ], 200);
    }

    public function verifyEmailChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Código inválido',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        
        $record = \Illuminate\Support\Facades\DB::table('email_change_codes')
            ->where('user_id', $user->id)
            ->where('code', $request->code)
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'Código incorrecto',
            ], 422);
        }

        if (\Carbon\Carbon::now()->isAfter($record->expires_at)) {
            return response()->json([
                'message' => 'Código expirado',
            ], 422);
        }

        // Actualizar email
        $user->email = $record->new_email;
        $user->save();

        // Eliminar código usado
        \Illuminate\Support\Facades\DB::table('email_change_codes')
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'message' => 'Email actualizado exitosamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'nombres' => $user->nombres,
                'apellidos' => $user->apellidos,
                'email' => $user->email,
                'status' => $user->status,
            ],
        ], 200);
    }
}
