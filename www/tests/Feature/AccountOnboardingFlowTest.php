<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\AccountInvitationMail;
use App\Mail\TrialVerificationMail;
use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\CompanyUserAccessService;
use Database\Seeders\PlanSeeder;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_it_creates_the_account_and_sends_invites(): void
    {
        Mail::fake();

        $this->post('/start-trial', [
            'name' => 'Marina Silva',
            'email' => 'marina@alpha.com',
            'password' => self::TEST_SECRET,
            'password_confirmation' => self::TEST_SECRET,
            'preferred_locale' => 'pt_BR',
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
            'plan_code' => 'free_trial',
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

    public function test_it_can_cancel_onboarding_and_return_to_the_public_home(): void
    {
        config()->set('security.mfa.enabled', false);

        $user = User::query()->create([
            'name' => 'Cancelled Onboarding',
            'email' => 'cancelled-onboarding@example.com',
            'password' => bcrypt(self::TEST_SECRET),
            'current_company_id' => null,
            'is_active' => true,
        ]);

        $this->actingAs($user, 'web')
            ->get(self::ONBOARDING_PATH)
            ->assertOk()
            ->assertSee(__('onboarding.cancel_go_home'))
            ->assertSee(__('onboarding.exit_go_login'));

        $this->post('/logout', ['redirect_to' => 'home'])
            ->assertRedirect(url('/'));

        $this->assertGuest('web');

        $this->actingAs($user, 'web')
            ->post('/logout', ['redirect_to' => 'login'])
            ->assertRedirect(route('login'));

        $this->assertGuest('web');
    }

    public function test_dashboard_modules_only_include_the_user_accessible_modules(): void
    {
        $user = User::query()->create([
            'name' => 'Module Access User',
            'email' => 'modules@example.com',
            'password' => bcrypt(self::TEST_SECRET),
            'current_company_id' => null,
            'is_active' => true,
        ]);
        $company = Company::query()->create([
            'name' => 'Module Company',
            'code' => 'module-company',
            'is_active' => true,
        ]);

        $user->companies()->syncWithoutDetaching([
            $company->id,
        ]);
        $user->forceFill(['current_company_id' => $company->id])->save();

        $bom = Permission::query()->create(['name' => 'BOM', 'slug' => 'bom.read', 'module' => 'bom']);
        $inventory = Permission::query()->create(['name' => 'Inventory', 'slug' => 'inventory.read', 'module' => 'inventory']);
        $identity = Permission::query()->create(['name' => 'Identity', 'slug' => 'identity.read', 'module' => 'identity']);

        $role = Role::query()->create([
            'company_id' => $company->id,
            'name' => 'Custom access',
            'slug' => 'user-access-'.$user->id.'-custom',
        ]);
        $role->permissions()->sync([$bom->id, $inventory->id]);
        $user->roles()->attach($role->id, ['company_id' => $company->id]);

        $service = app(CompanyUserAccessService::class);

        $this->assertSame(['bom', 'inventory'], $service->accessibleModules($user, $company));
        $this->assertTrue($service->hasModuleAccess($user, $company, 'inventory'));
        $this->assertFalse($service->hasModuleAccess($user, $company, 'identity'));
    }

    private function completeWizard(string $email, string $name, string $company): User
    {
        $this->post('/logout');

        $this->post('/start-trial', [
            'name' => $name,
            'email' => $email,
            'password' => self::TEST_SECRET,
            'password_confirmation' => self::TEST_SECRET,
            'preferred_locale' => 'pt_BR',
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
            'plan_code' => 'free_trial',
        ])->assertRedirect(route('onboarding.wizard'));

        return $user->fresh();
    }

    public function test_it_redirects_to_payment_when_switching_to_a_paid_plan_after_onboarding(): void
    {
        $user = $this->completeWizard('renova@alpha.com', 'Renova Admin', 'Renova Industria');

        $this->actingAs($user)->post(route('billing.subscription.update'), [
            'plan_code' => 'monthly',
        ])->assertRedirect(route('onboarding.payment.create', ['planCode' => 'monthly']));

        $this->assertSame('monthly', session('onboarding.payment_plan'));
        $this->assertSame('billing', session('payment.context'));
        $this->assertDatabaseMissing('subscriptions', ['plan_code' => 'monthly', 'status' => 'active']);
    }

    public function test_it_does_not_allow_reusing_the_free_trial_plan(): void
    {
        $user = $this->completeWizard('trial-unico@alpha.com', 'Trial Unico', 'Trial Industria');

        $response = $this->actingAs($user)
            ->from(route('billing.subscription.show'))
            ->post(route('billing.subscription.update'), [
                'plan_code' => 'free_trial',
            ]);

        $response->assertRedirect(route('billing.subscription.show'));
        $response->assertSessionHasErrors('plan_code');
    }
}
