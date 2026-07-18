<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $viteHttpDevOrigins = app()->environment('local')
            ? ' http://localhost:5173 http://127.0.0.1:5173'
            : '';
        $viteWsDevOrigins = app()->environment('local')
            ? ' ws://localhost:5173 ws://127.0.0.1:5173'
            : '';

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com{$viteHttpDevOrigins}; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com{$viteHttpDevOrigins}; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' https: data:; connect-src 'self'{$viteHttpDevOrigins}{$viteWsDevOrigins}; frame-ancestors 'self';");

        return $response;
    }
}
