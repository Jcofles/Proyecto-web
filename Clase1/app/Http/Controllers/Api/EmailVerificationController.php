<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    /**
     * Página de verificación (opcional - por si acceden desde email)
     */
    public function show(Request $request)
    {
        $token = $request->query('token');
        
        if (!$token) {
            return response()->json([
                'message' => 'Token no proporcionado'
            ], 400);
        }

        // Redirigir al frontend con el token
        $frontendUrl = env('APP_FRONTEND_URL', 'http://localhost:5173');
        return redirect($frontendUrl . '/verify-email?token=' . $token);
    }
}
