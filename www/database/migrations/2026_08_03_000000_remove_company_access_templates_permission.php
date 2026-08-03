<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TEMPLATE_PERMISSION_SLUG = 'company-access.templates.manage';

    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $permissionId = DB::table('permissions')
            ->where('slug', self::TEMPLATE_PERMISSION_SLUG)
            ->value('id');

        if ($permissionId === null) {
            return;
        }

        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')
                ->where('permission_id', $permissionId)
                ->delete();
        }

        if (Schema::hasTable('permission_user_overrides')) {
            DB::table('permission_user_overrides')
                ->where('permission_id', $permissionId)
                ->delete();
        }

        DB::table('permissions')
            ->where('id', $permissionId)
            ->delete();
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $exists = DB::table('permissions')
            ->where('slug', self::TEMPLATE_PERMISSION_SLUG)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('permissions')->insert([
            'name' => 'Manage role templates',
            'slug' => self::TEMPLATE_PERMISSION_SLUG,
            'module' => 'users',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
