<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');
        $originHost = parse_url($origin, PHP_URL_HOST) ?? '';

        // Debug logging
        \Log::debug('CORS Debug', [
            'origin' => $origin,
            'originHost' => $originHost,
            'app_env' => env('APP_ENV'),
            'sanctum_domains' => env('SANCTUM_STATEFUL_DOMAINS'),
            'app_frontend_url' => env('APP_FRONTEND_URL'),
        ]);

        // Dominios permitidos locales
        $localAllowed = [
            'localhost:5173',
            '127.0.0.1:5173',
            'localhost:8000',
            '127.0.0.1:8000',
            'localhost:3000',
            'null',
        ];

        // Dominios de producción desde SANCTUM_STATEFUL_DOMAINS (solo hosts)
        $productionDomains = array_filter(
            array_map('trim', explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')))
        );

        // Incluir el host de APP_FRONTEND_URL
        $frontendUrl = env('APP_FRONTEND_URL');
        if ($frontendUrl) {
            $frontendHost = parse_url($frontendUrl, PHP_URL_HOST);
            if ($frontendHost) {
                $productionDomains[] = $frontendHost;
            }
        }

        \Log::debug('CORS Production Domains', [
            'productionDomains' => $productionDomains,
            'localAllowed' => $localAllowed,
        ]);

        // Verificar si el origen es local o está en la lista de dominios permitidos
        $isAllowed = false;
        if (in_array($originHost, $localAllowed, true)) {
            $isAllowed = true;
        } elseif (in_array($originHost, $productionDomains, true)) {
            $isAllowed = true;
        }

        \Log::debug('CORS Check Result', [
            'isAllowed' => $isAllowed,
            'allowOrigin' => ($isAllowed && $origin) ? $origin : 'NOT ALLOWED',
        ]);

        $allowOrigin = ($isAllowed && $origin) ? $origin : '';
        
        // Manejar preflight OPTIONS
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)
                ->header('Access-Control-Allow-Origin', $allowOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, X-CSRF-Token')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        // Procesar petición normal
        $response = $next($request);
        
        return $response
            ->header('Access-Control-Allow-Origin', $allowOrigin)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, X-CSRF-Token')
            ->header('Access-Control-Allow-Credentials', 'true');
    }
}
