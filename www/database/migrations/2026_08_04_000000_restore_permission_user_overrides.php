<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permission_user_overrides')) {
            return;
        }

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

    public function down(): void
    {
        if (Schema::hasTable('permission_user_overrides')) {
            Schema::drop('permission_user_overrides');
        }
    }
};