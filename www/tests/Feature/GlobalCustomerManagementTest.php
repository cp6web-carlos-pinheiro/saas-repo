<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\CompanyUserAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GlobalCustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_company_user_is_always_created_as_administrator(): void
    {
        $this->actingAsGlobalAdmin();
        $this->seedPermissionModules();

        $company = Company::query()->create([
            'name' => 'Alpha Industry',
            'code' => 'ALPHA',
            'is_active' => true,
        ]);

        $response = $this->post(route('global-admin.customers.store'), [
            'name' => 'Primeiro Usuario',
            'email' => 'primeiro@alpha.test',
            'password' => 'Strong!Pass123',
            'password_confirmation' => 'Strong!Pass123',
            'company_id' => $company->id,
            'access_profile' => CompanyUserAccessService::CUSTOM_PROFILE,
            'modules' => ['inventory'],
        ]);

        $response->assertRedirect(route('global-admin.customers.index'));

        $customer = User::query()->where('email', 'primeiro@alpha.test')->firstOrFail();
        $access = app(CompanyUserAccessService::class)->accessFor($customer, $company);

        $this->assertSame(CompanyUserAccessService::ADMINISTRATOR_PROFILE, $access['profile']);
        $this->assertEqualsCanonicalizing(['inventory', 'purchasing'], $access['modules']);
    }

    public function test_company_context_edit_updates_only_target_company_access(): void
    {
        $this->actingAsGlobalAdmin();
        $this->seedPermissionModules();

        $service = app(CompanyUserAccessService::class);

        $companyA = Company::query()->create([
            'name' => 'Company A',
            'code' => 'COMPA',
            'is_active' => true,
        ]);

        $companyB = Company::query()->create([
            'name' => 'Company B',
            'code' => 'COMPB',
            'is_active' => true,
        ]);

        $firstCompanyUser = User::query()->create([
            'name' => 'First User',
            'email' => 'first@compa.test',
            'password' => 'Strong!Pass123',
            'is_active' => true,
            'current_company_id' => $companyA->id,
        ]);

        $service->sync(
            $firstCompanyUser,
            $companyA,
            CompanyUserAccessService::ADMINISTRATOR_PROFILE,
            [],
            true,
        );

        $customer = User::query()->create([
            'name' => 'Cross Account User',
            'email' => 'cross@test.dev',
            'password' => 'Strong!Pass123',
            'is_active' => true,
            'current_company_id' => $companyB->id,
        ]);

        $service->sync(
            $customer,
            $companyA,
            CompanyUserAccessService::CUSTOM_PROFILE,
            ['inventory'],
            false,
        );

        $service->sync(
            $customer,
            $companyB,
            CompanyUserAccessService::CUSTOM_PROFILE,
            ['inventory'],
            false,
        );

        $response = $this->put(route('global-admin.customers.update', [
            'customer' => $customer->id,
            'company_id' => $companyA->id,
        ]), [
            'name' => $customer->name,
            'email' => $customer->email,
            'company_id' => $companyA->id,
            'access_profile' => CompanyUserAccessService::CUSTOM_PROFILE,
            'modules' => ['purchasing'],
            'is_active' => '1',
            'return_to_company_id' => $companyA->id,
        ]);

        $response->assertRedirect(route('global-admin.companies.show', ['company' => $companyA->id]));

        $customer->refresh();

        $companyAAccess = $service->accessFor($customer, $companyA);
        $companyBAccess = $service->accessFor($customer, $companyB);

        $this->assertSame(CompanyUserAccessService::CUSTOM_PROFILE, $companyAAccess['profile']);
        $this->assertEqualsCanonicalizing(['purchasing'], $companyAAccess['modules']);

        $this->assertSame(CompanyUserAccessService::CUSTOM_PROFILE, $companyBAccess['profile']);
        $this->assertEqualsCanonicalizing(['inventory'], $companyBAccess['modules']);
    }

    private function actingAsGlobalAdmin(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Global Admin',
            'email' => 'global-admin@test.dev',
            'password' => 'Strong!Pass123',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin');
    }

    private function seedPermissionModules(): void
    {
        Permission::query()->create([
            'name' => 'Inventory Read',
            'slug' => 'inventory.read',
            'module' => 'inventory',
        ]);

        Permission::query()->create([
            'name' => 'Inventory Update',
            'slug' => 'inventory.update',
            'module' => 'inventory',
        ]);

        Permission::query()->create([
            'name' => 'Purchasing Read',
            'slug' => 'purchasing.read',
            'module' => 'purchasing',
        ]);
    }
}
