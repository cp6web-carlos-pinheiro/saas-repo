<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantInventoryPlantCrudManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_administrator_can_manage_inventory_plants(): void
    {
        ['company' => $company, 'user' => $user] = $this->context();

        $this->actingAs($user, 'web')
            ->post(route('inventory.plants.store'), [
                'name' => 'Planta Principal',
                'timezone' => 'America/Sao_Paulo',
                'is_active' => '1',
            ])
            ->assertRedirect(route('inventory.plants.index'));

        $plant = Plant::query()->where('company_id', $company->id)->firstOrFail();

        $this->assertDatabaseHas('plants', [
            'id' => $plant->id,
            'company_id' => $company->id,
            'name' => 'Planta Principal',
            'code' => 'PLANTA-PRINCIPAL',
            'timezone' => 'America/Sao_Paulo',
            'is_active' => 1,
        ]);

        $this->actingAs($user, 'web')
            ->get(route('inventory.plants.index'))
            ->assertOk()
            ->assertSee('Planta Principal');

        $this->actingAs($user, 'web')
            ->put(route('inventory.plants.update', $plant), [
                'name' => 'Planta Secundaria',
                'timezone' => 'UTC',
                'is_active' => '0',
            ])
            ->assertRedirect(route('inventory.plants.index'));

        $this->assertDatabaseHas('plants', [
            'id' => $plant->id,
            'name' => 'Planta Secundaria',
            'code' => 'PLANTA-PRINCIPAL',
            'timezone' => 'UTC',
            'is_active' => 0,
        ]);

        $this->actingAs($user, 'web')
            ->get(route('inventory.plants.show', $plant))
            ->assertOk()
            ->assertSee('Planta Secundaria');

        $this->actingAs($user, 'web')
            ->delete(route('inventory.plants.destroy', $plant))
            ->assertRedirect(route('inventory.plants.index'));

        $this->assertDatabaseMissing('plants', [
            'id' => $plant->id,
        ]);
    }

    /**
     * @return array{company: Company, user: User}
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

        return compact('company', 'user');
    }
}
