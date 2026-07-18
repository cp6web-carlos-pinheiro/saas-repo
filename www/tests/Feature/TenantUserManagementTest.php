<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_user_can_create_user_in_same_account(): void
    {
        $registerResponse = $this->postJson('/api/v1/register', [
            'name' => 'Master User',
            'company' => 'Nimbus Manufacturing',
            'email' => 'master@nimbus.com',
            'password' => 'Strong!Pass123',
            'password_confirmation' => 'Strong!Pass123',
            'terms' => true,
        ]);

        $registerResponse->assertCreated();

        $masterToken = (string) $registerResponse->json('data.token');
        $master = User::query()->where('email', 'master@nimbus.com')->firstOrFail();

        $createUserResponse = $this->withHeader('Authorization', 'Bearer '.$masterToken)
            ->postJson('/api/v1/tenant/users', [
                'name' => 'Ana Operadora',
                'email' => 'ana.operadora@nimbus.com',
                'password' => 'Strong!Pass123',
                'password_confirmation' => 'Strong!Pass123',
                'role' => 'member',
                'activate' => true,
            ]);

        $createUserResponse->assertCreated();
        $createUserResponse->assertJsonPath('success', true);

        $newUser = User::query()->where('email', 'ana.operadora@nimbus.com')->first();

        $this->assertNotNull($newUser);
        $this->assertDatabaseHas('company_user', [
            'company_id' => $master->current_company_id,
            'user_id' => $newUser->id,
        ]);

        $this->assertTrue(
            $newUser->roles()
                ->wherePivot('company_id', $master->current_company_id)
                ->where('slug', 'organization-member')
                ->exists()
        );
    }

    public function test_non_master_user_cannot_create_users_in_same_account(): void
    {
        $registerResponse = $this->postJson('/api/v1/register', [
            'name' => 'Master User',
            'company' => 'Aurora Parts',
            'email' => 'master@aurora.com',
            'password' => 'Strong!Pass123',
            'password_confirmation' => 'Strong!Pass123',
            'terms' => true,
        ]);

        $registerResponse->assertCreated();
        $masterToken = (string) $registerResponse->json('data.token');

        $this->withHeader('Authorization', 'Bearer '.$masterToken)
            ->postJson('/api/v1/tenant/users', [
                'name' => 'Member User',
                'email' => 'member@aurora.com',
                'password' => 'Strong!Pass123',
                'password_confirmation' => 'Strong!Pass123',
                'role' => 'member',
                'activate' => true,
            ])->assertCreated();

        $memberLogin = $this->postJson('/api/v1/login', [
            'email' => 'member@aurora.com',
            'password' => 'Strong!Pass123',
        ]);

        $memberLogin->assertOk();
        $memberToken = (string) $memberLogin->json('data.token');

        $forbiddenResponse = $this->withHeader('Authorization', 'Bearer '.$memberToken)
            ->postJson('/api/v1/tenant/users', [
                'name' => 'Outra Pessoa',
                'email' => 'outra.pessoa@aurora.com',
                'password' => 'Strong!Pass123',
                'password_confirmation' => 'Strong!Pass123',
                'role' => 'member',
                'activate' => true,
            ]);

        $forbiddenResponse->assertForbidden();
        $this->assertDatabaseMissing('users', [
            'email' => 'outra.pessoa@aurora.com',
        ]);
    }
}
