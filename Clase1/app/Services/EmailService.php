<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    public function sendVerification($entity, string $token): void
    {
        try {
            Mail::to($entity->email)->send(new \App\Mail\VerificationEmail($entity, $token));
            Log::info('Email enviado a: ' . $entity->email);
        } catch (\Exception $e) {
            Log::error('Error enviando correo: ' . $e->getMessage());
        }
    }
}