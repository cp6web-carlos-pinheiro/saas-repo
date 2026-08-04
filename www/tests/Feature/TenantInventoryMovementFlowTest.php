<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryReservation;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Infrastructure\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantInventoryMovementFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_stock_posts_compound_movements_and_is_reversible(): void
    {
        [$company, $sourceWarehouse, $targetWarehouse, $product] = $this->inventoryContext();

        $service = app(InventoryService::class);

        $service->upsertBalance([
            'warehouse_id' => $sourceWarehouse->id,
            'product_id' => $product->id,
            'qty_available' => 12,
            'qty_reserved' => 0,
            'qty_in_transit' => 0,
            'qty_inspection' => 0,
        ]);

        $transfer = $service->transferStock([
            'source_warehouse_id' => $sourceWarehouse->id,
            'target_warehouse_id' => $targetWarehouse->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'movement_at' => now()->toDateTimeString(),
            'metadata' => ['transfer_reason' => 'reposicao interna'],
        ]);

        $this->assertSame('TRANSFER_OUT', (string) ($transfer['source_movement']['movement_type'] ?? ''));
        $this->assertSame('TRANSFER_IN', (string) ($transfer['target_movement']['movement_type'] ?? ''));
        $this->assertSame(7.0, (float) ($transfer['source_balance']['qty_available'] ?? 0));
        $this->assertSame(5.0, (float) ($transfer['target_balance']['qty_available'] ?? 0));

        $reversal = $service->reverseMovement([
            'movement_id' => $transfer['source_movement']['id'],
            'reason' => 'cancelamento da transferencia',
        ]);

        $this->assertSame('TRANSFER_OUT', (string) ($reversal['source_movement']['movement_type'] ?? ''));
        $this->assertSame('TRANSFER_IN', (string) ($reversal['target_movement']['movement_type'] ?? ''));
        $this->assertSame(0.0, (float) ($reversal['source_balance']['qty_available'] ?? 0));
        $this->assertSame(12.0, (float) ($reversal['target_balance']['qty_available'] ?? 0));

        $this->assertDatabaseHas('inventory_balances', [
            'company_id' => $company->id,
            'warehouse_id' => $sourceWarehouse->id,
            'product_id' => $product->id,
            'qty_available' => 12.0,
        ]);

        $this->assertDatabaseHas('inventory_balances', [
            'company_id' => $company->id,
            'warehouse_id' => $targetWarehouse->id,
            'product_id' => $product->id,
            'qty_available' => 0.0,
        ]);

        $this->assertDatabaseHas('stock_ledger_movements', [
            'company_id' => $company->id,
            'warehouse_id' => $sourceWarehouse->id,
            'product_id' => $product->id,
            'movement_type' => 'TRANSFER_OUT',
        ]);
    }

    public function test_reverse_receipt_movement_creates_issue_and_restores_balance(): void
    {
        [, $warehouse, , $product] = $this->inventoryContext();

        $service = app(InventoryService::class);

        $service->upsertBalance([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'qty_available' => 2,
            'qty_reserved' => 0,
            'qty_in_transit' => 0,
            'qty_inspection' => 0,
        ]);

        $receipt = $service->postMovement([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'movement_type' => 'RECEIPT',
            'quantity' => 4,
            'reference_type' => 'purchase_receipt',
            'reference_id' => 99,
            'movement_at' => now()->toDateTimeString(),
        ]);

        $reversal = $service->reverseMovement([
            'movement_id' => $receipt['movement']['id'],
            'reason' => 'devolucao do recebimento',
        ]);

        $this->assertSame('ISSUE', (string) ($reversal['reversal_movement']['movement_type'] ?? ''));
        $this->assertSame(2.0, (float) ($reversal['balance']['qty_available'] ?? 0));
    }

    public function test_reservation_uses_origin_and_priority_and_auto_releases_when_expired(): void
    {
        [, $warehouse, , $product] = $this->inventoryContext();

        $service = app(InventoryService::class);

        $service->upsertBalance([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'qty_available' => 8,
            'qty_reserved' => 0,
            'qty_in_transit' => 0,
            'qty_inspection' => 0,
        ]);

        $reservation = $service->reserveStock([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'reservation_origin' => 'PRODUCTION',
            'priority' => 5,
            'quantity' => 3,
            'expires_at' => now()->subMinute()->toDateTimeString(),
            'metadata' => ['work_order' => 'PO-100'],
        ]);

        $this->assertSame('PRODUCTION', (string) ($reservation['reservation']['reservation_origin'] ?? ''));
        $this->assertSame(5, (int) ($reservation['reservation']['priority'] ?? 0));
        $this->assertSame(5.0, (float) ($reservation['balance']['qty_available'] ?? 0));
        $this->assertSame(3.0, (float) ($reservation['balance']['qty_reserved'] ?? 0));

        $releasedCount = $service->releaseExpiredReservations();

        $this->assertSame(1, $releasedCount);

        $reservationRow = InventoryReservation::query()->findOrFail($reservation['reservation']['id']);
        $this->assertSame('RELEASED', $reservationRow->status);

        $this->assertDatabaseHas('inventory_balances', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'qty_available' => 8.0,
            'qty_reserved' => 0.0,
        ]);
    }

    /**
     * @return array{0: Company, 1: Warehouse, 2: Warehouse, 3: Product}
     */
    private function inventoryContext(): array
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

        $sourceWarehouse = Warehouse::query()->create([
            'company_id' => $company->id,
            'plant_id' => $plant->id,
            'name' => 'Almoxarifado A',
            'code' => 'WH-A',
            'is_active' => true,
        ]);

        $targetWarehouse = Warehouse::query()->create([
            'company_id' => $company->id,
            'plant_id' => $plant->id,
            'name' => 'Almoxarifado B',
            'code' => 'WH-B',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'sku' => 'STK-001',
            'description' => 'Produto de teste de estoque',
            'product_type' => 'FG',
            'uom' => 'UN',
            'lot_control' => false,
            'serial_control' => false,
            'is_active' => true,
        ]);

        return [$company, $sourceWarehouse, $targetWarehouse, $product];
    }
}