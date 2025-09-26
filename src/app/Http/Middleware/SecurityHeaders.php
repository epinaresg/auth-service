<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Evita que el navegador interprete tipos MIME incorrectos
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Bloquea que la API se cargue en iframes externos
        $response->headers->set('X-Frame-Options', 'DENY');

        // Política de referrer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // CSP mínimo para APIs JSON
        $response->headers->set('Content-Security-Policy', "default-src 'none';");

        // Opcional: evitar que el navegador haga cache de respuestas sensibles
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');

        return $response;
    }
}
