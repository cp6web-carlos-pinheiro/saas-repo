<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Duration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DurationTest extends TestCase
{
    #[DataProvider('minuteFormats')]
    public function test_it_formats_minutes_as_hours_and_minutes(float|int|null $minutes, string $expected): void
    {
        self::assertSame($expected, Duration::formatMinutes($minutes));
    }

    public static function minuteFormats(): array
    {
        return [
            'empty' => [null, '00:00'],
            'less than one hour' => [18, '00:18'],
            'hours and minutes' => [125, '02:05'],
            'decimal minutes are rounded' => [12.5, '00:13'],
            'more than two hour digits' => [6005, '100:05'],
        ];
    }

    public function test_it_converts_masked_input_to_minutes_and_preserves_other_values(): void
    {
        self::assertSame(125, Duration::minutesFromInput('02:05'));
        self::assertSame(6005, Duration::minutesFromInput('100:05'));
        self::assertSame('12.5', Duration::minutesFromInput('12.5'));
        self::assertSame('01:75', Duration::minutesFromInput('01:75'));
    }
}
