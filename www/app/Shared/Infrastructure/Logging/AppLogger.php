<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Logging;

use Illuminate\Support\Facades\Log;

final class AppLogger
{
    public function info(string $message, array $context = [], string $channel = 'stack'): void
    {
        Log::channel($channel)->info($message, LogContext::default($context));
    }

    public function warning(string $message, array $context = [], string $channel = 'stack'): void
    {
        Log::channel($channel)->warning($message, LogContext::default($context));
    }

    public function error(string $message, array $context = [], string $channel = 'stack'): void
    {
        Log::channel($channel)->error($message, LogContext::default($context));
    }
}
