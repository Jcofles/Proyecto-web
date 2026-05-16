<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EmailService
{
    public function sendVerification($entity, string $token): void
    {
        try {
            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
            $verifyUrl = $frontendUrl . '/verify-email?token=' . $token;

            $html = "
                <h2>Verifica tu correo electrónico</h2>
                <p>Hola {$entity->nombres},</p>
                <p>Gracias por registrarte en ITFIP Maps. Haz clic en el enlace para verificar tu cuenta:</p>
                <a href='{$verifyUrl}' style='background:#00bcd4;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;margin:16px 0;'>Verificar mi correo</a>
                <p>O copia y pega este enlace en tu navegador:</p>
                <p>{$verifyUrl}</p>
                <p>Este enlace expira en 24 horas.</p>
                <p>Si no realizaste este registro, ignora este correo.</p>
                <p>Saludos,<br><strong>ITFIP Maps</strong></p>
            ";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('mail.mailers.resend.key', env('RESEND_API_KEY')),
                'Content-Type' => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from' => 'ITFIP Maps <onboarding@resend.dev>',
                'to' => [$entity->email],
                'subject' => 'Verifica tu correo - ITFIP Maps',
                'html' => $html,
            ]);

            if ($response->successful()) {
                Log::info('Email enviado via Resend API a: ' . $entity->email);
            } else {
                Log::error('Resend API error: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Error enviando correo: ' . $e->getMessage());
        }
    }
}