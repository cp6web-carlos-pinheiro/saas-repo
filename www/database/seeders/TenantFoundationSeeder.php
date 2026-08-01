<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SaaS\Organization;
use App\Models\SaaS\Plan;
use App\Models\SaaS\Subscription;
use App\Models\SaaS\Tenant;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Illuminate\Database\Seeder;

final class TenantFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->updateOrCreate(
            ['code' => 'BEYOND_MAIN'],
            [
                'name' => 'Beyond Main Company',
                'is_active' => true,
            ]
        );

        $user = User::query()->updateOrCreate(
            ['email' => 'admin@beyondmrp.local'],
            [
                'name' => 'Beyond Admin',
                'password' => 'ChangeMe123!',
                'is_active' => true,
                'current_company_id' => $company->id,
            ]
        );

        $user->companies()->syncWithoutDetaching([
            $company->id => ['is_default' => true],
        ]);

        if ((int) $user->current_company_id !== (int) $company->id) {
            $user->current_company_id = $company->id;
            $user->save();
        }

        $organization = Organization::query()->updateOrCreate(
            ['company_id' => $company->id],
            [
                'name' => $company->name,
                'slug' => 'beyond-main',
                'timezone' => 'UTC',
                'preferences' => ['selected_plan' => 'free_trial'],
            ]
        );

        Tenant::query()->updateOrCreate(
            ['organization_id' => $organization->id],
            [
                'name' => $organization->name,
                'slug' => $organization->slug,
                'is_active' => true,
            ]
        );

        $plan = Plan::query()->where('code', 'free_trial')->firstOrFail();
        $startsAt = now();
        $endsAt = $startsAt->copy()->addDays($plan->trial_days ?? 14);

        $trial = Trial::query()->firstOrCreate(
            ['user_id' => $user->id, 'organization_id' => $organization->id],
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
            ['organization_id' => $organization->id],
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
