<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TrialApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_registers_and_logs_in_via_api(): void
    {
        $registerResponse = $this->postJson('/api/v1/register', [
            'name' => 'Carlos Lima',
            'company' => 'Forge Labs',
            'email' => 'carlos@forgelabs.com',
            'password' => 'Strong!Pass123',
            'password_confirmation' => 'Strong!Pass123',
            'terms' => true,
        ]);

        $registerResponse->assertCreated();
        $registerResponse->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'email' => 'carlos@forgelabs.com',
        ]);

        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => 'carlos@forgelabs.com',
            'password' => 'Strong!Pass123',
        ]);

        $loginResponse->assertOk();
        $loginResponse->assertJsonPath('success', true);
        $this->assertNotEmpty($loginResponse->json('data.token'));

        $user = User::query()->where('email', 'carlos@forgelabs.com')->first();
        $this->assertNotNull($user);
    }
}
