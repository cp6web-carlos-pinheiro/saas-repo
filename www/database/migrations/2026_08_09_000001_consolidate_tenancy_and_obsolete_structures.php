<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            $this->consolidateOrganizationsIntoCompanies();
            $this->removeObsoleteStructures();
            $this->consolidateProductionOutputs();
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        // This migration deliberately consolidates duplicated sources of truth.
        // Restoring the old structures would recreate ambiguity and cannot be
        // done losslessly after new records have been written.
    }

    private function consolidateOrganizationsIntoCompanies(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('slug', 180)->nullable()->after('code');
            $table->string('domain', 180)->nullable()->after('slug');
            $table->string('segment', 120)->nullable()->after('domain');
            $table->string('operation_size', 80)->nullable()->after('segment');
            $table->string('timezone', 80)->default('UTC')->after('operation_size');
            $table->json('preferences')->nullable()->after('timezone');
        });

        $organizations = DB::table('organizations')->orderBy('id')->get();

        foreach ($organizations as $organization) {
            DB::table('companies')->where('id', (int) $organization->company_id)->update([
                'slug' => $organization->slug,
                'domain' => $organization->domain,
                'segment' => $organization->segment,
                'operation_size' => $organization->operation_size,
                'timezone' => $organization->timezone,
                'preferences' => $organization->preferences,
            ]);
        }

        $profiles = DB::table('onboarding_profiles')->get();

        foreach ($profiles as $profile) {
            $companyId = DB::table('organizations')->where('id', (int) $profile->organization_id)->value('company_id');

            if ($companyId === null) {
                continue;
            }

            $company = DB::table('companies')->where('id', (int) $companyId)->first();
            DB::table('companies')->where('id', (int) $companyId)->update([
                'segment' => $company?->segment ?? $profile->segment,
                'operation_size' => $company?->operation_size ?? $profile->operation_size,
                'timezone' => $company?->timezone ?: ($profile->timezone ?: 'UTC'),
            ]);
        }

        $this->replaceOrganizationReference('trials', false, true);
        $this->replaceOrganizationReference('subscriptions', false, false);
        $this->replaceOrganizationReference('onboarding_profiles', false, true);
        $this->replaceOrganizationReference('audit_logs', true, false);
        $this->replaceOrganizationReference('account_invitations', false, false, true);

        Schema::table('onboarding_profiles', function (Blueprint $table): void {
            $table->dropColumn(['segment', 'operation_size', 'timezone']);
        });

        Schema::dropIfExists('tenants');
        Schema::dropIfExists('organizations');

        Schema::table('companies', function (Blueprint $table): void {
            $table->unique('slug', 'uq_companies_slug');
            $table->index('domain', 'ix_companies_domain');
        });
    }

    private function replaceOrganizationReference(
        string $tableName,
        bool $nullable,
        bool $uniqueWithUser,
        bool $companyAlreadyExists = false,
    ): void {
        if (! $companyAlreadyExists) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('company_id')->nullable();
            });
        }

        $rows = DB::table($tableName)->whereNotNull('organization_id')->get(['id', 'organization_id']);

        foreach ($rows as $row) {
            $companyId = DB::table('organizations')->where('id', (int) $row->organization_id)->value('company_id');

            if ($companyId !== null) {
                DB::table($tableName)->where('id', (int) $row->id)->update(['company_id' => (int) $companyId]);
            }
        }

        $indexes = match ($tableName) {
            'trials' => ['trials_organization_id_status_index'],
            'onboarding_profiles' => ['onboarding_profiles_organization_id_user_id_unique'],
            'account_invitations' => ['account_invitations_organization_id_accepted_at_index'],
            default => [],
        };

        foreach ($indexes as $index) {
            try {
                Schema::table($tableName, static fn (Blueprint $table) => $table->dropIndex($index));
            } catch (Throwable) {
                // Index names may differ across database engines.
            }
        }

        try {
            Schema::table($tableName, static fn (Blueprint $table) => $table->dropForeign(['organization_id']));
        } catch (Throwable) {
            // SQLite rebuilds the table while dropping the column below.
        }

        Schema::table($tableName, static function (Blueprint $table): void {
            $table->dropColumn('organization_id');
        });

        Schema::table($tableName, function (Blueprint $table) use ($nullable, $companyAlreadyExists): void {
            if (! $companyAlreadyExists) {
                $table->unsignedBigInteger('company_id')->nullable($nullable)->change();
                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            }
        });

        if ($uniqueWithUser) {
            Schema::table($tableName, static function (Blueprint $table): void {
                $table->unique(['company_id', 'user_id'], 'uq_onboarding_company_user');
            });
        }

        if ($tableName === 'trials') {
            Schema::table($tableName, static function (Blueprint $table): void {
                $table->index(['company_id', 'status'], 'ix_trials_company_status');
            });
        }

        if ($tableName === 'account_invitations') {
            Schema::table($tableName, static function (Blueprint $table): void {
                $table->index(['company_id', 'accepted_at'], 'ix_invitations_company_accepted');
            });
        }
    }

    private function removeObsoleteStructures(): void
    {
        Schema::dropIfExists('permission_user_overrides');
        Schema::dropIfExists('master_data_records');
        Schema::dropIfExists('password_resets');

        if (Schema::hasColumn('company_user', 'is_default')) {
            Schema::table('company_user', static function (Blueprint $table): void {
                $table->dropColumn('is_default');
            });
        }

        if (Schema::hasColumn('products', 'uom')) {
            Schema::table('products', static function (Blueprint $table): void {
                $table->dropColumn('uom');
            });
        }
    }

    private function consolidateProductionOutputs(): void
    {
        Schema::table('production_operation_outputs', function (Blueprint $table): void {
            $table->foreignId('production_order_id')->nullable()->after('company_id')
                ->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('work_center_id')->nullable()->after('production_order_operation_id')
                ->constrained('work_centers')->nullOnDelete();
            $table->decimal('setup_time_minutes', 10, 2)->default(0)->after('work_center_id');
            $table->decimal('process_time_minutes', 10, 2)->default(0)->after('setup_time_minutes');
            $table->timestamp('inspected_at')->nullable()->after('inspection_status');
            $table->text('inspection_notes')->nullable()->after('inspected_at');
            $table->foreignId('created_by')->nullable()->after('operator_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('production_operation_outputs', static function (Blueprint $table): void {
            $table->unsignedBigInteger('production_order_operation_id')->nullable()->change();
        });

        DB::table('production_operation_outputs as output')
            ->join('production_order_operations as operation', 'operation.id', '=', 'output.production_order_operation_id')
            ->whereNull('output.production_order_id')
            ->update(['output.production_order_id' => DB::raw('operation.production_order_id')]);

        $legacyOutputs = DB::table('production_order_outputs')->orderBy('id')->get();

        foreach ($legacyOutputs as $legacy) {
            $operationId = null;

            if ($legacy->operation_no !== null) {
                $operationId = DB::table('production_order_operations')
                    ->where('production_order_id', (int) $legacy->production_order_id)
                    ->where('operation_no', (int) $legacy->operation_no)
                    ->value('id');
            }

            DB::table('production_operation_outputs')->insert([
                'company_id' => $legacy->company_id,
                'production_order_id' => $legacy->production_order_id,
                'production_order_operation_id' => $operationId,
                'work_center_id' => $legacy->work_center_id,
                'setup_time_minutes' => $legacy->setup_time_minutes,
                'process_time_minutes' => $legacy->process_time_minutes,
                'quantity_good' => $legacy->quantity_completed,
                'quantity_scrapped' => $legacy->quantity_scrapped,
                'quantity_rework' => 0,
                'lot_number' => $legacy->lot_number,
                'inspection_status' => $legacy->inspection_status,
                'inspected_at' => $legacy->inspected_at,
                'inspection_notes' => $legacy->inspection_notes,
                'operator_id' => $legacy->created_by,
                'created_by' => $legacy->created_by,
                'production_resource_id' => null,
                'reported_at' => $legacy->produced_at,
                'notes' => null,
                'metadata' => $legacy->metadata,
                'created_at' => $legacy->created_at,
                'updated_at' => $legacy->updated_at,
            ]);
        }

        Schema::dropIfExists('production_order_outputs');

        Schema::table('production_operation_outputs', static function (Blueprint $table): void {
            $table->index(['company_id', 'production_order_id', 'reported_at'], 'ix_production_outputs_order_date');
        });
    }
};
