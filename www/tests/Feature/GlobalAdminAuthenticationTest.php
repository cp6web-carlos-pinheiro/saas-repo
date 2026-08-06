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
