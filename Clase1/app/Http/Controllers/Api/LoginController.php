<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
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
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
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

        // Actualizar solo los campos enviados
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        
        $user->save();

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
            ],
        ], 200);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
        // Cambiar estado a eliminado
        $user->status = 'eliminado';
        $user->save();
        
        // Cerrar todas las sesiones
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Cuenta eliminada exitosamente',
        ], 200);
    }
}
