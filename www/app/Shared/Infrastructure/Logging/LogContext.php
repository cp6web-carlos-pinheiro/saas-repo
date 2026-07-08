<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Logging;

final class LogContext
{
    public static function default(array $extra = []): array
    {
        return array_merge([
            'trace_id' => request()?->header('X-Trace-Id'),
            'user_id' => auth()->id(),
            'ip' => request()?->ip(),
            'route' => request()?->path(),
        ], $extra);
    }
}
