<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OVERRIDES_PERMISSION = 'company-access.overrides.update';

    public function up(): void
    {
        if (Schema::hasTable('permissions')) {
            $permissionId = DB::table('permissions')
                ->where('slug', self::OVERRIDES_PERMISSION)
                ->value('id');

            if ($permissionId !== null) {
                if (Schema::hasTable('permission_role')) {
                    DB::table('permission_role')
                        ->where('permission_id', (int) $permissionId)
                        ->delete();
                }

                DB::table('permissions')
                    ->where('id', (int) $permissionId)
                    ->delete();
            }
        }

        if (Schema::hasTable('permission_user_overrides')) {
            Schema::drop('permission_user_overrides');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('permission_user_overrides')) {
            Schema::create('permission_user_overrides', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->boolean('is_allowed');
                $table->string('reason', 255)->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['company_id', 'user_id', 'permission_id'], 'puo_cmp_usr_perm_uq');
            });
        }

        if (Schema::hasTable('permissions')) {
            $exists = DB::table('permissions')
                ->where('slug', self::OVERRIDES_PERMISSION)
                ->exists();

            if (! $exists) {
                $now = now();

                DB::table('permissions')->insert([
                    'name' => 'Update user permission overrides',
                    'slug' => self::OVERRIDES_PERMISSION,
                    'module' => 'users',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
