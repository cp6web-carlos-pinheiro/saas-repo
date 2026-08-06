<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Mail\AccountInvitationMail;
use App\Models\SaaS\AccountInvitation;
use App\Models\SaaS\EmailVerification;
use App\Models\SaaS\OnboardingProfile;
use App\Models\SaaS\Plan;
use App\Models\SaaS\Subscription;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AccountOnboardingService
{
    private const MASTER_ROLE_SLUG = 'master';

    private const MEMBER_ROLE_SLUG = 'organization-member';

    public function __construct(private readonly AuditLogService $audit) {}

    public function planCatalog(): array
    {
        return Plan::query()
            ->active()
            ->ordered()
            ->get()
            ->mapWithKeys(fn (Plan $plan): array => [$plan->code => $this->serializePlan($plan)])
            ->all();
    }

    public function planForCode(?string $planCode): ?array
    {
        if ($planCode === null || $planCode === '') {
            return null;
        }

        $plan = Plan::query()->where('code', $planCode)->first();

        return $plan ? $this->serializePlan($plan) : null;
    }

    public function dueDateForPlan(string $planCode, CarbonInterface $from): ?CarbonInterface
    {
        $plan = $this->planForCode($planCode);
        $dueDate = null;

        if ($plan !== null) {
            if (isset($plan['trial_days'])) {
                $dueDate = $from->copy()->addDays((int) $plan['trial_days']);
            } elseif (isset($plan['interval_months'])) {
                $dueDate = $from->copy()->addMonthsNoOverflow((int) $plan['interval_months']);
            }
        }

        return $dueDate;
    }

    /**
     * @return array{user: User, emailVerificationToken: string}
     */
    public function createMasterUser(array $data, Request $request): array
    {
        $email = mb_strtolower(trim((string) $data['email']));

        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => __('messages.email_already_registered'),
            ]);
        }

        return DB::transaction(function () use ($data, $request, $email): array {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make((string) $data['password']),
                'preferred_locale' => (string) ($data['preferred_locale'] ?? config('app.locale', 'en')),
                'current_company_id' => null,
                'is_active' => true,
            ]);

            $token = Str::random(64);

            EmailVerification::query()->create([
                'user_id' => $user->id,
                'token' => hash('sha256', $token),
                'expires_at' => now()->addDay(),
                'requested_ip' => $request->ip(),
            ]);

            $this->audit->record(
                event: 'account.user.created',
                context: ['email' => $user->email],
                userId: $user->id,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return [
                'user' => $user,
                'emailVerificationToken' => $token,
            ];
        });
    }

    public function createCompany(User $user, array $data, Request $request): Company
    {
        return DB::transaction(function () use ($user, $data, $request): Company {
            $company = Company::query()->create([
                'name' => $data['company_name'],
                'code' => $this->uniqueCode($data['company_name']),
                'slug' => $this->uniqueSlug($data['company_name']),
                'domain' => $data['company_domain'] !== '' ? mb_strtolower(trim((string) $data['company_domain'])) : null,
                'segment' => $data['segment'] !== '' ? $data['segment'] : null,
                'operation_size' => $data['operation_size'] !== '' ? $data['operation_size'] : null,
                'timezone' => $data['timezone'] ?? 'UTC',
                'preferences' => [
                    'onboarding_stage' => 'company',
                ],
                'is_active' => true,
            ]);

            $user->forceFill([
                'current_company_id' => $company->id,
            ])->save();

            $company->users()->syncWithoutDetaching([$user->id]);

            $masterRole = $this->ensureMasterRole($company->id);
            $user->roles()->syncWithoutDetaching([
                $masterRole->id => ['company_id' => $company->id],
            ]);

            OnboardingProfile::query()->updateOrCreate(
                ['company_id' => $company->id, 'user_id' => $user->id],
                ['progress' => 25]
            );

            $this->audit->record(
                event: 'account.company.created',
                context: ['company_id' => $company->id],
                userId: $user->id,
                companyId: $company->id,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return $company;
        });
    }

    public function createPlanSubscription(User $user, array $data, Request $request): Subscription
    {
        return $this->upsertPlanSubscription($user, $data, $request);
    }

    public function changePlanSubscription(User $user, array $data, Request $request): Subscription
    {
        return $this->upsertPlanSubscription($user, $data, $request);
    }

    public function recordPaymentProvider(Subscription $subscription, array $payment): Subscription
    {
        $subscription->forceFill([
            'provider' => 'pagarme',
            'provider_customer_id' => $payment['customer_id'] ?? null,
            'provider_subscription_id' => $payment['order_id'] ?? $payment['charge_id'] ?? null,
        ])->save();

        return $subscription;
    }

    private function upsertPlanSubscription(User $user, array $data, Request $request): Subscription
    {
        $companyId = (int) ($user->current_company_id ?? 0);

        if ($companyId <= 0) {
            throw ValidationException::withMessages([
                'company' => __('messages.account_not_found_for_plan'),
            ]);
        }

        $company = Company::query()->findOrFail($companyId);

        $planCode = (string) $data['plan_code'];
        $planModel = Plan::query()->active()->where('code', $planCode)->first();

        if ($planModel === null) {
            throw ValidationException::withMessages([
                'plan_code' => __('messages.invalid_plan'),
            ]);
        }

        return DB::transaction(function () use ($user, $request, $company, $planCode, $planModel): Subscription {
            $plan = $this->serializePlan($planModel);

            if (($plan['allow_once'] ?? false) === true && Trial::query()->where('company_id', $company->id)->exists()) {
                throw ValidationException::withMessages([
                    'plan_code' => __('messages.free_trial_already_used'),
                ]);
            }

            $startsAt = now();
            $endsAt = $this->dueDateForPlan($planCode, $startsAt);

            $subscription = Subscription::query()->updateOrCreate(
                ['company_id' => $company->id],
                [
                    'trial_id' => null,
                    'provider' => 'manual',
                    'plan_code' => $planCode,
                    'status' => (string) ($plan['status'] ?? 'active'),
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'canceled_at' => null,
                ]
            );

            $trial = null;

            if (isset($plan['trial_days']) && $endsAt !== null) {
                $trial = Trial::query()->updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'trial_start_date' => $startsAt,
                        'trial_end_date' => $endsAt,
                        'grace_ends_at' => $endsAt->copy()->addDays(3),
                        'status' => 'active',
                        'expired_at' => null,
                        'is_expired' => false,
                        'email_domain' => Str::after((string) $user->email, '@'),
                        'registration_ip' => $request->ip(),
                    ]
                );

                $subscription->forceFill([
                    'trial_id' => $trial->id,
                ])->save();
            }

            $company->update([
                'preferences' => array_merge($company->preferences ?? [], [
                    'selected_plan' => $planCode,
                    'plan_selected_at' => now()->toISOString(),
                ]),
            ]);

            OnboardingProfile::query()->updateOrCreate(
                ['company_id' => $company->id, 'user_id' => $user->id],
                ['progress' => 75]
            );

            $this->audit->record(
                event: 'account.plan.selected',
                context: ['plan_code' => $planCode, 'subscription_id' => $subscription->id, 'trial_id' => $trial?->id],
                userId: $user->id,
                companyId: $company->id,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return $subscription;
        });
    }

    /**
     * Creates the complete trial account used by the public API flow.
     *
     * @return array{user: User, company: Company, trial: Trial, emailVerificationToken: string}
     */
    public function registerTrial(array $data, Request $request): array
    {
        $email = mb_strtolower(trim((string) $data['email']));
        $domain = Str::after($email, '@');

        $this->guardAgainstTrialAbuse($email, $domain);

        return DB::transaction(function () use ($data, $request, $email, $domain): array {
            $company = Company::query()->create([
                'name' => $data['company'],
                'code' => $this->uniqueCode($data['company']),
                'slug' => $this->uniqueSlug($data['company']),
                'domain' => $domain,
                'timezone' => 'UTC',
                'preferences' => ['source' => 'trial-signup'],
                'is_active' => true,
            ]);

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make((string) $data['password']),
                'current_company_id' => $company->id,
                'is_active' => true,
            ]);

            $company->users()->attach($user->id);
            $masterRole = $this->ensureMasterRole($company->id);
            $user->roles()->attach($masterRole->id, ['company_id' => $company->id]);

            $trial = Trial::query()->create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'trial_start_date' => now(),
                'trial_end_date' => now()->addDays(14),
                'grace_ends_at' => now()->addDays(17),
                'status' => 'active',
                'is_expired' => false,
                'email_domain' => $domain,
                'registration_ip' => $request->ip(),
            ]);

            Subscription::query()->create([
                'company_id' => $company->id,
                'trial_id' => $trial->id,
                'provider' => 'stripe',
                'plan_code' => 'trial',
                'status' => 'trialing',
                'starts_at' => now(),
                'ends_at' => $trial->trial_end_date,
            ]);

            OnboardingProfile::query()->create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'progress' => 10,
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
                context: ['trial_id' => $trial->id, 'company_id' => $company->id],
                userId: $user->id,
                companyId: $company->id,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return [
                'user' => $user,
                'company' => $company,
                'trial' => $trial,
                'emailVerificationToken' => $token,
            ];
        });
    }

    public function registerTrialFromSocial(string $name, string $company, string $email, Request $request): array
    {
        return $this->registerTrial([
            'name' => $name,
            'company' => $company,
            'email' => $email,
            'password' => Str::random(32),
        ], $request);
    }

    public function completeOnboarding(User $user, array $data): OnboardingProfile
    {
        $company = Company::query()->findOrFail((int) $user->current_company_id);
        $profile = OnboardingProfile::query()->firstOrNew([
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]);

        $profile->fill([
            'import_data' => (bool) ($data['import_data'] ?? false),
            'connect_integrations' => (bool) ($data['connect_integrations'] ?? false),
            'invite_team' => (bool) ($data['invite_team'] ?? false),
            'progress' => 100,
            'completed_at' => now(),
        ])->save();

        $company->update([
            'segment' => $data['segment'] ?? null,
            'operation_size' => $data['operation_size'] ?? null,
            'timezone' => $data['timezone'] ?? 'UTC',
            'preferences' => array_merge($company->preferences ?? [], [
                'import_data' => $profile->import_data,
                'connect_integrations' => $profile->connect_integrations,
                'invite_team' => $profile->invite_team,
            ]),
        ]);

        return $profile;
    }

    public function isMasterUser(User $user): bool
    {
        $companyId = (int) ($user->current_company_id ?? 0);

        return $companyId > 0 && $user->roles()
            ->wherePivot('company_id', $companyId)
            ->whereIn('slug', [self::MASTER_ROLE_SLUG, 'admin', 'account-master', 'organization-admin'])
            ->exists();
    }

    public function registerTeamMember(User $actor, array $data, Request $request): User
    {
        $companyId = (int) ($actor->current_company_id ?? 0);

        if ($companyId <= 0 || ! $this->isMasterUser($actor)) {
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

            if ($user !== null && $user->companies()->whereKey($companyId)->exists()) {
                throw ValidationException::withMessages(['email' => __('messages.user_already_belongs_to_account')]);
            }

            if ($user === null) {
                $user = User::query()->create([
                    'name' => $data['name'],
                    'email' => $email,
                    'password' => Hash::make((string) $data['password']),
                    'current_company_id' => $companyId,
                    'is_active' => (bool) ($data['activate'] ?? true),
                ]);
            } elseif ($user->current_company_id === null) {
                $user->forceFill(['current_company_id' => $companyId])->save();
            }

            $company->users()->syncWithoutDetaching([$user->id]);
            $user->roles()->syncWithoutDetaching([$role->id => ['company_id' => $companyId]]);
            OnboardingProfile::query()->firstOrCreate(
                ['company_id' => $companyId, 'user_id' => $user->id],
                ['progress' => 0],
            );

            $this->audit->record(
                event: 'tenant.user.created',
                context: ['created_user_id' => $user->id, 'role' => $role->slug, 'created_by' => $actor->id],
                userId: $actor->id,
                companyId: $companyId,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return $user->fresh();
        });
    }

    private function guardAgainstTrialAbuse(string $email, string $domain): void
    {
        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'Ja existe um cadastro com este e-mail.']);
        }

        if (Trial::query()->where('email_domain', $domain)->where('created_at', '>=', now()->subDays(30))->count() >= 5) {
            throw ValidationException::withMessages(['email' => 'Limite de trial por dominio excedido. Fale com o comercial.']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePlan(Plan $plan): array
    {
        $data = [
            'id' => $plan->id,
            'code' => $plan->code,
            'label' => $plan->label,
            'description' => $plan->description,
            'payment_method' => $plan->payment_method,
            'billing_cycle_label' => $plan->billing_cycle_label,
            'amount_cents' => $plan->amount_cents,
            'interval_months' => $plan->interval_months,
            'renewable' => $plan->renewable,
            'allow_once' => $plan->allow_once,
            'status' => $plan->default_status,
            'is_active' => $plan->is_active,
            'sort_order' => $plan->sort_order,
        ];

        if ($plan->trial_days !== null) {
            $data['trial_days'] = $plan->trial_days;
        }

        return $data;
    }

    /**
     * @return Collection<int, AccountInvitation>
     */
    public function sendInvitations(User $user, array $emails, Request $request): Collection
    {
        $companyId = (int) ($user->current_company_id ?? 0);

        if ($companyId <= 0) {
            throw ValidationException::withMessages([
                'company' => __('messages.invitation_send_account_missing'),
            ]);
        }

        $company = Company::query()->findOrFail($companyId);

        $normalizedEmails = collect($emails)
            ->map(static fn ($email) => mb_strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values();

        if ($normalizedEmails->isEmpty()) {
            return collect();
        }

        $invitations = collect();

        DB::transaction(function () use ($normalizedEmails, $user, $company, $companyId, $request, &$invitations): void {
            foreach ($normalizedEmails as $email) {
                if ($email === mb_strtolower((string) $user->email)) {
                    continue;
                }

                $existingMember = User::query()
                    ->where('email', $email)
                    ->whereHas('companies', static fn ($query) => $query->where('companies.id', $companyId))
                    ->exists();

                if ($existingMember) {
                    continue;
                }

                $plainToken = Str::random(64);

                $invitation = AccountInvitation::query()->create([
                    'company_id' => $companyId,
                    'invited_by_user_id' => $user->id,
                    'email' => $email,
                    'role_slug' => self::MEMBER_ROLE_SLUG,
                    'token' => hash('sha256', $plainToken),
                    'expires_at' => now()->addDays(7),
                    'sent_at' => now(),
                    'meta' => [
                        'source' => 'onboarding-invites',
                        'ip' => $request->ip(),
                    ],
                ]);

                Mail::to($email)->queue(new AccountInvitationMail(
                    $invitation,
                    route('account-invitations.show', ['token' => $plainToken]),
                ));

                $invitations->push($invitation);
            }
        });

        if ($invitations->isNotEmpty()) {
            $this->audit->record(
                event: 'account.invites.sent',
                context: ['count' => $invitations->count()],
                userId: $user->id,
                companyId: $company->id,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );
        }

        return $invitations;
    }

    public function acceptInvitation(AccountInvitation $invitation, array $data, Request $request): User
    {
        if ($invitation->revoked_at !== null || $invitation->accepted_at !== null || $invitation->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'token' => __('messages.invitation_invalid_or_expired'),
            ]);
        }

        return DB::transaction(function () use ($invitation, $data, $request): User {
            $email = mb_strtolower(trim((string) $invitation->email));
            $user = User::query()->where('email', $email)->first();

            if ($user === null) {
                $user = User::query()->create([
                    'name' => $data['name'],
                    'email' => $email,
                    'password' => Hash::make((string) $data['password']),
                    'current_company_id' => null,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            }

            $company = Company::query()->findOrFail($invitation->company_id);
            $role = $this->ensureMemberRole($company->id);

            $company->users()->syncWithoutDetaching([$user->id]);

            $user->roles()->syncWithoutDetaching([
                $role->id => ['company_id' => $company->id],
            ]);

            if ($user->current_company_id === null) {
                $user->forceFill(['current_company_id' => $company->id])->save();
            }

            $invitation->forceFill([
                'accepted_by_user_id' => $user->id,
                'accepted_at' => now(),
            ])->save();

            $this->audit->record(
                event: 'account.invite.accepted',
                context: ['invitation_id' => $invitation->id],
                userId: $user->id,
                companyId: $invitation->company_id,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return $user->fresh();
        });
    }

    public function createSocialUser(string $name, string $email, Request $request): array
    {
        return $this->createMasterUser([
            'name' => $name,
            'email' => $email,
            'password' => Str::random(32),
        ], $request);
    }

    private function uniqueSlug(string $companyName): string
    {
        $base = Str::slug($companyName);
        $slug = $base !== '' ? $base : 'organization';
        $count = 1;

        while (Company::query()->where('slug', $slug)->exists()) {
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
        $role = Role::query()->withoutGlobalScope('tenant')->firstOrCreate(
            [
                'company_id' => $companyId,
                'slug' => self::MASTER_ROLE_SLUG,
            ],
            [
                'name' => 'Master',
            ]
        );

        $role->permissions()->sync(Permission::query()->pluck('id')->all());

        return $role;
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
