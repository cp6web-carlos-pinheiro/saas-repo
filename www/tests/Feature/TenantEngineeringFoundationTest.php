<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Eco\Application\Services\EngineeringChangeOrderService;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Routing\Application\Services\RoutingStandardTimeService;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingOperation;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingVersion;
use App\Modules\Scheduling\Application\Services\ProductionResourceService;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionResource;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
use App\Shared\Infrastructure\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantEngineeringFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_and_work_center_hour_rate_are_versioned(): void
    {
        [$company, $plant, $workCenter] = $this->context();
        $service = app(ProductionResourceService::class);

        $resource = $service->createResource([
            'plant_id' => $plant->id,
            'work_center_id' => $workCenter->id,
            'code' => 'MCH-001',
            'name' => 'Torno 001',
            'resource_type' => 'MACHINE',
            'status' => 'ACTIVE',
            'capacity_per_day' => 8,
            'efficiency_factor' => 95,
            'effective_from' => '2026-08-01',
        ]);

        $rate = $service->createRate((int) $workCenter->id, [
            'hourly_rate' => 125.50,
            'currency' => 'BRL',
            'effective_from' => '2026-08-01',
            'change_reason' => 'Custo padrão inicial',
        ], null);

        self::assertSame('MCH-001', $resource['code']);
        self::assertSame(125.50, (float) $rate['hourly_rate']);
        self::assertSame(125.50, (float) ($service->effectiveRate((int) $workCenter->id, '2026-08-10')['hourly_rate'] ?? 0));
        self::assertDatabaseHas('production_resources', ['company_id' => $company->id, 'code' => 'MCH-001']);
    }

    public function test_standard_time_calculates_per_unit_and_approves_without_overlap(): void
    {
        [, $plant, $workCenter, $routingOperation] = $this->routingContext();
        $service = app(RoutingStandardTimeService::class);

        $time = $service->create((int) $routingOperation->id, [
            'version_number' => 1,
            'time_basis' => 'PER_UNIT',
            'base_quantity' => 1,
            'setup_time_minutes' => 10,
            'runtime_minutes' => 2,
            'queue_time_minutes' => 3,
            'move_time_minutes' => 1,
            'efficiency_factor' => 100,
            'yield_factor' => 100,
        ]);

        $approved = $service->approve((int) $time['id'], ['effective_from' => '2026-08-01']);
        $calculated = $service->calculate((int) $routingOperation->id, 5, '2026-08-10');

        self::assertSame('APPROVED', $approved['status']);
        self::assertSame(10.0, (float) $calculated['setup_time_minutes']);
        self::assertSame(10.0, (float) $calculated['runtime_time_minutes']);
        self::assertSame(25.0, (float) $calculated['total_time_minutes']);
        self::assertSame((int) $time['id'], (int) $calculated['standard_time_id']);
        self::assertSame($workCenter->id, $routingOperation->work_center_id);
    }

    public function test_eco_accepts_standard_time_and_reports_routing_impact(): void
    {
        [, , , $routingOperation] = $this->routingContext();
        $standardTimeService = app(RoutingStandardTimeService::class);
        $time = $standardTimeService->create((int) $routingOperation->id, [
            'version_number' => 1,
            'time_basis' => 'PER_BATCH',
            'base_quantity' => 1,
            'runtime_minutes' => 20,
        ]);

        $eco = app(EngineeringChangeOrderService::class)->createDraft([
            'title' => 'Revisar tempo de operação',
            'effective_from' => '2026-09-01',
            'lines' => [[
                'target_domain' => 'STANDARD_TIME',
                'target_entity_id' => $time['id'],
                'change_type' => 'STANDARD_TIME_CHANGE',
                'change_summary' => 'Atualização do tempo de processo',
            ]],
        ]);

        self::assertSame('DRAFT', $eco['status']);
        self::assertSame('STANDARD_TIME', $eco['lines'][0]['target_domain']);
    }

    /** @return array{0: Company, 1: Plant, 2: WorkCenter} */
    private function context(): array
    {
        $company = Company::query()->create(['name' => 'Engineering Co', 'code' => 'ENG', 'is_active' => true]);
        app(TenantContext::class)->setCompanyId($company->id);

        $plant = Plant::query()->create([
            'company_id' => $company->id,
            'name' => 'Planta Principal',
            'code' => 'PLT-ENG',
            'timezone' => 'UTC',
            'is_active' => true,
        ]);

        $workCenter = WorkCenter::query()->create([
            'company_id' => $company->id,
            'plant_id' => $plant->id,
            'code' => 'WC-ENG',
            'name' => 'Centro de Engenharia',
            'resource_type' => 'MACHINE',
            'capacity_per_day' => 8,
            'efficiency_factor' => 100,
            'is_active' => true,
        ]);

        return [$company, $plant, $workCenter];
    }

    /** @return array{0: Company, 1: Plant, 2: WorkCenter, 3: RoutingOperation} */
    private function routingContext(): array
    {
        [$company, $plant, $workCenter] = $this->context();
        $unit = Unit::query()->create([
            'company_id' => $company->id,
            'code' => 'UN',
            'name' => 'Unidade',
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'company_id' => $company->id,
            'sku' => 'FG-ENG-001',
            'description' => 'Produto de engenharia',
            'product_type' => 'FG',
            'uom' => 'UN',
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);
        $routing = RoutingVersion::query()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'version_number' => 1,
            'status' => 'DRAFT',
            'description' => 'Routing de teste',
        ]);
        $operation = RoutingOperation::query()->create([
            'company_id' => $company->id,
            'routing_version_id' => $routing->id,
            'work_center_id' => $workCenter->id,
            'operation_no' => 10,
            'operation_code' => 'OP-10',
            'operation_name' => 'Operação 10',
            'sequence' => 1,
            'setup_time_minutes' => 5,
            'runtime_minutes' => 5,
        ]);

        return [$company, $plant, $workCenter, $operation];
    }
}
