<?php

namespace App\Services;

class SecureKeyService
{
    /**
     * Genera la semilla secreta asociada al usuario.
     */
    public function buildSeed(string $email, string $secureEmail): string
    {
        $appKey = config('app.key') ?: env('APP_KEY');
        return hash_hmac('sha256', trim($email) . '|' . trim($secureEmail), $appKey);
    }

    /**
     * Construye el contenido del archivo .jw con 128 líneas únicas.
     */
    public function makeSecureKeyFileContent(string $email, string $secureEmail): string
    {
        $seed = $this->buildSeed($email, $secureEmail);
        $lines = [];

        for ($i = 0; $i < 128; $i++) {
            $hash = strtoupper(hash_hmac('sha256', "$seed|line:$i", $seed));
            $lines[] = sprintf('%03d|%s', $i + 1, $hash);
        }

        return implode("\n", $lines);
    }

    /**
     * Obtiene el hash que identifica el archivo seguro.
     */
    public function buildSecureKeyHash(string $email, string $secureEmail): string
    {
        return hash('sha256', $this->makeSecureKeyFileContent($email, $secureEmail));
    }

    /**
     * Nombre del archivo seguro.
     */
    public function fileName(): string
    {
        return 'para ti crack.jw';
    }
}
