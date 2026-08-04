<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SaaS\Organization;
use App\Models\SaaS\Trial;
use App\Modules\Customer\Infrastructure\Persistence\Models\Customer;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Sales\Infrastructure\Persistence\Models\Sale;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
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
                'tax_amount' => '0,00',
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
                'tax_amount' => '0,00',
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
                'tax_amount' => '5,50',
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
            'tax_cents' => 550,
            'amount_cents' => 33630,
        ]);

        $this->assertDatabaseHas('sale_lines', [
            'company_id' => $company->id,
            'product_id' => $productA->id,
        ]);

        $this->assertDatabaseHas('sale_lines', [
            'company_id' => $company->id,
            'product_id' => $productB->id,
        ]);

        $sale = Sale::query()->firstOrFail();

        $this->actingAs($user, 'web')
            ->get(route('sales.index'))
            ->assertOk()
            ->assertSee('#'.$sale->id)
            ->assertSee('Cliente Atlas')
            ->assertSee('336,30')
            ->assertSee('PENDING', false);
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

        $user->companies()->attach($company->id, ['is_default' => true]);

        $role = Role::query()->withoutGlobalScope('tenant')->create([
            'company_id' => $company->id,
            'name' => 'Master',
            'slug' => 'master',
        ]);

        $user->roles()->attach($role->id, ['company_id' => $company->id]);

        $organization = Organization::query()->create([
            'company_id' => $company->id,
            'name' => 'Atlas Components',
            'slug' => 'atlas-components',
            'timezone' => 'UTC',
        ]);

        Trial::query()->create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
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
            'safety_stock' => 0,
            'lead_time_days' => 0,
            'lot_control' => false,
            'serial_control' => false,
            'is_active' => true,
        ]);

        return compact('company', 'user', 'customer', 'productA', 'productB');
    }
}