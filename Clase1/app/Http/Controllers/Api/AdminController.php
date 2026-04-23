<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Bloquear un usuario (solo admin)
     */
    public function blockUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $bloqueadoId = \App\Models\UserStatus::where('nombre', 'bloqueado')->value('id');
        $user->status_id = $bloqueadoId;
        $user->save();
        
        // Cerrar todas las sesiones del usuario bloqueado
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Usuario bloqueado exitosamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
            ],
        ], 200);
    }

    /**
     * Desbloquear un usuario (solo admin)
     */
    public function unblockUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $inactivoId = \App\Models\UserStatus::where('nombre', 'inactivo')->value('id');
        $user->status_id = $inactivoId;
        $user->save();

        return response()->json([
            'message' => 'Usuario desbloqueado exitosamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
            ],
        ], 200);
    }
}
