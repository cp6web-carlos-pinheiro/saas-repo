<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $companyIds = DB::table('companies')->pluck('id')->all();

        foreach ($companyIds as $companyId) {
            $companyId = (int) $companyId;

            $adminRoleId = DB::table('roles')
                ->where('company_id', $companyId)
                ->where('slug', 'admin')
                ->value('id');

            if ($adminRoleId === null) {
                $adminRoleId = DB::table('roles')->insertGetId([
                    'company_id' => $companyId,
                    'name' => 'Admin',
                    'slug' => 'admin',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $legacyRoleIds = DB::table('roles')
                ->where('company_id', $companyId)
                ->where(function ($query): void {
                    $query->where('slug', 'account-master')
                        ->orWhere('slug', 'organization-admin')
                        ->orWhere('slug', 'like', 'user-access-%-administrator');
                })
                ->pluck('id')
                ->map(static fn ($id): int => (int) $id)
                ->all();

            if ($legacyRoleIds === []) {
                continue;
            }

            $legacyUserIds = DB::table('role_user')
                ->where('company_id', $companyId)
                ->whereIn('role_id', $legacyRoleIds)
                ->pluck('user_id')
                ->map(static fn ($id): int => (int) $id)
                ->unique()
                ->values()
                ->all();

            foreach ($legacyUserIds as $userId) {
                $alreadyLinked = DB::table('role_user')
                    ->where('role_id', (int) $adminRoleId)
                    ->where('user_id', $userId)
                    ->where('company_id', $companyId)
                    ->exists();

                if (! $alreadyLinked) {
                    DB::table('role_user')->insert([
                        'role_id' => (int) $adminRoleId,
                        'user_id' => $userId,
                        'company_id' => $companyId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            DB::table('role_user')
                ->where('company_id', $companyId)
                ->whereIn('role_id', $legacyRoleIds)
                ->delete();

            DB::table('roles')
                ->where('company_id', $companyId)
                ->whereIn('id', $legacyRoleIds)
                ->delete();
        }
    }

    public function down(): void
    {
        // Irreversible data consolidation migration.
    }
};
