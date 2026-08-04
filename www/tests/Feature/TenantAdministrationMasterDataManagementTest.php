<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SaaS\Organization;
use App\Models\SaaS\Trial;
use App\Modules\Customer\Infrastructure\Persistence\Models\Customer;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisition;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Sales\Infrastructure\Persistence\Models\Sale;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Branch;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\MasterDataRecord;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Modules\Tenant\Infrastructure\Persistence\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantAdministrationMasterDataManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_administrator_can_manage_admin_data_and_use_it_in_transactions(): void
    {
        ['company' => $company, 'user' => $user] = $this->contextWithRole('master');

        $this->actingAs($user, 'web')
            ->post(route('inventory.branches.store'), [
                'name' => 'Filial Sul',
                'code' => 'BR-SUL',
                'is_active' => '1',
            ])
            ->assertRedirect(route('inventory.branches.index'));

        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $this->actingAs($user, 'web')
            ->post(route('inventory.plants.store'), [
                'name' => 'Planta Sul',
                'code' => 'PL-SUL',
                'branch_id' => $branch->id,
                'timezone' => 'UTC',
                'is_active' => '1',
            ])
            ->assertRedirect(route('inventory.plants.index'));

        $plant = Plant::query()->where('company_id', $company->id)->firstOrFail();

        $this->actingAs($user, 'web')
            ->post(route('inventory.warehouses.store'), [
                'name' => 'Armazem Sul',
                'code' => 'WH-SUL',
                'plant_id' => $plant->id,
                'is_active' => '1',
            ])
            ->assertRedirect(route('inventory.warehouses.index'));

        $warehouse = Warehouse::query()->where('company_id', $company->id)->firstOrFail();

        $this->actingAs($user, 'web')
            ->post(route('inventory.warehouse-locations.store'), [
                'name' => 'Endereco A1',
                'code' => 'A1',
                'warehouse_id' => $warehouse->id,
                'is_active' => '1',
            ])
            ->assertRedirect(route('inventory.warehouse-locations.index'));

        $this->assertDatabaseHas('warehouse_locations', [
            'company_id' => $company->id,
            'warehouse_id' => $warehouse->id,
            'code' => 'A1',
        ]);

        $this->actingAs($user, 'web')
            ->delete(route('inventory.branches.destroy', $branch))
            ->assertSessionHasErrors('branch');

        $this->assertDatabaseHas('branches', ['id' => $branch->id]);

        $departmentId = $this->createMasterData($user, 'departments', 'DEP-ENG', 'Engenharia');
        $costCenterId = $this->createMasterData($user, 'cost-centers', 'CC-100', 'Centro 100');
        $unitId = $this->createMasterData($user, 'units', 'UN', 'Unidade');
        $categoryId = $this->createMasterData($user, 'categories', 'CAT-RAW', 'Materia Prima');
        $brandId = $this->createMasterData($user, 'brands', 'BR-ATLAS', 'Atlas');
        $cfopId = $this->createMasterData($user, 'cfops', '5102', 'CFOP Padrao');
        $taxId = $this->createMasterData($user, 'taxes', 'TRB-STD', 'Tributo Padrao', 17.5);

        $this->actingAs($user, 'web')
            ->post(route('products.store'), [
                'sku' => 'P-ADM-001',
                'description' => 'Produto Administrativo',
                'product_type' => 'RAW',
                'unit_id' => $unitId,
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'uom' => '',
                'safety_stock' => 0,
                'lead_time_days' => 2,
                'lot_control' => '0',
                'serial_control' => '0',
                'is_active' => '1',
            ])
            ->assertRedirect(route('products.index'));

        $product = Product::query()->where('company_id', $company->id)->where('sku', 'P-ADM-001')->firstOrFail();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'unit_id' => $unitId,
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'uom' => 'UN',
        ]);

        $this->actingAs($user, 'web')
            ->post(route('purchasing.suppliers.store'), [
                'name' => 'Fornecedor Integrado',
                'person_type' => 'PJ',
                'tax_id' => '12.345.678/0001-95',
                'email' => 'fornecedor@atlas.test',
                'phone' => '11999999999',
                'status' => 'ACTIVE',
                'default_lead_time_days' => 2,
                'payment_terms' => '30D',
                'default_cfop_id' => $cfopId,
                'tax_profile_id' => $taxId,
            ])
            ->assertRedirect(route('purchasing.suppliers.index'));

        $supplier = Supplier::query()->where('company_id', $company->id)->where('name', 'Fornecedor Integrado')->firstOrFail();

        $this->actingAs($user, 'web')
            ->post(route('customers.store'), [
                'name' => 'Cliente Integrado',
                'person_type' => 'PJ',
                'tax_id' => '12.345.678/0001-95',
                'email' => 'cliente@atlas.test',
                'phone' => '11988888888',
                'status' => 'ACTIVE',
                'default_cfop_id' => $cfopId,
                'tax_profile_id' => $taxId,
            ])
            ->assertRedirect(route('customers.index'));

        $customer = Customer::query()->where('company_id', $company->id)->where('name', 'Cliente Integrado')->firstOrFail();

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'default_cfop_id' => $cfopId,
            'tax_profile_id' => $taxId,
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'default_cfop_id' => $cfopId,
            'tax_profile_id' => $taxId,
        ]);

        $this->actingAs($user, 'web')
            ->post(route('purchasing.requisitions.store'), [
                'required_date' => now()->addDays(7)->toDateString(),
                'department_id' => $departmentId,
                'cost_center_id' => $costCenterId,
                'status' => 'DRAFT',
                'source_type' => 'manual',
                'notes' => 'Requisicao integrada',
                'items' => [[
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'supplier_id' => $supplier->id,
                    'quantity' => 4,
                    'need_by_date' => now()->addDays(7)->toDateString(),
                    'order_date' => now()->toDateString(),
                ]],
            ])
            ->assertRedirect(route('purchasing.requisitions.index'));

        $requisition = PurchaseRequisition::query()->where('company_id', $company->id)->latest('id')->firstOrFail();

        $this->actingAs($user, 'web')
            ->post(route('purchasing.orders.store'), [
                'supplier_id' => $supplier->id,
                'purchase_requisition_id' => $requisition->id,
                'department_id' => $departmentId,
                'cost_center_id' => $costCenterId,
                'status' => 'DRAFT',
                'order_date' => now()->toDateString(),
                'expected_delivery_date' => now()->addDays(10)->toDateString(),
                'notes' => 'Pedido integrado',
                'items' => [[
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => 4,
                    'unit_price' => '10,00',
                    'need_by_date' => now()->addDays(7)->toDateString(),
                    'promised_date' => now()->addDays(10)->toDateString(),
                ]],
            ])
            ->assertRedirect(route('purchasing.orders.index'));

        $order = PurchaseOrder::query()->where('company_id', $company->id)->latest('id')->firstOrFail();

        $this->actingAs($user, 'web')
            ->post(route('sales.store'), [
                'customer_id' => $customer->id,
                'department_id' => $departmentId,
                'cost_center_id' => $costCenterId,
                'sale_date' => now()->toDateString(),
                'status' => 'DRAFT',
                'discount_amount' => '0,00',
                'tax_amount' => '0,00',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => '15,00',
                ]],
            ])
            ->assertRedirect(route('sales.index'));

        $sale = Sale::query()->where('company_id', $company->id)->latest('id')->firstOrFail();

        $this->assertDatabaseHas('purchase_requisitions', [
            'id' => $requisition->id,
            'department_id' => $departmentId,
            'cost_center_id' => $costCenterId,
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $order->id,
            'department_id' => $departmentId,
            'cost_center_id' => $costCenterId,
        ]);

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'department_id' => $departmentId,
            'cost_center_id' => $costCenterId,
        ]);
    }

    public function test_user_without_permissions_cannot_access_admin_data_crud(): void
    {
        ['user' => $user] = $this->contextWithRole('viewer');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->actingAs($user, 'web')
            ->get(route('admin-data.index', ['domain' => 'departments']));
    }

    /**
     * @return array{company: Company, user: User}
     */
    private function contextWithRole(string $roleSlug): array
    {
        $company = Company::query()->create([
            'name' => 'Atlas Components',
            'code' => 'ATL',
            'is_active' => true,
        ]);

        $user = User::query()->create([
            'name' => 'Master User',
            'email' => $roleSlug.'@atlas.test',
            'password' => 'Strong!Pass123',
            'current_company_id' => $company->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($company->id, ['is_default' => true]);

        $role = Role::query()->withoutGlobalScope('tenant')->create([
            'company_id' => $company->id,
            'name' => ucfirst($roleSlug),
            'slug' => $roleSlug,
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

        return compact('company', 'user');
    }

    private function createMasterData(User $user, string $domain, string $code, string $name, ?float $taxRate = null): int
    {
        $payload = [
            'code' => $code,
            'name' => $name,
            'description' => 'Registro '.$domain,
            'is_active' => '1',
        ];

        if ($taxRate !== null) {
            $payload['tax_rate'] = $taxRate;
        }

        $this->actingAs($user, 'web')
            ->post(route('admin-data.store', ['domain' => $domain]), $payload)
            ->assertRedirect(route('admin-data.index', ['domain' => $domain]));

        return (int) MasterDataRecord::query()
            ->where('company_id', (int) $user->current_company_id)
            ->where('domain', $domain)
            ->where('code', $code)
            ->value('id');
    }
}
