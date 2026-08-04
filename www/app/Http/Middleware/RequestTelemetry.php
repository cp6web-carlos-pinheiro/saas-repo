<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class RequestTelemetry
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = trim((string) $request->header('X-Request-Id', ''));

        if ($requestId === '') {
            $requestId = (string) Str::uuid();
        }

        $request->attributes->set('request_id', $requestId);

        Log::withContext([
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => '/'.ltrim($request->path(), '/'),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
        ]);

        $start = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Request-Id', $requestId);

        if ((bool) config('security.telemetry.enabled', true)) {
            $durationMs = (int) round((microtime(true) - $start) * 1000);

            Log::channel('telemetry')->info('http.request', [
                'request_id' => $requestId,
                'method' => $request->method(),
                'path' => '/'.ltrim($request->path(), '/'),
                'route' => $request->route()?->getName(),
                'status' => $response->getStatusCode(),
                'duration_ms' => $durationMs,
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
            ]);
        }

        return $response;
    }
}
