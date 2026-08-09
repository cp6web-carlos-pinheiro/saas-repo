<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SaaS\Plan;
use App\Models\SaaS\Subscription;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Illuminate\Database\Seeder;

final class TenantFoundationSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin@beyondmrp.local';

    public function run(): void
    {
        $company = Company::query()->updateOrCreate(
            ['code' => 'BEYOND_MAIN'],
            [
                'name' => 'Beyond Main Company',
                'slug' => 'beyond-main',
                'timezone' => 'UTC',
                'preferences' => ['selected_plan' => 'free_trial'],
                'is_active' => true,
            ]
        );

        $user = User::query()->updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Beyond Admin',
                'password' => 'ChangeMe123!',
                'is_active' => true,
                'current_company_id' => $company->id,
            ]
        );

        $user->companies()->syncWithoutDetaching([$company->id]);

        if ((int) $user->current_company_id !== (int) $company->id) {
            $user->current_company_id = $company->id;
            $user->save();
        }

        $plan = Plan::query()->where('code', 'free_trial')->firstOrFail();
        $startsAt = now();
        $endsAt = $startsAt->copy()->addDays($plan->trial_days ?? 14);

        $trial = Trial::query()->firstOrCreate(
            ['user_id' => $user->id, 'company_id' => $company->id],
            [
                'trial_start_date' => $startsAt,
                'trial_end_date' => $endsAt,
                'grace_ends_at' => $endsAt->copy()->addDays(3),
                'status' => 'active',
                'is_expired' => false,
                'email_domain' => 'beyondmrp.local',
            ]
        );

        Subscription::query()->updateOrCreate(
            ['company_id' => $company->id],
            [
                'trial_id' => $trial->id,
                'provider' => 'manual',
                'plan_code' => $plan->code,
                'status' => $plan->default_status,
                'starts_at' => $trial->trial_start_date,
                'ends_at' => $trial->trial_end_date,
                'canceled_at' => null,
            ]
        );
    }
}
