<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\TrialVerificationMail;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class TrialRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_validation_errors_for_an_incomplete_company_signup(): void
    {
        $this->post('/cadastro-empresa', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors([
            'name',
            'email',
            'password',
            'preferred_locale',
            'terms',
        ]);
    }

    public function test_it_registers_user_and_redirects_to_onboarding(): void
    {
        Mail::fake();

        $response = $this->post('/start-trial', [
            'name' => 'Ana Souza',
            'company' => 'Acme Industria',
            'email' => 'ana@acme.com',
            'password' => 'Strong!Pass123',
            'password_confirmation' => 'Strong!Pass123',
            'preferred_locale' => 'pt_BR',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('onboarding.wizard'));

        $user = User::query()->where('email', 'ana@acme.com')->first();
        $this->assertNotNull($user);

        Mail::assertQueued(TrialVerificationMail::class);
    }

    public function test_it_blocks_duplicate_trial_by_email(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Ana Souza',
            'company' => 'Acme Industria',
            'email' => 'ana@acme.com',
            'password' => 'Strong!Pass123',
            'password_confirmation' => 'Strong!Pass123',
            'preferred_locale' => 'pt_BR',
            'terms' => '1',
        ];

        $this->post('/start-trial', $payload)->assertRedirect(route('onboarding.wizard'));

        $this->post('/logout')->assertRedirect(route('login'));

        $this->post('/start-trial', $payload)
            ->assertSessionHasErrors('email');
    }
}
