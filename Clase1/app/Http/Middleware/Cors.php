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

        // Dominios permitidos locales y de producción según las variables de entorno.
        $allowed = array_filter(array_merge([
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://localhost:8000',
            'http://127.0.0.1:8000',
            'http://localhost:3000',
            'null',
        ], array_map('trim', explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')))));

        $frontendUrl = env('APP_FRONTEND_URL');
        if ($frontendUrl) {
            $allowed[] = rtrim($frontendUrl, '/');
        }

        $origin = $origin ? rtrim($origin, '/') : $origin;
        $allowOrigin = ($origin && in_array($origin, $allowed, true)) ? $origin : '';
        
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
