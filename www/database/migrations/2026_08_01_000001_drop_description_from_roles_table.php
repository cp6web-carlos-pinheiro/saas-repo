<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $totalPermissionCount = (int) DB::table('permissions')->count();

        DB::table('roles')
            ->where('slug', 'like', 'user-access-%')
            ->orderBy('id')
            ->chunkById(100, function ($roles): void {
                foreach ($roles as $role) {
                    $rolePermissionCount = (int) DB::table('permission_role')
                        ->where('role_id', $role->id)
                        ->count();
                    $profileSuffix = $rolePermissionCount > 0 && $rolePermissionCount === $totalPermissionCount
                        ? 'administrator'
                        : 'custom';
                    $baseSlug = preg_replace('/-(administrator|custom)$/', '', (string) $role->slug) ?: (string) $role->slug;

                    DB::table('roles')
                        ->where('id', $role->id)
                        ->update([
                            'slug' => $baseSlug.'-'.$profileSuffix,
                        ]);
                }
            });

        Schema::table('roles', function (Blueprint $table): void {
            $table->dropColumn('description');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->string('description', 255)->nullable()->after('slug');
        });
    }
};
