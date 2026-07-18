<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Mail\AccountInvitationMail;
use App\Models\SaaS\AccountInvitation;
use App\Models\SaaS\EmailVerification;
use App\Models\SaaS\OnboardingProfile;
use App\Models\SaaS\Organization;
use App\Models\SaaS\Subscription;
use App\Models\SaaS\Tenant;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
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
    private const MASTER_ROLE_SLUG = 'account-master';

    private const MEMBER_ROLE_SLUG = 'organization-member';

    private const CREDIT_CARD_PAYMENT_METHOD = 'Cartao de credito';

    private const PLAN_CATALOG = [
        'free_trial' => [
            'label' => 'Gratis 14 dias',
            'description' => 'Acesso gratuito por 14 dias. Disponivel uma unica vez e sem renovacao.',
            'payment_method' => 'Sem cobranca',
            'billing_cycle_label' => 'Uso unico de 14 dias',
            'trial_days' => 14,
            'renewable' => false,
            'allow_once' => true,
            'status' => 'trialing',
        ],
        'monthly' => [
            'label' => 'Plano mensal',
            'description' => 'O valor e cobrado mensalmente no cartao de credito.',
            'payment_method' => self::CREDIT_CARD_PAYMENT_METHOD,
            'billing_cycle_label' => 'Cobranca mensal',
            'interval_months' => 1,
            'renewable' => true,
            'status' => 'active',
        ],
        'semiannual' => [
            'label' => 'Plano semestral',
            'description' => 'O valor e cobrado semestralmente no cartao de credito.',
            'payment_method' => self::CREDIT_CARD_PAYMENT_METHOD,
            'billing_cycle_label' => 'Cobranca semestral',
            'interval_months' => 6,
            'renewable' => true,
            'status' => 'active',
        ],
        'annual' => [
            'label' => 'Plano anual',
            'description' => 'O valor e cobrado anualmente no cartao de credito.',
            'payment_method' => self::CREDIT_CARD_PAYMENT_METHOD,
            'billing_cycle_label' => 'Cobranca anual',
            'interval_months' => 12,
            'renewable' => true,
            'status' => 'active',
        ],
    ];

    public function __construct(private readonly AuditLogService $audit) {}

    public function planCatalog(): array
    {
        return self::PLAN_CATALOG;
    }

    public function planForCode(?string $planCode): ?array
    {
        if ($planCode === null || $planCode === '') {
            return null;
        }

        return self::PLAN_CATALOG[$planCode] ?? null;
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
                'is_active' => true,
            ]);

            $organization = Organization::query()->create([
                'company_id' => $company->id,
                'name' => $data['company_name'],
                'slug' => $this->uniqueSlug($data['company_name']),
                'domain' => $data['company_domain'] !== '' ? mb_strtolower(trim((string) $data['company_domain'])) : null,
                'segment' => $data['segment'] !== '' ? $data['segment'] : null,
                'operation_size' => $data['operation_size'] !== '' ? $data['operation_size'] : null,
                'timezone' => $data['timezone'] ?? 'UTC',
                'preferences' => [
                    'onboarding_stage' => 'company',
                ],
            ]);

            Tenant::query()->create([
                'organization_id' => $organization->id,
                'name' => $company->name,
                'slug' => $organization->slug,
                'is_active' => true,
            ]);

            $user->forceFill([
                'current_company_id' => $company->id,
            ])->save();

            $company->users()->syncWithoutDetaching([
                $user->id => ['is_default' => true],
            ]);

            $masterRole = $this->ensureMasterRole($company->id);
            $user->roles()->syncWithoutDetaching([
                $masterRole->id => ['company_id' => $company->id],
            ]);

            OnboardingProfile::query()->updateOrCreate(
                ['organization_id' => $organization->id, 'user_id' => $user->id],
                [
                    'timezone' => $organization->timezone,
                    'progress' => 25,
                ]
            );

            $this->audit->record(
                event: 'account.company.created',
                context: ['company_id' => $company->id, 'organization_id' => $organization->id],
                userId: $user->id,
                organizationId: $organization->id,
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

    private function upsertPlanSubscription(User $user, array $data, Request $request): Subscription
    {
        $companyId = (int) ($user->current_company_id ?? 0);

        if ($companyId <= 0) {
            throw ValidationException::withMessages([
                'company' => __('messages.account_not_found_for_plan'),
            ]);
        }

        $organization = Organization::query()->where('company_id', $companyId)->firstOrFail();

        $planCode = (string) $data['plan_code'];

        if (! array_key_exists($planCode, self::PLAN_CATALOG)) {
            throw ValidationException::withMessages([
                'plan_code' => __('messages.invalid_plan'),
            ]);
        }

        return DB::transaction(function () use ($user, $request, $organization, $planCode): Subscription {
            $plan = self::PLAN_CATALOG[$planCode];

            if (($plan['allow_once'] ?? false) === true && Trial::query()->where('organization_id', $organization->id)->exists()) {
                throw ValidationException::withMessages([
                    'plan_code' => __('messages.free_trial_already_used'),
                ]);
            }

            $startsAt = now();
            $endsAt = $this->dueDateForPlan($planCode, $startsAt);

            $subscription = Subscription::query()->updateOrCreate(
                ['organization_id' => $organization->id],
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
                        'organization_id' => $organization->id,
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

            $organization->update([
                'preferences' => array_merge($organization->preferences ?? [], [
                    'selected_plan' => $planCode,
                    'selected_plan_label' => $plan['label'],
                    'selected_plan_payment_method' => $plan['payment_method'],
                    'selected_plan_billing_cycle' => $plan['billing_cycle_label'],
                    'plan_selected_at' => now()->toISOString(),
                ]),
            ]);

            OnboardingProfile::query()->updateOrCreate(
                ['organization_id' => $organization->id, 'user_id' => $user->id],
                [
                    'timezone' => $organization->timezone,
                    'progress' => 75,
                ]
            );

            $this->audit->record(
                event: 'account.plan.selected',
                context: ['plan_code' => $planCode, 'subscription_id' => $subscription->id, 'trial_id' => $trial?->id],
                userId: $user->id,
                organizationId: $organization->id,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return $subscription;
        });
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

        $organization = Organization::query()->where('company_id', $companyId)->firstOrFail();

        $normalizedEmails = collect($emails)
            ->map(static fn ($email) => mb_strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values();

        if ($normalizedEmails->isEmpty()) {
            return collect();
        }

        $invitations = collect();

        DB::transaction(function () use ($normalizedEmails, $user, $organization, $companyId, $request, &$invitations): void {
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
                    'organization_id' => $organization->id,
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
                organizationId: $organization->id,
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

            $company->users()->syncWithoutDetaching([
                $user->id => ['is_default' => $user->current_company_id === null],
            ]);

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
                organizationId: $invitation->organization_id,
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
                'name' => 'Account Master',
                'description' => 'Conta principal com permissao para gerenciar usuarios',
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
                'description' => 'Usuario padrao da organizacao',
            ]
        );
    }
}
