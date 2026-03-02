<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Enviar código de recuperación por email
     */
    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'El correo no está registrado',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        
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
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $record = DB::table('password_reset_codes')
            ->where('email', $request->email)
            ->where('code', $request->code)
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
            'email' => $request->email,
        ], 200);
    }

    /**
     * Restablecer contraseña
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validación fallida',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verificar código nuevamente
        $record = DB::table('password_reset_codes')
            ->where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (!$record || Carbon::now()->isAfter($record->expires_at)) {
            return response()->json([
                'message' => 'Código inválido o expirado',
            ], 422);
        }

        // Actualizar contraseña
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Eliminar código usado
        DB::table('password_reset_codes')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente',
        ], 200);
    }
}
