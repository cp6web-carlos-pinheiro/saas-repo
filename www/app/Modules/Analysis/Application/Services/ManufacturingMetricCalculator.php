<?php

declare(strict_types=1);

namespace App\Modules\Analysis\Application\Services;

final class ManufacturingMetricCalculator
{
    public static function efficiency(float $standardMinutes, float $actualMinutes): ?float
    {
        if ($actualMinutes <= 0) {
            return null;
        }

        return min(100.0, round(($standardMinutes > 0 ? $standardMinutes : $actualMinutes) / $actualMinutes * 100, 2));
    }

    public static function oee(float $productive, float $pause, float $planned, float $good, float $processed): array
    {
        $availability = ($productive + $pause) > 0 ? $productive / ($productive + $pause) : null;
        $performance = $productive > 0 ? min(1, $planned / $productive) : null;
        $quality = $processed > 0 ? min(1, $good / $processed) : null;

        return ['availability' => $availability, 'performance' => $performance, 'quality' => $quality, 'oee' => ($availability !== null && $performance !== null && $quality !== null) ? round($availability * $performance * $quality, 6) : null];
    }

    public static function percentile(array $values, float $percentile): float
    {
        sort($values);
        if (! $values) {
            return 0.0;
        }$rank = (count($values) - 1) * $percentile;
        $low = (int) floor($rank);
        $high = (int) ceil($rank);

        return (float) $values[$low] + (($values[$high] - $values[$low]) * ($rank - $low));
    }
}
