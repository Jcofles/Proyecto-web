<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecureKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SecureKeyController extends Controller
{
    protected SecureKeyService $secureKeyService;

    public function __construct(SecureKeyService $secureKeyService)
    {
        $this->secureKeyService = $secureKeyService;
    }

    public function downloadSecureKey(Request $request)
    {
        $user = $request->user();
        
        $content = $this->secureKeyService->makeSecureKeyFileContent($user->email, $user->secure_email);
        
        $user->update(['secure_key_downloaded_at' => now()]);
        
        return response($content)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $this->secureKeyService->fileName() . '"');
    }

    public function loginWithKey(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'secure_key_content' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->secure_email) {
            return response()->json(['message' => 'Usuario no encontrado o sin clave segura'], 404);
        }

        $expectedHash = $this->secureKeyService->buildSecureKeyHash($user->email, $user->secure_email);
        $providedHash = hash('sha256', $request->secure_key_content);

        if ($expectedHash !== $providedHash) {
            return response()->json(['message' => 'Archivo de clave segura inválido'], 401);
        }

        $token = $user->createToken('secure_key_auth')->plainTextToken;

        return response()->json([
            'message' => 'Acceso concedido con clave segura',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function sendSecureKeyEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->secure_email) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $content = $this->secureKeyService->makeSecureKeyFileContent($user->email, $user->secure_email);

        try {
            Mail::raw('Adjunto encontrarás tu archivo de recuperación segura.', function ($message) use ($user, $content) {
                $message->to($user->secure_email)
                    ->subject('Tu archivo de recuperación - ITFIP Maps')
                    ->attachData($content, $this->secureKeyService->fileName(), [
                        'mime' => 'application/octet-stream',
                    ]);
            });

            return response()->json(['message' => 'Archivo enviado al correo seguro']);
        } catch (\Exception $e) {
            Log::error('Error enviando clave segura: ' . $e->getMessage());
            return response()->json(['message' => 'Error al enviar el archivo'], 500);
        }
    }
}
