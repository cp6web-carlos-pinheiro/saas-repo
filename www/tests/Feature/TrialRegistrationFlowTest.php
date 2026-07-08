<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\TrialVerificationMail;
use App\Models\SaaS\Organization;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class TrialRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_registers_trial_and_redirects_to_onboarding(): void
    {
        Mail::fake();

        $response = $this->post('/start-trial', [
            'name' => 'Ana Souza',
            'company' => 'Acme Industria',
            'email' => 'ana@acme.com',
            'password' => 'Strong!Pass123',
            'password_confirmation' => 'Strong!Pass123',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('onboarding.wizard'));

        $user = User::query()->where('email', 'ana@acme.com')->first();
        $this->assertNotNull($user);

        $organization = Organization::query()->where('company_id', $user->current_company_id)->first();
        $this->assertNotNull($organization);

        $trial = Trial::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($trial);
        $this->assertSame('active', $trial->status);

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
            'terms' => '1',
        ];

        $this->post('/start-trial', $payload)->assertRedirect(route('onboarding.wizard'));

        $this->post('/start-trial', $payload)
            ->assertSessionHasErrors('email');
    }
}
