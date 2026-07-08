<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Logging;

use Monolog\Formatter\JsonFormatter;

final class JsonLogFormatter
{
    public function __invoke($logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter());
        }
    }
}
