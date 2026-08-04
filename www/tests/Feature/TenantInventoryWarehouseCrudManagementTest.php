<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SaaS\Organization;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantInventoryWarehouseCrudManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_administrator_can_manage_inventory_warehouses(): void
    {
        ['company' => $company, 'user' => $user, 'plant' => $plant] = $this->context();

        $this->actingAs($user, 'web')
            ->post(route('inventory.warehouses.store'), [
                'name' => 'Almoxarifado Principal',
                'code' => 'WH-ALPHA',
                'plant_id' => $plant->id,
                'is_active' => '1',
            ])
            ->assertRedirect(route('inventory.warehouses.index'));

        $warehouse = Warehouse::query()->where('company_id', $company->id)->firstOrFail();

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'company_id' => $company->id,
            'plant_id' => $plant->id,
            'name' => 'Almoxarifado Principal',
            'code' => 'WH-ALPHA',
            'is_active' => 1,
        ]);

        $this->actingAs($user, 'web')
            ->get(route('inventory.warehouses.index'))
            ->assertOk()
            ->assertSee('Almoxarifado Principal');

        $this->actingAs($user, 'web')
            ->put(route('inventory.warehouses.update', $warehouse), [
                'name' => 'Almoxarifado Secundario',
                'code' => 'WH-BETA',
                'plant_id' => $plant->id,
                'is_active' => '0',
            ])
            ->assertRedirect(route('inventory.warehouses.index'));

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'name' => 'Almoxarifado Secundario',
            'code' => 'WH-BETA',
            'is_active' => 0,
        ]);

        $this->actingAs($user, 'web')
            ->get(route('inventory.warehouses.show', $warehouse))
            ->assertOk()
            ->assertSee('Almoxarifado Secundario');

        $this->actingAs($user, 'web')
            ->delete(route('inventory.warehouses.destroy', $warehouse))
            ->assertRedirect(route('inventory.warehouses.index'));

        $this->assertDatabaseMissing('warehouses', [
            'id' => $warehouse->id,
        ]);
    }

    /**
     * @return array{company: Company, user: User, plant: Plant}
     */
    private function context(): array
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

        $plant = Plant::query()->create([
            'company_id' => $company->id,
            'name' => 'Planta Atlas',
            'code' => 'PLT-001',
            'timezone' => 'UTC',
            'is_active' => true,
        ]);

        return compact('company', 'user', 'plant');
    }
}
