<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $oldSlug = 'purchasing.suppliers.rules.manage';
        $newSlug = 'purchasing.supplier-rules.manage';

        $oldPermission = DB::table('permissions')->where('slug', $oldSlug)->first();
        $newPermission = DB::table('permissions')->where('slug', $newSlug)->first();

        if (! $oldPermission) {
            return;
        }

        if (! $newPermission) {
            DB::table('permissions')
                ->where('id', $oldPermission->id)
                ->update(['slug' => $newSlug]);

            return;
        }

        DB::table('permission_role')
            ->where('permission_id', $oldPermission->id)
            ->whereNotExists(function ($query) use ($newPermission): void {
                $query->select(DB::raw('1'))
                    ->from('permission_role as pr2')
                    ->whereColumn('pr2.role_id', 'permission_role.role_id')
                    ->where('pr2.permission_id', $newPermission->id);
            })
            ->update(['permission_id' => $newPermission->id]);

        DB::table('permission_role')
            ->where('permission_id', $oldPermission->id)
            ->delete();

        DB::table('permission_user_overrides')
            ->where('permission_id', $oldPermission->id)
            ->whereNotExists(function ($query) use ($newPermission): void {
                $query->select(DB::raw('1'))
                    ->from('permission_user_overrides as puo2')
                    ->whereColumn('puo2.company_id', 'permission_user_overrides.company_id')
                    ->whereColumn('puo2.user_id', 'permission_user_overrides.user_id')
                    ->where('puo2.permission_id', $newPermission->id);
            })
            ->update(['permission_id' => $newPermission->id]);

        DB::table('permission_user_overrides')
            ->where('permission_id', $oldPermission->id)
            ->delete();

        DB::table('permissions')->where('id', $oldPermission->id)->delete();
    }

    public function down(): void
    {
        $oldSlug = 'purchasing.suppliers.rules.manage';
        $newSlug = 'purchasing.supplier-rules.manage';

        $newPermission = DB::table('permissions')->where('slug', $newSlug)->first();
        $oldPermission = DB::table('permissions')->where('slug', $oldSlug)->first();

        if (! $newPermission) {
            return;
        }

        if (! $oldPermission) {
            DB::table('permissions')
                ->where('id', $newPermission->id)
                ->update(['slug' => $oldSlug]);

            return;
        }

        DB::table('permission_role')
            ->where('permission_id', $newPermission->id)
            ->whereNotExists(function ($query) use ($oldPermission): void {
                $query->select(DB::raw('1'))
                    ->from('permission_role as pr2')
                    ->whereColumn('pr2.role_id', 'permission_role.role_id')
                    ->where('pr2.permission_id', $oldPermission->id);
            })
            ->update(['permission_id' => $oldPermission->id]);

        DB::table('permission_role')
            ->where('permission_id', $newPermission->id)
            ->delete();

        DB::table('permission_user_overrides')
            ->where('permission_id', $newPermission->id)
            ->whereNotExists(function ($query) use ($oldPermission): void {
                $query->select(DB::raw('1'))
                    ->from('permission_user_overrides as puo2')
                    ->whereColumn('puo2.company_id', 'permission_user_overrides.company_id')
                    ->whereColumn('puo2.user_id', 'permission_user_overrides.user_id')
                    ->where('puo2.permission_id', $oldPermission->id);
            })
            ->update(['permission_id' => $oldPermission->id]);

        DB::table('permission_user_overrides')
            ->where('permission_id', $newPermission->id)
            ->delete();

        DB::table('permissions')->where('id', $newPermission->id)->delete();
    }
};
