<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SaaS\Trial;
use App\Modules\Bom\Infrastructure\Persistence\Models\BomHeader;
use App\Modules\Bom\Infrastructure\Persistence\Models\BomItem;
use App\Modules\Customer\Infrastructure\Persistence\Models\Customer;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryBalance;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryReservation;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderMaterialConsumption;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderOperation;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\SupplierProduct;
use App\Modules\Sales\Infrastructure\Persistence\Models\Sale;
use App\Modules\Sales\Infrastructure\Persistence\Models\SaleLine;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenterHourRate;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantSalesManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoiced_order_blocks_editing_but_allows_next_operational_transition(): void
    {
        ['company' => $company, 'user' => $user, 'customer' => $customer, 'productA' => $productA] = $this->salesContext();

        $this->actingAs($user, 'web')
            ->post(route('sales.store'), [
                'customer_id' => $customer->id,
                'sale_date' => '2026-08-03',
                'status' => 'CONFIRMED',
                'discount_amount' => '0,00',
                'notes' => 'Pedido operacional.',
                'items' => [[
                    'product_id' => $productA->id,
                    'quantity' => '1',
                    'unit_price' => '10,00',
                ]],
            ])
            ->assertRedirect(route('sales.index'));

        $sale = Sale::query()->firstOrFail();

        $this->assertSame('PENDING', $sale->operational_status);

        $this->actingAs($user, 'web')
            ->post(route('sales.transition', $sale), [
                'next_operational_status' => 'PICKING',
            ])
            ->assertRedirect(route('sales.show', $sale));

        $sale->refresh();

        $this->assertSame('PICKING', $sale->operational_status);
        $this->assertNotNull($sale->picking_at);
        $this->assertSame($user->id, $sale->picking_by);

        $this->actingAs($user, 'web')
            ->post(route('sales.transition', $sale), [
                'next_operational_status' => 'INVOICED',
            ])
            ->assertRedirect(route('sales.show', $sale));

        $sale->refresh();

        $this->assertSame('INVOICED', $sale->operational_status);
        $this->assertNotNull($sale->invoiced_at);
        $this->assertSame($user->id, $sale->invoiced_by);

        $this->actingAs($user, 'web')
            ->get(route('sales.edit', $sale))
            ->assertRedirect(route('sales.show', $sale));

        $this->actingAs($user, 'web')
            ->put(route('sales.update', $sale), [
                'customer_id' => $customer->id,
                'sale_date' => '2026-08-04',
                'status' => 'CONFIRMED',
                'operational_status' => 'INVOICED',
                'discount_amount' => '0,00',
                'notes' => 'Tentativa de edição bloqueada.',
                'items' => [[
                    'product_id' => $productA->id,
                    'quantity' => '1',
                    'unit_price' => '99,00',
                ]],
            ])
            ->assertRedirect(route('sales.show', $sale));

        $sale->refresh();

        $this->assertSame('2026-08-03', $sale->sale_date->format('Y-m-d'));
        $this->assertSame('Pedido operacional.', $sale->notes);

        $this->actingAs($user, 'web')
            ->post(route('sales.transition', $sale), [
                'next_operational_status' => 'SHIPPED',
            ])
            ->assertRedirect(route('sales.show', $sale));

        $sale->refresh();

        $this->assertSame('SHIPPED', $sale->operational_status);
        $this->assertNotNull($sale->shipped_at);
        $this->assertSame($user->id, $sale->shipped_by);

        $this->actingAs($user, 'web')
            ->post(route('sales.transition', $sale), [
                'next_operational_status' => 'DELIVERED',
            ])
            ->assertRedirect(route('sales.show', $sale));

        $sale->refresh();

        $this->assertSame('DELIVERED', $sale->operational_status);
        $this->assertNotNull($sale->delivered_at);
        $this->assertSame($user->id, $sale->delivered_by);
    }

    public function test_company_administrator_can_create_and_list_sales(): void
    {
        ['company' => $company, 'user' => $user, 'customer' => $customer, 'productA' => $productA, 'productB' => $productB] = $this->salesContext();

        $this->actingAs($user, 'web')
            ->post(route('sales.store'), [
                'customer_id' => $customer->id,
                'sale_date' => '2026-08-03',
                'status' => 'CONFIRMED',
                'discount_amount' => '20,00',
                'notes' => 'Primeira venda do cliente.',
                'items' => [
                    [
                        'product_id' => $productA->id,
                        'quantity' => '2',
                        'unit_price' => '100,25',
                    ],
                    [
                        'product_id' => $productB->id,
                        'quantity' => '3',
                        'unit_price' => '50,10',
                    ],
                ],
            ])
            ->assertRedirect(route('sales.index'));

        $this->assertDatabaseHas('sales', [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'status' => 'CONFIRMED',
            'operational_status' => 'PENDING',
            'subtotal_cents' => 35080,
            'discount_cents' => 2000,
            'amount_cents' => 33080,
        ]);

        $sale = Sale::query()->firstOrFail();

        $this->assertNotNull($sale->confirmed_at);
        $this->assertSame($user->id, $sale->confirmed_by);

        $this->assertDatabaseHas('sale_lines', [
            'company_id' => $company->id,
            'product_id' => $productA->id,
        ]);

        $this->assertDatabaseHas('sale_lines', [
            'company_id' => $company->id,
            'product_id' => $productB->id,
        ]);

        $this->actingAs($user, 'web')
            ->get(route('sales.index'))
            ->assertOk()
            ->assertSee('#'.$sale->id)
            ->assertSee('Cliente Atlas')
            ->assertSee('330,80')
            ->assertSee('PENDING', false);
    }

    public function test_cancellation_requires_reason_and_records_audit_before_invoicing(): void
    {
        ['user' => $user, 'customer' => $customer, 'productA' => $productA] = $this->salesContext();

        $this->actingAs($user, 'web')
            ->post(route('sales.store'), [
                'customer_id' => $customer->id,
                'sale_date' => '2026-08-03',
                'status' => 'CONFIRMED',
                'discount_amount' => '0,00',
                'notes' => 'Pedido cancelável.',
                'items' => [[
                    'product_id' => $productA->id,
                    'quantity' => '1',
                    'unit_price' => '12,00',
                ]],
            ])
            ->assertRedirect(route('sales.index'));

        $sale = Sale::query()->firstOrFail();

        $this->actingAs($user, 'web')
            ->from(route('sales.edit', $sale))
            ->put(route('sales.update', $sale), [
                'customer_id' => $customer->id,
                'sale_date' => '2026-08-03',
                'status' => 'CANCELLED',
                'discount_amount' => '0,00',
                'cancel_reason' => '',
                'notes' => 'Pedido cancelável.',
                'items' => [[
                    'product_id' => $productA->id,
                    'quantity' => '1',
                    'unit_price' => '12,00',
                ]],
            ])
            ->assertRedirect(route('sales.edit', $sale))
            ->assertSessionHasErrors('cancel_reason');

        $this->actingAs($user, 'web')
            ->put(route('sales.update', $sale), [
                'customer_id' => $customer->id,
                'sale_date' => '2026-08-03',
                'status' => 'CANCELLED',
                'discount_amount' => '0,00',
                'cancel_reason' => 'Cliente desistiu da compra.',
                'notes' => 'Pedido cancelável.',
                'items' => [[
                    'product_id' => $productA->id,
                    'quantity' => '1',
                    'unit_price' => '12,00',
                ]],
            ])
            ->assertRedirect(route('sales.index'));

        $sale->refresh();

        $this->assertSame('CANCELLED', $sale->status);
        $this->assertSame('Cliente desistiu da compra.', $sale->cancel_reason);
        $this->assertNotNull($sale->canceled_at);
        $this->assertSame($user->id, $sale->canceled_by);
    }

    public function test_cannot_cancel_order_after_invoicing(): void
    {
        ['user' => $user, 'customer' => $customer, 'productA' => $productA] = $this->salesContext();

        $this->actingAs($user, 'web')
            ->post(route('sales.store'), [
                'customer_id' => $customer->id,
                'sale_date' => '2026-08-03',
                'status' => 'CONFIRMED',
                'discount_amount' => '0,00',
                'notes' => 'Pedido não cancelável após faturamento.',
                'items' => [[
                    'product_id' => $productA->id,
                    'quantity' => '1',
                    'unit_price' => '20,00',
                ]],
            ])
            ->assertRedirect(route('sales.index'));

        $sale = Sale::query()->firstOrFail();

        $this->actingAs($user, 'web')
            ->post(route('sales.transition', $sale), ['next_operational_status' => 'PICKING'])
            ->assertRedirect(route('sales.show', $sale));

        $this->actingAs($user, 'web')
            ->post(route('sales.transition', $sale), ['next_operational_status' => 'INVOICED'])
            ->assertRedirect(route('sales.show', $sale));

        $sale->refresh();

        $this->actingAs($user, 'web')
            ->put(route('sales.update', $sale), [
                'customer_id' => $customer->id,
                'sale_date' => '2026-08-03',
                'status' => 'CANCELLED',
                'discount_amount' => '0,00',
                'cancel_reason' => 'Tentativa tardia de cancelamento.',
                'notes' => 'Pedido não cancelável após faturamento.',
                'items' => [[
                    'product_id' => $productA->id,
                    'quantity' => '1',
                    'unit_price' => '20,00',
                ]],
            ])
            ->assertSessionHasErrors('status');

        $sale->refresh();

        $this->assertSame('CONFIRMED', $sale->status);
        $this->assertNull($sale->canceled_at);
        $this->assertNull($sale->canceled_by);
    }

    public function test_transition_requires_recorded_previous_operational_step(): void
    {
        ['user' => $user, 'customer' => $customer, 'productA' => $productA] = $this->salesContext();

        $this->actingAs($user, 'web')
            ->post(route('sales.store'), [
                'customer_id' => $customer->id,
                'sale_date' => '2026-08-03',
                'status' => 'CONFIRMED',
                'discount_amount' => '0,00',
                'notes' => 'Pedido inconsistente.',
                'items' => [[
                    'product_id' => $productA->id,
                    'quantity' => '1',
                    'unit_price' => '15,00',
                ]],
            ])
            ->assertRedirect(route('sales.index'));

        $sale = Sale::query()->firstOrFail();

        $sale->forceFill([
            'operational_status' => 'INVOICED',
            'picking_at' => now()->subHour(),
            'picking_by' => $user->id,
            'invoiced_at' => null,
            'invoiced_by' => null,
        ])->save();

        $this->actingAs($user, 'web')
            ->post(route('sales.transition', $sale), [
                'next_operational_status' => 'SHIPPED',
            ])
            ->assertRedirect(route('sales.show', $sale))
            ->assertSessionHas('error');

        $sale->refresh();
        $this->assertSame('INVOICED', $sale->operational_status);
        $this->assertNull($sale->shipped_at);

        $sale->forceFill([
            'operational_status' => 'SHIPPED',
            'invoiced_at' => now()->subMinutes(30),
            'invoiced_by' => $user->id,
            'shipped_at' => null,
            'shipped_by' => null,
        ])->save();

        $this->actingAs($user, 'web')
            ->post(route('sales.transition', $sale), [
                'next_operational_status' => 'DELIVERED',
            ])
            ->assertRedirect(route('sales.show', $sale))
            ->assertSessionHas('error');

        $sale->refresh();
        $this->assertSame('SHIPPED', $sale->operational_status);
        $this->assertNull($sale->delivered_at);
    }

    public function test_sale_production_page_consolidates_coverage_and_stock_across_bom_levels(): void
    {
        ['company' => $company, 'user' => $user, 'customer' => $customer, 'productA' => $finishedProduct] = $this->salesContext();
        $unit = $finishedProduct->unit;

        $intermediate = Product::query()->create([
            'company_id' => $company->id,
            'sku' => 'WIP-001',
            'description' => 'Conjunto intermediário',
            'product_type' => 'WIP',
            'unit_id' => $unit->id,
            'safety_stock' => 0,
            'lead_time_days' => 0,
            'lot_control' => false,
            'serial_control' => false,
            'is_active' => true,
        ]);
        $rawMaterial = Product::query()->create([
            'company_id' => $company->id,
            'sku' => 'RAW-001',
            'description' => 'Matéria-prima principal',
            'product_type' => 'RAW',
            'unit_id' => $unit->id,
            'safety_stock' => 0,
            'lead_time_days' => 0,
            'lot_control' => false,
            'serial_control' => false,
            'is_active' => true,
        ]);

        $rootBom = BomHeader::query()->create([
            'company_id' => $company->id,
            'product_id' => $finishedProduct->id,
            'version_number' => 1,
            'status' => 'APPROVED',
            'effective_from' => '2026-01-01',
        ]);
        BomItem::query()->create([
            'company_id' => $company->id,
            'bom_header_id' => $rootBom->id,
            'component_product_id' => $intermediate->id,
            'line_no' => 1,
            'quantity_per' => 2,
            'uom' => $unit->code,
        ]);
        BomItem::query()->create([
            'company_id' => $company->id,
            'bom_header_id' => $rootBom->id,
            'component_product_id' => $rawMaterial->id,
            'line_no' => 2,
            'quantity_per' => 3,
            'uom' => $unit->code,
        ]);

        $intermediateBom = BomHeader::query()->create([
            'company_id' => $company->id,
            'product_id' => $intermediate->id,
            'version_number' => 1,
            'status' => 'APPROVED',
            'effective_from' => '2026-01-01',
        ]);
        BomItem::query()->create([
            'company_id' => $company->id,
            'bom_header_id' => $intermediateBom->id,
            'component_product_id' => $rawMaterial->id,
            'line_no' => 1,
            'quantity_per' => 4,
            'uom' => $unit->code,
        ]);

        $plant = Plant::query()->create([
            'company_id' => $company->id,
            'name' => 'Fábrica principal',
            'code' => 'PLT-01',
            'timezone' => 'America/Sao_Paulo',
            'is_active' => true,
        ]);
        $warehouse = Warehouse::query()->create([
            'company_id' => $company->id,
            'plant_id' => $plant->id,
            'name' => 'Estoque central',
            'code' => 'WH-01',
            'is_active' => true,
        ]);

        foreach ([
            [$finishedProduct, 0.5, 0.5],
            [$intermediate, 1, 0],
            [$rawMaterial, 5, 1],
        ] as [$product, $available, $reserved]) {
            InventoryBalance::query()->create([
                'company_id' => $company->id,
                'warehouse_id' => $warehouse->id,
                'product_id' => $product->id,
                'qty_available' => $available,
                'qty_reserved' => $reserved,
            ]);
        }

        $sale = Sale::query()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'sale_date' => '2026-08-03',
            'status' => 'CONFIRMED',
            'confirmed_at' => now(),
            'operational_status' => 'PENDING',
            'subtotal_cents' => 2000,
            'discount_cents' => 0,
            'amount_cents' => 2000,
        ]);
        $line = SaleLine::query()->create([
            'company_id' => $company->id,
            'sale_id' => $sale->id,
            'product_id' => $finishedProduct->id,
            'quantity' => 2,
            'unit_price' => 10,
        ]);

        foreach ([
            [$finishedProduct, 'SALE', 0.5, 'finished_good'],
            [$rawMaterial, 'PRODUCTION', 1, 'production_component'],
        ] as [$product, $origin, $quantity, $allocationType]) {
            InventoryReservation::query()->create([
                'company_id' => $company->id,
                'warehouse_id' => $warehouse->id,
                'product_id' => $product->id,
                'reservation_origin' => $origin,
                'priority' => 10,
                'quantity' => $quantity,
                'status' => 'RESERVED',
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'reserved_at' => now(),
                'metadata' => ['allocation_type' => $allocationType],
            ]);
        }

        $this->actingAs($user, 'web')
            ->get(route('sales.show', $sale))
            ->assertOk()
            ->assertSee(route('sales.production-status', $sale), false)
            ->assertDontSee(route('sales.materials', $sale), false);

        $this->actingAs($user, 'web')
            ->get(route('sales.materials', $sale))
            ->assertRedirect(route('sales.production-status', $sale));

        $this->actingAs($user, 'web')
            ->get(route('sales.production-status', $sale))
            ->assertOk()
            ->assertSee('WIP-001')
            ->assertSee('RAW-001')
            ->assertSee('WH-01')
            ->assertSee(__('sale.production_status.create_order'))
            ->assertSee(__('sale.production_status.create_requisition'))
            ->assertViewHas('analysis', function (array $analysis) use ($finishedProduct, $intermediate, $rawMaterial): bool {
                $item = $analysis['items'][0];
                $forecastProductIds = collect($item['forecasts'])->pluck('product_id');
                $materials = collect($item['materials'])->keyBy('product_id');
                $wip = $materials->get($intermediate->id);
                $raw = $materials->get($rawMaterial->id);

                return $item['production_status'] === 'forecast'
                    && $analysis['readiness'] === 'blocked_materials'
                    && $analysis['progress_percent'] === 0.0
                    && $analysis['schedule_incomplete'] === true
                    && collect($analysis['alerts'])->pluck('key')->contains('materials_to_buy')
                    && collect($analysis['alerts'])->pluck('key')->contains('orders_not_created')
                    && collect($analysis['timeline'])->pluck('type')->contains('sale_confirmed')
                    && $item['coverage']['required_quantity'] === 2.0
                    && $item['coverage']['linked_quantity'] === 0.5
                    && $item['coverage']['available_to_link'] === 0.5
                    && $item['coverage']['quantity_to_produce'] === 1.0
                    && $item['counts']['forecast'] === 2
                    && $forecastProductIds->contains($finishedProduct->id)
                    && $forecastProductIds->contains($intermediate->id)
                    && $wip['required_quantity'] === 2.0
                    && $wip['available_to_link'] === 1.0
                    && $wip['shortage_quantity'] === 1.0
                    && $wip['recommended_action'] === 'PRODUCE'
                    && $raw['required_quantity'] === 7.0
                    && $raw['linked_quantity'] === 1.0
                    && $raw['available_to_link'] === 5.0
                    && $raw['shortage_quantity'] === 1.0
                    && $raw['recommended_action'] === 'BUY'
                    && $item['counts']['materials_short'] === 2
                    && $item['counts']['to_buy'] === 1
                    && $item['counts']['to_produce'] === 1;
            });

        $this->actingAs($user, 'web')
            ->get(route('production.orders.create', [
                'sale_id' => $sale->id,
                'sale_line_id' => $line->id,
                'product_id' => $intermediate->id,
                'quantity_planned' => 1,
                'dependency_level' => 1,
            ]))
            ->assertOk()
            ->assertViewHas('creationContext', fn (?array $context): bool => $context === [
                'sale_id' => $sale->id,
                'sale_line_id' => $line->id,
                'root_product_id' => $finishedProduct->id,
                'dependency_level' => 1,
            ])
            ->assertViewHas('initialValues', fn (array $values): bool => $values['quantity_planned'] === 1.0);

        $this->actingAs($user, 'web')
            ->post(route('production.orders.store'), [
                'sale_id' => $sale->id,
                'sale_line_id' => $line->id,
                'dependency_level' => 1,
                'product_id' => $intermediate->id,
                'warehouse_id' => $warehouse->id,
                'quantity_planned' => 1,
            ])
            ->assertSessionHasNoErrors();

        $createdOrder = ProductionOrder::query()->latest('id')->firstOrFail();
        $this->assertSame($sale->id, $createdOrder->source_reference_id);
        $this->assertSame('sale', $createdOrder->source_reference_type);
        $this->assertSame($line->id, (int) data_get($createdOrder->metadata, 'sale_line_id'));
        $this->assertSame(1, (int) data_get($createdOrder->metadata, 'dependency_level'));

        $this->actingAs($user, 'web')
            ->get(route('purchasing.requisitions.create', [
                'sale_id' => $sale->id,
                'sale_line_id' => $line->id,
                'product_id' => $rawMaterial->id,
                'quantity' => 1,
                'warehouse_id' => $warehouse->id,
            ]))
            ->assertOk()
            ->assertViewHas('creationContext', fn (?array $context): bool => $context['sale_id'] === $sale->id)
            ->assertViewHas('lineRows', fn (array $rows): bool => $rows[0]['product_id'] === $rawMaterial->id
                && $rows[0]['warehouse_id'] === $warehouse->id
                && $rows[0]['quantity'] === 1.0);

        $this->actingAs($user, 'web')
            ->post(route('purchasing.requisitions.store'), [
                'required_date' => now()->addDays(7)->toDateString(),
                'status' => 'DRAFT',
                'source_type' => 'sale',
                'source_reference_id' => $sale->id,
                'source_reference_type' => 'sale',
                'items' => [[
                    'product_id' => $rawMaterial->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => 1,
                    'need_by_date' => now()->addDays(7)->toDateString(),
                    'order_date' => now()->toDateString(),
                ]],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('purchase_requisitions', [
            'company_id' => $company->id,
            'source_reference_id' => $sale->id,
            'source_reference_type' => 'sale',
        ]);
    }

    public function test_sale_production_status_page_explodes_orders_materials_and_costs(): void
    {
        ['company' => $company, 'user' => $user, 'customer' => $customer, 'productA' => $finishedProduct] = $this->salesContext();
        $unit = $finishedProduct->unit;
        $rawMaterial = Product::query()->create([
            'company_id' => $company->id,
            'sku' => 'RAW-COST-001',
            'description' => 'Material com custo',
            'product_type' => 'RAW',
            'unit_id' => $unit->id,
            'safety_stock' => 0,
            'lead_time_days' => 0,
            'lot_control' => false,
            'serial_control' => false,
            'is_active' => true,
        ]);

        $bom = BomHeader::query()->create([
            'company_id' => $company->id,
            'product_id' => $finishedProduct->id,
            'version_number' => 1,
            'status' => 'APPROVED',
            'effective_from' => '2026-01-01',
        ]);
        BomItem::query()->create([
            'company_id' => $company->id,
            'bom_header_id' => $bom->id,
            'component_product_id' => $rawMaterial->id,
            'line_no' => 1,
            'quantity_per' => 2,
            'uom' => $unit->code,
        ]);

        $plant = Plant::query()->create([
            'company_id' => $company->id,
            'name' => 'Planta de custos',
            'code' => 'PLT-COST',
            'timezone' => 'America/Sao_Paulo',
            'is_active' => true,
        ]);
        $warehouse = Warehouse::query()->create([
            'company_id' => $company->id,
            'plant_id' => $plant->id,
            'name' => 'Estoque de custos',
            'code' => 'WH-COST',
            'is_active' => true,
        ]);
        $workCenter = WorkCenter::query()->create([
            'company_id' => $company->id,
            'plant_id' => $plant->id,
            'code' => 'WC-COST',
            'name' => 'Centro de custos',
            'resource_type' => 'MACHINE',
            'capacity_per_day' => 480,
            'efficiency_factor' => 100,
            'is_active' => true,
        ]);
        WorkCenterHourRate::query()->create([
            'company_id' => $company->id,
            'work_center_id' => $workCenter->id,
            'hourly_rate' => 120,
            'currency' => 'BRL',
            'effective_from' => '2026-01-01',
            'status' => 'ACTIVE',
        ]);

        $supplier = Supplier::query()->create([
            'company_id' => $company->id,
            'code' => 'SUP-COST',
            'name' => 'Fornecedor de custos',
            'person_type' => 'PJ',
            'status' => 'ACTIVE',
            'default_lead_time_days' => 2,
        ]);
        SupplierProduct::query()->create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'product_id' => $rawMaterial->id,
            'moq' => 1,
            'lead_time_days' => 2,
            'unit_price' => 5,
            'is_preferred' => true,
            'is_active' => true,
        ]);

        $sale = Sale::query()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'sale_date' => '2026-08-03',
            'status' => 'CONFIRMED',
            'confirmed_at' => now(),
            'operational_status' => 'PENDING',
            'subtotal_cents' => 30000,
            'discount_cents' => 0,
            'amount_cents' => 30000,
        ]);
        $line = SaleLine::query()->create([
            'company_id' => $company->id,
            'sale_id' => $sale->id,
            'product_id' => $finishedProduct->id,
            'quantity' => 3,
            'unit_price' => 100,
        ]);
        $order = ProductionOrder::query()->create([
            'company_id' => $company->id,
            'product_id' => $finishedProduct->id,
            'warehouse_id' => $warehouse->id,
            'source_type' => 'SALE',
            'source_reference_id' => $sale->id,
            'source_reference_type' => 'sale',
            'order_number' => 'OP-SALE-COST',
            'status' => 'PARTIALLY_COMPLETED',
            'quantity_planned' => 3,
            'quantity_produced' => 1.5,
            'quantity_scrapped' => 0,
            'scheduled_start_date' => now()->subDays(2)->toDateString(),
            'scheduled_end_date' => now()->addDays(5)->toDateString(),
            'metadata' => [
                'sale_line_id' => $line->id,
                'root_product_id' => $finishedProduct->id,
                'dependency_level' => 0,
            ],
        ]);
        ProductionOrderOperation::query()->create([
            'company_id' => $company->id,
            'production_order_id' => $order->id,
            'operation_no' => 10,
            'operation_code' => 'OP10',
            'operation_name' => 'Produzir item vendido',
            'sequence' => 1,
            'work_center_id' => $workCenter->id,
            'status' => 'IN_PROGRESS',
            'quantity_planned' => 3,
            'setup_scope' => 'ROUTING',
            'setup_time_minutes' => 30,
            'runtime_time_minutes' => 60,
            'productive_time_minutes' => 90,
            'total_time_minutes' => 90,
            'actual_productive_minutes' => 30,
        ]);
        ProductionOrderMaterialConsumption::query()->create([
            'company_id' => $company->id,
            'production_order_id' => $order->id,
            'product_id' => $rawMaterial->id,
            'warehouse_id' => $warehouse->id,
            'quantity_consumed' => 2,
            'quantity_scrapped' => 0,
            'consumed_at' => now(),
        ]);

        $this->actingAs($user, 'web')
            ->get(route('sales.index'))
            ->assertOk()
            ->assertSee(route('sales.production-status', $sale), false);

        $this->actingAs($user, 'web')
            ->get(route('sales.production-status', $sale))
            ->assertOk()
            ->assertSee('OP-SALE-COST')
            ->assertSee('RAW-COST-001')
            ->assertViewHas('analysis', function (array $analysis): bool {
                $item = $analysis['items'][0];

                return $item['production_status'] === 'in_progress'
                    && $analysis['readiness'] === 'in_progress'
                    && $analysis['progress_percent'] === 50.0
                    && $analysis['projected_completion'] === now()->addDays(5)->toDateString()
                    && $analysis['costs']['variance'] === -140.0
                    && $analysis['costs']['variance_percent'] === -66.7
                    && collect($analysis['timeline'])->pluck('type')->contains('order_created')
                    && $item['counts']['in_progress'] === 1
                    && $item['costs']['estimated_material'] === 30.0
                    && $item['costs']['estimated_production'] === 180.0
                    && $item['costs']['estimated_total'] === 210.0
                    && $item['costs']['actual_material'] === 10.0
                    && $item['costs']['actual_production'] === 60.0
                    && $item['costs']['actual_total'] === 70.0
                    && $item['costs']['estimated_incomplete'] === false
                    && $item['costs']['actual_incomplete'] === false;
            });
    }

    /**
     * @return array{company: Company, user: User, customer: Customer, productA: Product, productB: Product}
     */
    private function salesContext(): array
    {
        $company = Company::query()->create([
            'name' => 'Atlas Components',
            'code' => 'ATL',
            'is_active' => true,
        ]);

        $user = User::query()->create([
            'name' => 'Master User',
            'email' => 'master@atlas.test',
            'password' => 'Strong!Pass123',
            'current_company_id' => $company->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($company->id);

        $role = Role::query()->withoutGlobalScope('tenant')->create([
            'company_id' => $company->id,
            'name' => 'Master',
            'slug' => 'master',
        ]);

        $user->roles()->attach($role->id, ['company_id' => $company->id]);

        Trial::query()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'trial_start_date' => now()->subDay(),
            'trial_end_date' => now()->addDays(10),
            'status' => 'active',
            'is_expired' => false,
        ]);

        $customer = Customer::query()->create([
            'company_id' => $company->id,
            'code' => 'CUS-001',
            'name' => 'Cliente Atlas',
            'person_type' => 'PJ',
            'status' => 'ACTIVE',
        ]);

        $productA = Product::query()->create([
            'company_id' => $company->id,
            'sku' => 'PRD-001',
            'description' => 'Produto A',
            'product_type' => 'FG',
            'uom' => 'UN',
            'unit_id' => Unit::query()->create([
                'company_id' => $company->id,
                'code' => 'UN',
                'name' => 'Unidade',
                'is_active' => true,
            ])->id,
            'safety_stock' => 0,
            'lead_time_days' => 0,
            'lot_control' => false,
            'serial_control' => false,
            'is_active' => true,
        ]);

        $productB = Product::query()->create([
            'company_id' => $company->id,
            'sku' => 'PRD-002',
            'description' => 'Produto B',
            'product_type' => 'FG',
            'uom' => 'UN',
            'unit_id' => Unit::query()->create([
                'company_id' => $company->id,
                'code' => 'UN2',
                'name' => 'Unidade 2',
                'is_active' => true,
            ])->id,
            'safety_stock' => 0,
            'lead_time_days' => 0,
            'lot_control' => false,
            'serial_control' => false,
            'is_active' => true,
        ]);

        return compact('company', 'user', 'customer', 'productA', 'productB');
    }
}
