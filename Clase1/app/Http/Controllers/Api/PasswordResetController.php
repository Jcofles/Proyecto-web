<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPasswordCodeRequest;
use App\Http\Requests\VerifyPasswordCodeRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Enviar código de recuperación por email
     */
    public function sendCode(SendPasswordCodeRequest $request)
    {
        $validated = $request->validated();
        $email = $validated['email'];
        
        // Generar código de 6 dígitos
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Guardar código en BD (eliminar códigos anteriores del mismo email)
        DB::table('password_reset_codes')->where('email', $email)->delete();
        DB::table('password_reset_codes')->insert([
            'email' => $email,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(15),
            'created_at' => Carbon::now(),
        ]);

        // Enviar email
        try {
            Mail::raw("Tu código de recuperación es: {$code}\n\nEste código expira en 15 minutos.", function ($message) use ($email) {
                $message->to($email)
                        ->subject('Código de recuperación - ITFIP Maps');
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al enviar el correo',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Código enviado exitosamente',
            'email' => $email,
        ], 200);
    }

    /**
     * Verificar código
     */
    public function verifyCode(VerifyPasswordCodeRequest $request)
    {
        $validated = $request->validated();
        
        $record = DB::table('password_reset_codes')
            ->where('email', $validated['email'])
            ->where('code', $validated['code'])
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'Código incorrecto',
            ], 422);
        }

        if (Carbon::now()->isAfter($record->expires_at)) {
            return response()->json([
                'message' => 'Código expirado',
            ], 422);
        }

        return response()->json([
            'message' => 'Código verificado',
            'email' => $validated['email'],
        ], 200);
    }

    /**
     * Restablecer contraseña
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        // Verificar código nuevamente
        $record = DB::table('password_reset_codes')
            ->where('email', $validated['email'])
            ->where('code', $validated['code'])
            ->first();

        if (!$record || Carbon::now()->isAfter($record->expires_at)) {
            return response()->json([
                'message' => 'Código inválido o expirado',
            ], 422);
        }

        // Actualizar contraseña
        $user = User::where('email', $validated['email'])->first();
        $user->password = Hash::make($validated['password']);
        $user->save();

        // Eliminar código usado
        DB::table('password_reset_codes')->where('email', $validated['email'])->delete();

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente',
        ], 200);
    }
}
