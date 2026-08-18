<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\MRP\Application\Services\MrpPlanningService;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Shared\Infrastructure\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

final class TenantMrpSchedulerPlanningTest extends TestCase
{
    use RefreshDatabase;

    public function test_finite_scheduler_splits_load_across_days(): void
    {
        [$company, $plant] = $this->context();

        $this->createWorkCenter($company, $plant->id);

        $service = app(MrpPlanningService::class);
        $method = new ReflectionMethod($service, 'buildFiniteSchedulerPlan');
        $method->setAccessible(true);

        $plan = $method->invoke($service, [[
            'source_requirement_key' => 'FG-1',
            'product_id' => 99,
            'product_sku' => 'FG-001',
            'release_date' => now()->toDateString(),
            'quantity' => 8,
        ]], now()->toDateString());

        $this->assertSame('finite', (string) ($plan['mode'] ?? ''));
        $this->assertSame(5.0, (float) ($plan['daily_capacity_units'] ?? 0));
        $this->assertCount(2, $plan['days'] ?? []);
        $this->assertSame(5.0, (float) ($plan['days'][0]['allocated_units'] ?? 0));
        $this->assertSame(3.0, (float) ($plan['days'][1]['allocated_units'] ?? 0));
        $this->assertSame(8.0, array_sum(array_map(
            static fn (array $day): float => (float) ($day['allocated_units'] ?? 0),
            $plan['days'] ?? []
        )));
        $this->assertSame('WC-001', (string) ($plan['source_work_centers'][0]['code'] ?? ''));
    }

    /**
     * @return array{0: Company, 1: Plant}
     */
    private function context(): array
    {
        $company = Company::query()->create([
            'name' => 'Atlas Components',
            'code' => 'ATL',
            'is_active' => true,
        ]);

        app(TenantContext::class)->setCompanyId($company->id);

        $plant = Plant::query()->create([
            'company_id' => $company->id,
            'name' => 'Planta Principal',
            'code' => 'PLT-001',
            'timezone' => 'UTC',
            'is_active' => true,
        ]);

        return [$company, $plant];
    }

    private function createWorkCenter(Company $company, int $plantId): WorkCenter
    {
        return WorkCenter::query()->create([
            'company_id' => $company->id,
            'plant_id' => $plantId,
            'code' => 'WC-001',
            'name' => 'Centro Principal',
            'resource_type' => 'MACHINE',
            'capacity_per_day' => 5,
            'efficiency_factor' => 100,
            'is_active' => true,
        ]);
    }
}
