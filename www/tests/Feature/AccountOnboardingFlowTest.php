<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\AccountInvitationMail;
use App\Mail\TrialVerificationMail;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class AccountOnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_SECRET = 'Strong!Pass123';

    private const ONBOARDING_PATH = '/onboarding';

    private const FIRST_MEMBER_EMAIL = 'carla@prime.com';

    private const SECOND_MEMBER_EMAIL = 'diego@nova.com';

    public function test_it_creates_the_account_and_sends_invites(): void
    {
        Mail::fake();

        $this->post('/start-trial', [
            'name' => 'Marina Silva',
            'email' => 'marina@alpha.com',
            'password' => self::TEST_SECRET,
            'password_confirmation' => self::TEST_SECRET,
            'terms' => '1',
        ])->assertRedirect(route('onboarding.wizard'));

        Mail::assertQueued(TrialVerificationMail::class);

        $user = User::query()->where('email', 'marina@alpha.com')->firstOrFail();

        $this->actingAs($user)->post(self::ONBOARDING_PATH, [
            'company_name' => 'Alpha Industria',
            'company_domain' => 'alpha.com',
            'segment' => 'Industrial',
            'operation_size' => 'mid',
            'timezone' => 'America/Sao_Paulo',
        ])->assertRedirect(route('onboarding.wizard'));

        $user = $user->fresh();

        $this->actingAs($user)->post(self::ONBOARDING_PATH, [
            'plan_code' => 'starter',
        ])->assertRedirect(route('onboarding.wizard'));

        $this->actingAs($user)->post(self::ONBOARDING_PATH, [
            'emails' => "ana@alpha.com\nbruno@alpha.com",
        ])->assertRedirect(route('dashboard.industrial'));

        Mail::assertQueued(AccountInvitationMail::class);
        $this->assertDatabaseCount('account_invitations', 2);
    }

    public function test_invited_existing_user_keeps_access_to_multiple_accounts(): void
    {
        Mail::fake();

        $firstUser = $this->completeWizard(self::FIRST_MEMBER_EMAIL, 'Prime Admin', 'Prime One');
        $secondUser = $this->completeWizard(self::SECOND_MEMBER_EMAIL, 'Nova Admin', 'Nova Two');

        $firstCompanyId = (int) $firstUser->current_company_id;
        $secondCompanyId = (int) $secondUser->current_company_id;

        $this->actingAs($secondUser)->post(self::ONBOARDING_PATH, [
            'emails' => self::FIRST_MEMBER_EMAIL,
        ])->assertRedirect(route('dashboard.industrial'));

        $inviteUrl = null;

        Mail::assertQueued(AccountInvitationMail::class, function (AccountInvitationMail $mail) use (&$inviteUrl): bool {
            $inviteUrl = $mail->inviteUrl;

            return str_contains($mail->invitation->email, self::FIRST_MEMBER_EMAIL);
        });

        $path = (string) parse_url((string) $inviteUrl, PHP_URL_PATH);
        $token = basename($path);

        $this->actingAs($firstUser)->post(route('account-invitations.accept', ['token' => $token]))
            ->assertRedirect(route('dashboard.industrial'));

        $firstUser->refresh();

        $this->assertSame($firstCompanyId, (int) $firstUser->current_company_id);
        $this->assertDatabaseHas('company_user', [
            'company_id' => $secondCompanyId,
            'user_id' => $firstUser->id,
        ]);
    }

    public function test_login_routes_users_without_a_company_to_onboarding(): void
    {
        $user = User::query()->create([
            'name' => 'Login Test',
            'email' => 'login-test@example.com',
            'password' => bcrypt(self::TEST_SECRET),
            'current_company_id' => null,
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => self::TEST_SECRET,
        ])->assertRedirect(route('onboarding.wizard'));
    }

    private function completeWizard(string $email, string $name, string $company): User
    {
        $this->post('/logout');

        $this->post('/start-trial', [
            'name' => $name,
            'email' => $email,
            'password' => self::TEST_SECRET,
            'password_confirmation' => self::TEST_SECRET,
            'terms' => '1',
        ])->assertRedirect(route('onboarding.wizard'));

        $user = User::query()->where('email', $email)->firstOrFail();

        $this->actingAs($user)->post(self::ONBOARDING_PATH, [
            'company_name' => $company,
            'company_domain' => strtolower(str_replace(' ', '', $company)).'.com',
            'segment' => 'Industrial',
            'operation_size' => 'mid',
            'timezone' => 'America/Sao_Paulo',
        ])->assertRedirect(route('onboarding.wizard'));

        $user = $user->fresh();

        $this->actingAs($user)->post(self::ONBOARDING_PATH, [
            'plan_code' => 'starter',
        ])->assertRedirect(route('onboarding.wizard'));

        return $user->fresh();
    }
}
