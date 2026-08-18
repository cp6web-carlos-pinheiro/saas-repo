<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private const PERMISSION_SLUGS_TO_REMOVE = [
        'company-access.rbac.manage',
        'company-access.audit.read',
    ];

    public function up(): void
    {
        if (Schema::hasTable('permissions')) {
            $permissionIds = DB::table('permissions')
                ->whereIn('slug', self::PERMISSION_SLUGS_TO_REMOVE)
                ->pluck('id')
                ->map(static fn ($id): int => (int) $id)
                ->all();

            if ($permissionIds !== []) {
                if (Schema::hasTable('permission_role')) {
                    DB::table('permission_role')
                        ->whereIn('permission_id', $permissionIds)
                        ->delete();
                }

                if (Schema::hasTable('permission_user_overrides')) {
                    DB::table('permission_user_overrides')
                        ->whereIn('permission_id', $permissionIds)
                        ->delete();
                }

                DB::table('permissions')
                    ->whereIn('id', $permissionIds)
                    ->delete();
            }
        }

        if (Schema::hasTable('rbac_change_requests')) {
            Schema::drop('rbac_change_requests');
        }

        if (Schema::hasTable('company_role_template_versions')) {
            Schema::drop('company_role_template_versions');
        }

        if (Schema::hasTable('role_template_versions')) {
            Schema::drop('role_template_versions');
        }

        if (Schema::hasTable('role_templates')) {
            Schema::drop('role_templates');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('role_templates')) {
            Schema::create('role_templates', function (Blueprint $table): void {
                $table->id();
                $table->string('key', 120)->unique();
                $table->string('name', 120);
                $table->string('module_focus', 100)->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('current_version')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('role_template_versions')) {
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
        }

        if (! Schema::hasTable('company_role_template_versions')) {
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
        }

        if (! Schema::hasTable('rbac_change_requests')) {
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

        if (Schema::hasTable('permissions')) {
            $now = now();

            $current = DB::table('permissions')
                ->whereIn('slug', self::PERMISSION_SLUGS_TO_REMOVE)
                ->pluck('slug')
                ->all();

            $missing = array_values(array_diff(self::PERMISSION_SLUGS_TO_REMOVE, $current));

            $toInsert = [];

            foreach ($missing as $slug) {
                $toInsert[] = [
                    'name' => match ($slug) {
                        'company-access.rbac.manage' => 'Manage RBAC console',
                        'company-access.audit.read' => 'Read RBAC audit history',
                        default => $slug,
                    },
                    'slug' => $slug,
                    'module' => 'users',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($toInsert !== []) {
                DB::table('permissions')->insert($toInsert);
            }
        }
    }
};
