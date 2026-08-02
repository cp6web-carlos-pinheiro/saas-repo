<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('name', 120);
            $table->string('module_focus', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('current_version')->default(1);
            $table->timestamps();
        });

        Schema::create('role_template_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('role_template_id')->constrained('role_templates')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('display_name', 120);
            $table->json('permissions');
            $table->text('notes')->nullable();
            $table->foreignId('published_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['role_template_id', 'version'], 'rtv_tpl_ver_uq');
        });

        Schema::create('company_role_template_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('role_template_id')->constrained('role_templates')->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->unsignedInteger('applied_version');
            $table->foreignId('applied_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('applied_at');
            $table->timestamps();

            $table->unique(['company_id', 'role_template_id'], 'crtv_cmp_tpl_uq');
        });

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

        Schema::create('rbac_change_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->string('change_type', 100);
            $table->json('payload');
            $table->text('reason')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rbac_change_requests');
        Schema::dropIfExists('permission_user_overrides');
        Schema::dropIfExists('company_role_template_versions');
        Schema::dropIfExists('role_template_versions');
        Schema::dropIfExists('role_templates');
    }
};
