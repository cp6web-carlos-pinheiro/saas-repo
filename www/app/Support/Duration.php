<?php

declare(strict_types=1);

namespace App\Support;

final class Duration
{
    public static function formatMinutes(float|int|null $minutes): string
    {
        $totalMinutes = max(0, (int) round((float) $minutes));

        return sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
    }

    public static function minutesFromInput(mixed $value): mixed
    {
        if (! is_string($value) || ! str_contains($value, ':')) {
            return $value;
        }

        $value = trim($value);

        if (! preg_match('/^(\d+):([0-5]\d)$/', $value, $matches)) {
            return $value;
        }

        return ((int) $matches[1] * 60) + (int) $matches[2];
    }
}
