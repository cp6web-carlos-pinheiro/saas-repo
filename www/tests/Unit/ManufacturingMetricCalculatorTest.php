<?php
declare(strict_types=1);
namespace Tests\Unit;
use App\Modules\Analysis\Application\Services\ManufacturingMetricCalculator;
use PHPUnit\Framework\TestCase;
final class ManufacturingMetricCalculatorTest extends TestCase
{
    public function test_efficiency_is_capped_at_one_hundred_percent(): void { self::assertSame(100.0, ManufacturingMetricCalculator::efficiency(50, 40)); }
    public function test_missing_standard_uses_actual_time(): void { self::assertSame(100.0, ManufacturingMetricCalculator::efficiency(0, 40)); }
    public function test_oee_uses_pause_and_quality_components(): void { $oee=ManufacturingMetricCalculator::oee(80,20,80,90,100);self::assertSame(0.8,$oee['availability']);self::assertSame(0.9,$oee['quality']);self::assertSame(0.72,$oee['oee']); }
    public function test_percentile_is_reproducible(): void { self::assertSame(3.0, ManufacturingMetricCalculator::percentile([1,2,3,4,5],.5)); }
}
