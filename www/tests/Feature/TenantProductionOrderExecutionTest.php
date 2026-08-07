<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryBalance;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Production\Application\Services\ProductionOrderService;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Infrastructure\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantProductionOrderExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_production_posts_finished_goods_receipt_and_tracks_scrap_and_inspection(): void
    {
        [$company, $warehouse, $product] = $this->context();

        $service = app(ProductionOrderService::class);

        $order = ProductionOrder::query()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'source_type' => 'MANUAL',
            'order_number' => 'PO-20260804-000001',
            'status' => 'RELEASED',
            'quantity_planned' => 10,
            'quantity_produced' => 0,
            'quantity_scrapped' => 0,
            'scheduled_start_date' => now()->toDateString(),
            'scheduled_end_date' => now()->addDay()->toDateString(),
            'created_by' => null,
            'metadata' => ['line' => 'A1'],
        ]);

        $partial = $service->partialProduction((int) $order->id, [
            'quantity_completed' => 4,
            'quantity_scrapped' => 1,
            'lot_number' => 'LOT-001',
            'produced_at' => now()->toDateTimeString(),
            'operation_no' => 10,
            'setup_time_minutes' => 12.5,
            'process_time_minutes' => 18,
            'inspection_status' => 'APPROVED',
            'inspected_at' => now()->toDateTimeString(),
            'inspection_notes' => 'Liberado na inspeção em processo',
            'metadata' => ['inspector' => 'qa-1'],
        ], null);

        $this->assertSame(4.0, (float) ($partial['output']['quantity_completed'] ?? 0));
        $this->assertSame('APPROVED', (string) ($partial['output']['inspection_status'] ?? ''));
        $this->assertSame(4.0, (float) ($partial['production_order']['quantity_produced'] ?? 0));
        $this->assertSame(1.0, (float) ($partial['production_order']['quantity_scrapped'] ?? 0));

        $balance = InventoryBalance::query()
            ->where('company_id', $company->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        $this->assertSame(4.0, (float) $balance->qty_available);

        $this->assertDatabaseHas('production_operation_outputs', [
            'company_id' => $company->id,
            'production_order_id' => $order->id,
            'inspection_status' => 'APPROVED',
        ]);

        $this->assertDatabaseHas('stock_ledger_movements', [
            'company_id' => $company->id,
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'movement_type' => 'RECEIPT',
            'reference_type' => 'production_order',
            'reference_id' => $order->id,
        ]);
    }

    public function test_sales_order_reference_is_exposed_only_for_sales_sources(): void
    {
        [$company, $warehouse, $product] = $this->context();

        $salesOrder = ProductionOrder::query()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'source_type' => 'SALE',
            'source_reference_type' => 'sale',
            'source_reference_id' => 42,
            'order_number' => 'PO-20260806-000001',
            'status' => 'DRAFT',
            'quantity_planned' => 1,
            'quantity_produced' => 0,
            'quantity_scrapped' => 0,
        ]);

        $manualOrder = ProductionOrder::query()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'source_type' => 'MANUAL',
            'source_reference_type' => 'manual_note',
            'source_reference_id' => 42,
            'order_number' => 'PO-20260806-000002',
            'status' => 'DRAFT',
            'quantity_planned' => 1,
            'quantity_produced' => 0,
            'quantity_scrapped' => 0,
        ]);

        $this->assertSame('#42', $salesOrder->sales_order_reference);
        $this->assertNull($manualOrder->sales_order_reference);
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
            'name' => 'PA',
            'code' => 'FG-001',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'sku' => 'FG-001',
            'description' => 'Produto acabado',
            'product_type' => 'FG',
            'uom' => 'UN',
            'unit_id' => Unit::query()->create([
                'company_id' => $company->id,
                'code' => 'UN',
                'name' => 'Unidade',
                'is_active' => true,
            ])->id,
            'lot_control' => false,
            'serial_control' => false,
            'is_active' => true,
        ]);

        return [$company, $warehouse, $product];
    }
}
