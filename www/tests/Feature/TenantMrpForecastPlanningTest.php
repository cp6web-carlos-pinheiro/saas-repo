<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryBalance;
use App\Modules\MRP\Application\Services\MrpPlanningService;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Infrastructure\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantMrpForecastPlanningTest extends TestCase
{
    use RefreshDatabase;

    public function test_forecast_lines_are_accepted_as_additional_demand(): void
    {
        [$company, $warehouse, $product] = $this->context();

        InventoryBalance::query()->create([
            'company_id' => $company->id,
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'qty_available' => 0,
            'qty_reserved' => 0,
            'qty_in_transit' => 0,
            'qty_inspection' => 0,
            'last_movement_at' => now(),
        ]);

        $service = app(MrpPlanningService::class);

        $result = $service->run([
            'reference_date' => now()->toDateString(),
            'planning_bucket' => 'daily',
            'priority_rule' => 'priority_due_date',
            'demand_lines' => [[
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 2,
                'need_by_date' => now()->addDays(5)->toDateString(),
                'priority' => 20,
                'source_type' => 'sales_order',
                'source_reference_id' => 100,
                'source_reference_type' => 'sales_order',
            ]],
            'forecast_lines' => [[
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 3,
                'need_by_date' => now()->addDays(5)->toDateString(),
                'priority' => 5,
                'source_type' => 'forecast',
                'source_reference_id' => 200,
                'source_reference_type' => 'forecast',
            ]],
        ], null);

        $this->assertCount(2, $result['demand_aggregation']);

        $sourceTypes = array_map(
            static fn (array $row): string => (string) ($row['source_type'] ?? ''),
            $result['demand_aggregation']
        );

        $this->assertContains('forecast', $sourceTypes);
        $this->assertContains('sales_order', $sourceTypes);
        $this->assertSame(5.0, (float) $result['purchase_suggestions'][0]['quantity']);
    }

    /**
     * @return array{0: Company, 1: Warehouse, 2: Product}
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

        $warehouse = Warehouse::query()->create([
            'company_id' => $company->id,
            'plant_id' => $plant->id,
            'name' => 'Almoxarifado',
            'code' => 'WH-001',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'sku' => 'RAW-001',
            'description' => 'Insumo planejado',
            'product_type' => 'RAW',
            'uom' => 'KG',
            'unit_id' => Unit::query()->create([
                'company_id' => $company->id,
                'code' => 'KG',
                'name' => 'Quilograma',
                'is_active' => true,
            ])->id,
            'safety_stock' => 0,
            'lead_time_days' => 3,
            'lot_control' => false,
            'serial_control' => false,
            'is_active' => true,
        ]);

        return [$company, $warehouse, $product];
    }
}
