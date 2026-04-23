<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class TwoFactorController extends Controller
{
    public function enable(Request $request)
    {
        $user = $request->user();

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Debes verificar tu correo electrónico antes de activar la autenticación en dos pasos.',
            ], 403);
        }

        $user->two_factor_enabled = true;
        $user->two_factor_code_hash = null;
        $user->two_factor_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'Autenticación en dos pasos activada correctamente.',
            'two_factor_enabled' => true,
        ], 200);
    }

    public function disable(Request $request)
    {
        $user = $request->user();

        $user->two_factor_enabled = false;
        $user->two_factor_code_hash = null;
        $user->two_factor_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'Autenticación en dos pasos desactivada correctamente.',
            'two_factor_enabled' => false,
        ], 200);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        if (!$user->two_factor_enabled) {
            return response()->json([
                'message' => 'La autenticación en dos pasos no está habilitada para este usuario.',
            ], 403);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Debes verificar tu correo electrónico primero.',
            ], 403);
        }

        if (!$user->two_factor_code_hash || !$user->two_factor_expires_at || Carbon::now()->gt($user->two_factor_expires_at)) {
            return response()->json([
                'message' => 'El código ha expirado. Solicita un nuevo ingreso para recibir otro código.',
            ], 403);
        }

        if (!hash_equals($user->two_factor_code_hash, hash('sha256', $validated['code']))) {
            return response()->json([
                'message' => 'Código inválido.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->two_factor_code_hash = null;
        $user->two_factor_expires_at = null;
        $activoId = UserStatus::where('nombre', 'activo')->value('id');
        $user->status_id = $activoId;
        $user->save();

        return response()->json([
            'message' => 'Verificación exitosa. Has iniciado sesión.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
            ],
        ], 200);
    }
}
