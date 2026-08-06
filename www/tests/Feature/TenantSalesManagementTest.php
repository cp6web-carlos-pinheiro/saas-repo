<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SaaS\Trial;
use App\Modules\Customer\Infrastructure\Persistence\Models\Customer;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Sales\Infrastructure\Persistence\Models\Sale;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
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
