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
use App\Modules\Sales\Infrastructure\Persistence\Models\Sale;
use App\Modules\Sales\Infrastructure\Persistence\Models\SaleLine;
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

    public function test_sale_materials_page_nets_linked_and_free_stock_across_bom_levels(): void
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
            'operational_status' => 'PENDING',
            'subtotal_cents' => 2000,
            'discount_cents' => 0,
            'amount_cents' => 2000,
        ]);
        SaleLine::query()->create([
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
            ->assertSee(route('sales.materials', $sale), false);

        $this->actingAs($user, 'web')
            ->get(route('sales.materials', $sale))
            ->assertOk()
            ->assertSee('RAW-001')
            ->assertSee('WH-01')
            ->assertViewHas('analysis', function (array $analysis) use ($intermediate, $rawMaterial): bool {
                $finished = $analysis['finished_products'][0];
                $materials = collect($analysis['materials'])->keyBy('product_id');
                $wip = $materials->get($intermediate->id);
                $raw = $materials->get($rawMaterial->id);

                return $finished['required_quantity'] === 2.0
                    && $finished['linked_quantity'] === 0.5
                    && $finished['available_to_link'] === 0.5
                    && $finished['quantity_to_produce'] === 1.0
                    && $wip['required_quantity'] === 2.0
                    && $wip['available_to_link'] === 1.0
                    && $wip['shortage_quantity'] === 1.0
                    && $wip['recommended_action'] === 'PRODUCE'
                    && $raw['required_quantity'] === 7.0
                    && $raw['linked_quantity'] === 1.0
                    && $raw['available_to_link'] === 5.0
                    && $raw['shortage_quantity'] === 1.0
                    && $raw['recommended_action'] === 'BUY'
                    && $analysis['purchase_items_count'] === 1
                    && $analysis['production_items_count'] === 1;
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
