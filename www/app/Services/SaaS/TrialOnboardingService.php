<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Models\SaaS\EmailVerification;
use App\Models\SaaS\OnboardingProfile;
use App\Models\SaaS\Organization;
use App\Models\SaaS\Subscription;
use App\Models\SaaS\Tenant;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class TrialOnboardingService
{
    public function __construct(private readonly AuditLogService $audit) {}

    /**
     * @return array{user: User, organization: Organization, trial: Trial, emailVerificationToken: string}
     */
    public function register(array $data, Request $request): array
    {
        $email = mb_strtolower(trim((string) $data['email']));
        $domain = Str::after($email, '@');

        $this->guardAgainstTrialAbuse($email, $domain);

        return DB::transaction(function () use ($data, $request, $email, $domain): array {
            $company = Company::query()->create([
                'name' => $data['company'],
                'code' => $this->uniqueCode($data['company']),
                'is_active' => true,
            ]);

            $organization = Organization::query()->create([
                'company_id' => $company->id,
                'name' => $data['company'],
                'slug' => $this->uniqueSlug($data['company']),
                'domain' => $domain,
                'timezone' => 'UTC',
                'preferences' => [
                    'source' => 'trial-signup',
                ],
            ]);

            Tenant::query()->create([
                'organization_id' => $organization->id,
                'name' => $data['company'],
                'slug' => $organization->slug,
                'is_active' => true,
            ]);

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make((string) $data['password']),
                'current_company_id' => $company->id,
                'is_active' => true,
            ]);

            $company->users()->attach($user->id, ['is_default' => true]);

            $adminRole = Role::query()->withoutGlobalScope('tenant')->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'slug' => 'organization-admin',
                ],
                [
                    'name' => 'Organization Admin',
                    'description' => 'Initial tenant administrator',
                ]
            );

            $user->roles()->attach($adminRole->id, ['company_id' => $company->id]);

            $trial = Trial::query()->create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'trial_start_date' => now(),
                'trial_end_date' => now()->addDays(14),
                'grace_ends_at' => now()->addDays(17),
                'status' => 'active',
                'is_expired' => false,
                'email_domain' => $domain,
                'registration_ip' => $request->ip(),
            ]);

            Subscription::query()->create([
                'organization_id' => $organization->id,
                'trial_id' => $trial->id,
                'provider' => 'stripe',
                'plan_code' => 'trial',
                'status' => 'trialing',
                'starts_at' => now(),
                'ends_at' => $trial->trial_end_date,
            ]);

            OnboardingProfile::query()->create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'progress' => 10,
                'timezone' => 'UTC',
            ]);

            $token = Str::random(64);

            EmailVerification::query()->create([
                'user_id' => $user->id,
                'token' => hash('sha256', $token),
                'expires_at' => now()->addDay(),
                'requested_ip' => $request->ip(),
            ]);

            $this->audit->record(
                event: 'trial.started',
                context: [
                    'trial_id' => $trial->id,
                    'organization_id' => $organization->id,
                ],
                userId: $user->id,
                organizationId: $organization->id,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );

            return [
                'user' => $user,
                'organization' => $organization,
                'trial' => $trial,
                'emailVerificationToken' => $token,
            ];
        });
    }

    /**
     * @return array{user: User, organization: Organization, trial: Trial, emailVerificationToken: string}
     */
    public function registerFromSocial(string $name, string $company, string $email, Request $request): array
    {
        $password = Str::random(32);

        return $this->register([
            'name' => $name,
            'company' => $company,
            'email' => $email,
            'password' => $password,
        ], $request);
    }

    public function completeOnboarding(User $user, array $data): OnboardingProfile
    {
        $organization = Organization::query()
            ->where('company_id', $user->current_company_id)
            ->firstOrFail();

        $profile = OnboardingProfile::query()->firstOrNew([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
        ]);

        $profile->fill([
            'segment' => $data['segment'] ?? null,
            'operation_size' => $data['operation_size'] ?? null,
            'timezone' => $data['timezone'] ?? 'UTC',
            'import_data' => (bool) ($data['import_data'] ?? false),
            'connect_integrations' => (bool) ($data['connect_integrations'] ?? false),
            'invite_team' => (bool) ($data['invite_team'] ?? false),
            'progress' => 100,
            'completed_at' => now(),
        ]);

        $profile->save();

        $organization->update([
            'segment' => $profile->segment,
            'operation_size' => $profile->operation_size,
            'timezone' => $profile->timezone,
            'preferences' => [
                'import_data' => $profile->import_data,
                'connect_integrations' => $profile->connect_integrations,
                'invite_team' => $profile->invite_team,
            ],
        ]);

        return $profile;
    }

    private function guardAgainstTrialAbuse(string $email, string $domain): void
    {
        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Ja existe um cadastro com este e-mail.',
            ]);
        }

        $domainTrials = Trial::query()
            ->where('email_domain', $domain)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        if ($domainTrials >= 5) {
            throw ValidationException::withMessages([
                'email' => 'Limite de trial por dominio excedido. Fale com o comercial.',
            ]);
        }
    }

    private function uniqueSlug(string $companyName): string
    {
        $base = Str::slug($companyName);
        $slug = $base !== '' ? $base : 'organization';
        $count = 1;

        while (Organization::query()->where('slug', $slug)->exists()) {
            $count++;
            $slug = $base.'-'.$count;
        }

        return $slug;
    }

    private function uniqueCode(string $companyName): string
    {
        $base = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $companyName) ?? 'ORG', 0, 8));
        $code = $base !== '' ? $base : 'ORG';

        while (Company::query()->where('code', $code)->exists()) {
            $code = $base.'-'.Str::upper(Str::random(4));
        }

        return $code;
    }
}
