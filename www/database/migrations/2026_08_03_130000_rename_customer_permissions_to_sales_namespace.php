<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->renamePermissionSlug('customers.read', 'sales.customers.read');
        $this->renamePermissionSlug('customers.create', 'sales.customers.create');
        $this->renamePermissionSlug('customers.update', 'sales.customers.update');
    }

    public function down(): void
    {
        $this->renamePermissionSlug('sales.customers.read', 'customers.read');
        $this->renamePermissionSlug('sales.customers.create', 'customers.create');
        $this->renamePermissionSlug('sales.customers.update', 'customers.update');
    }

    private function renamePermissionSlug(string $oldSlug, string $newSlug): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $oldPermission = DB::table('permissions')->where('slug', $oldSlug)->first();
        $newPermission = DB::table('permissions')->where('slug', $newSlug)->first();

        if (! $oldPermission) {
            return;
        }

        if (! $newPermission) {
            DB::table('permissions')
                ->where('id', $oldPermission->id)
                ->update(['slug' => $newSlug, 'updated_at' => now()]);

            return;
        }

        if (Schema::hasTable('permission_role')) {
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
        }

        DB::table('permissions')->where('id', $oldPermission->id)->delete();
    }
};
