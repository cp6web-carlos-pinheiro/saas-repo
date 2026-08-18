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

final class TenantMrpPlanningTest extends TestCase
{
    use RefreshDatabase;

    public function test_mrp_plan_surfaces_minimum_stock_alerts_for_low_inventory(): void
    {
        [$company, $warehouse, $rawMaterial] = $this->context();

        InventoryBalance::query()->create([
            'company_id' => $company->id,
            'warehouse_id' => $warehouse->id,
            'product_id' => $rawMaterial->id,
            'qty_available' => 3,
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
                'product_id' => $rawMaterial->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 1,
                'need_by_date' => now()->addDays(2)->toDateString(),
                'priority' => 10,
                'source_type' => 'sales_order',
                'source_reference_id' => 44,
                'source_reference_type' => 'sales_order',
            ]],
        ], null);

        $this->assertNotEmpty($result['minimum_stock_alerts']);
        $alert = $result['minimum_stock_alerts'][0];

        $this->assertSame('MINIMUM_STOCK', (string) ($alert['alert_type'] ?? ''));
        $this->assertSame(3.0, (float) ($alert['available_stock'] ?? 0));
        $this->assertSame(10.0, (float) ($alert['safety_stock'] ?? 0));
        $this->assertSame(7.0, (float) ($alert['reorder_quantity'] ?? 0));
        $this->assertSame($rawMaterial->id, (int) ($alert['product_id'] ?? 0));
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
            'name' => 'Almoxarifado Materia Prima',
            'code' => 'RM-001',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'sku' => 'RM-001',
            'description' => 'Materia-prima critica',
            'product_type' => 'RAW',
            'uom' => 'KG',
            'unit_id' => Unit::query()->create([
                'company_id' => $company->id,
                'code' => 'KG',
                'name' => 'Quilograma',
                'is_active' => true,
            ])->id,
            'safety_stock' => 10,
            'lead_time_days' => 5,
            'lot_control' => false,
            'serial_control' => false,
            'is_active' => true,
        ]);

        return [$company, $warehouse, $product];
    }
}
