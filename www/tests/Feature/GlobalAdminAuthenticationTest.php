<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GlobalAdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_admin_login_renders_a_single_form(): void
    {
        $this->withoutVite();

        $response = $this->get(route('global-admin.login'))
            ->assertOk()
            ->assertViewIs('admin.login');

        self::assertSame(1, substr_count($response->getContent(), '<form'));
    }

    public function test_guest_is_redirected_to_global_admin_login_from_protected_area(): void
    {
        $this->get(route('global-admin.home'))
            ->assertRedirect(route('global-admin.login'));
    }

    public function test_authenticated_global_admin_is_redirected_from_login_to_global_admin_home(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Global Admin',
            'email' => 'global-admin@test.dev',
            'password' => 'Strong!Pass123',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('global-admin.login'))
            ->assertRedirect(route('global-admin.home'));

        $sortableLists = [
            [route('global-admin.administrators.index'), 'email'],
            [route('global-admin.companies.index'), 'active_plan'],
            [route('global-admin.customers.index'), 'company'],
            [route('global-admin.plans.index'), 'duration'],
            [route('global-admin.tutorials.index'), 'id'],
        ];

        foreach ($sortableLists as [$url, $sort]) {
            $this->actingAs($admin, 'admin')
                ->get($url.'?'.http_build_query(['sort' => $sort, 'direction' => 'desc']))
                ->assertOk();
        }

        $this->actingAs($admin, 'admin')
            ->get(route('global-admin.administrators.index'))
            ->assertOk()
            ->assertSee('data-ui-modal-open="globalAdminHelpPanel"', false)
            ->assertSee('data-ui-modal-open="globalAdminSettingsPanel"', false)
            ->assertSee('data-theme-option="light"', false)
            ->assertSee('data-theme-option="system"', false)
            ->assertSee('data-theme-option="dark"', false);
    }

    public function test_authenticated_web_user_can_still_view_global_admin_login(): void
    {
        $user = User::query()->create([
            'name' => 'Company User',
            'email' => 'company-user@test.dev',
            'password' => 'Strong!Pass123',
            'is_active' => true,
        ]);

        $this->actingAs($user, 'web')
            ->get(route('global-admin.login'))
            ->assertOk()
            ->assertViewIs('admin.login');
    }
}
