<?php

declare(strict_types=1);

namespace App\Shared\Application\Events;

use DateTimeImmutable;

abstract class BaseEvent
{
    public function __construct(
        public readonly string $eventId,
        public readonly DateTimeImmutable $occurredAt,
        public readonly array $payload = []
    ) {
    }
}
