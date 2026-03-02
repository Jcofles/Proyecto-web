<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Enviar email de verificación usando un Mailable.
     *
     * @param  mixed  $entity  Instancia de User o PendingUser
     * @param  string $token   Token de verificación
     * @return void
     */
    public function sendVerification($entity, string $token): void
    {
        // el mailable se encarga de construir el contenido y asunto
        try {
            Mail::to($entity->email)->send(new \App\Mail\VerificationEmail($entity, $token));
        } catch (\Exception $e) {
            Log::error('Error enviando correo de verificación: ' . $e->getMessage());
            // fallos no impiden el registro
        }
    }
}
