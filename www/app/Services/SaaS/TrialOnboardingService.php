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

    private const MASTER_ROLE_SLUG = 'master';

    private const LEGACY_ADMIN_ROLE_SLUG = 'admin';

    private const LEGACY_ACCOUNT_MASTER_ROLE_SLUG = 'account-master';

    private const LEGACY_MASTER_ROLE_SLUG = 'organization-admin';

    private const MEMBER_ROLE_SLUG = 'organization-member';

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

            $adminRole = $this->ensureMasterRole($company->id);

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

    public function isMasterUser(User $user): bool
    {
        $companyId = (int) ($user->current_company_id ?? 0);

        if ($companyId <= 0) {
            return false;
        }

        return $user->roles()
            ->wherePivot('company_id', $companyId)
            ->whereIn('slug', [self::MASTER_ROLE_SLUG, self::LEGACY_ADMIN_ROLE_SLUG, self::LEGACY_ACCOUNT_MASTER_ROLE_SLUG, self::LEGACY_MASTER_ROLE_SLUG])
            ->exists();
    }

    public function registerTeamMember(User $actor, array $data, Request $request): User
    {
        $companyId = (int) ($actor->current_company_id ?? 0);

        if ($companyId <= 0) {
            throw ValidationException::withMessages([
                'company' => __('messages.user_without_active_company_for_member_registration'),
            ]);
        }

        if (! $this->isMasterUser($actor)) {
            throw ValidationException::withMessages([
                'authorization' => __('messages.only_master_user_can_register_members'),
            ]);
        }

        $email = mb_strtolower(trim((string) $data['email']));

        return DB::transaction(function () use ($actor, $companyId, $data, $email, $request): User {
            $company = Company::query()->findOrFail($companyId);
            $role = ($data['role'] ?? 'member') === 'master'
                ? $this->ensureMasterRole($companyId)
                : $this->ensureMemberRole($companyId);

            $user = User::query()->where('email', $email)->first();

            if ($user !== null) {
                $alreadyBelongsToCompany = $user->companies()
                    ->where('companies.id', $companyId)
                    ->exists();

                if ($alreadyBelongsToCompany) {
                    throw ValidationException::withMessages([
                        'email' => __('messages.user_already_belongs_to_account'),
                    ]);
                }

                if ((bool) ($data['activate'] ?? true) && ! $user->is_active) {
                    $user->forceFill(['is_active' => true])->save();
                }

                if ($user->current_company_id === null) {
                    $user->forceFill(['current_company_id' => $companyId])->save();
                }
            } else {
                $user = User::query()->create([
                    'name' => $data['name'],
                    'email' => $email,
                    'password' => Hash::make((string) $data['password']),
                    'current_company_id' => $companyId,
                    'is_active' => (bool) ($data['activate'] ?? true),
                ]);
            }

            $company->users()->syncWithoutDetaching([
                $user->id => ['is_default' => false],
            ]);

            $user->roles()->syncWithoutDetaching([
                $role->id => ['company_id' => $companyId],
            ]);

            $organization = Organization::query()->where('company_id', $companyId)->first();

            if ($organization !== null) {
                OnboardingProfile::query()->firstOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'progress' => 0,
                        'timezone' => $organization->timezone,
                    ]
                );
            }

            $this->audit->record(
                event: 'tenant.user.created',
                context: [
                    'created_user_id' => $user->id,
                    'role' => $role->slug,
                    'created_by' => $actor->id,
                ],
                userId: $actor->id,
                organizationId: $organization?->id,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return $user->fresh();
        });
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

    private function ensureMasterRole(int $companyId): Role
    {
        return Role::query()->withoutGlobalScope('tenant')->firstOrCreate(
            [
                'company_id' => $companyId,
                'slug' => self::MASTER_ROLE_SLUG,
            ],
            [
                'name' => 'Master',
            ]
        );
    }

    private function ensureMemberRole(int $companyId): Role
    {
        return Role::query()->withoutGlobalScope('tenant')->firstOrCreate(
            [
                'company_id' => $companyId,
                'slug' => self::MEMBER_ROLE_SLUG,
            ],
            [
                'name' => 'Organization Member',
            ]
        );
    }
}
