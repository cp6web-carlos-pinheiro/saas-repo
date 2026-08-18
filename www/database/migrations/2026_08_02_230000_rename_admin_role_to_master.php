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

            $masterRoleId = DB::table('roles')
                ->where('company_id', $companyId)
                ->where('slug', 'master')
                ->value('id');

            if ($masterRoleId === null) {
                $masterRoleId = DB::table('roles')->insertGetId([
                    'company_id' => $companyId,
                    'name' => 'Master',
                    'slug' => 'master',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $legacyRoleIds = DB::table('roles')
                ->where('company_id', $companyId)
                ->where(function ($query): void {
                    $query->whereIn('slug', ['admin', 'account-master', 'organization-admin'])
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
                    ->where('role_id', (int) $masterRoleId)
                    ->where('user_id', $userId)
                    ->where('company_id', $companyId)
                    ->exists();

                if (! $alreadyLinked) {
                    DB::table('role_user')->insert([
                        'role_id' => (int) $masterRoleId,
                        'user_id' => $userId,
                        'company_id' => $companyId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            DB::table('company_role_template_versions')
                ->whereIn('role_id', $legacyRoleIds)
                ->update(['role_id' => (int) $masterRoleId]);

            DB::table('role_user')
                ->where('company_id', $companyId)
                ->whereIn('role_id', $legacyRoleIds)
                ->delete();

            DB::table('roles')
                ->where('company_id', $companyId)
                ->whereIn('id', $legacyRoleIds)
                ->delete();

            DB::table('roles')
                ->where('id', (int) $masterRoleId)
                ->update(['name' => 'Master', 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        // Irreversible data consolidation migration.
    }
};
