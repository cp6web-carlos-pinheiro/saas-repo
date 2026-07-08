<?php

declare(strict_types=1);

namespace Database\Seeders;

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
    }
}
