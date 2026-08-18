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
        Schema::dropIfExists('purchase_fiscal_entry_postings');
        Schema::dropIfExists('purchase_fiscal_entries');
        Schema::dropIfExists('warehouse_locations');
        Schema::dropIfExists('branches');

        // SQLite can fail dropping an indexed column unless the index is removed first.
        try {
            DB::statement('DROP INDEX IF EXISTS ix_plants_company_branch');
        } catch (Throwable) {
            // Ignore: index may not exist depending on environment/history.
        }

        $this->dropColumnIfExists('plants', 'branch_id');

        $this->dropColumnIfExists('products', 'ncm_id');

        $this->dropColumnIfExists('suppliers', 'tax_id');
        $this->dropColumnIfExists('suppliers', 'default_cfop_id');
        $this->dropColumnIfExists('suppliers', 'tax_profile_id');

        $this->dropColumnIfExists('customers', 'tax_id');
        $this->dropColumnIfExists('customers', 'default_cfop_id');
        $this->dropColumnIfExists('customers', 'tax_profile_id');

        $this->dropColumnIfExists('purchase_requisitions', 'department_id');
        $this->dropColumnIfExists('purchase_requisitions', 'cost_center_id');

        $this->dropColumnIfExists('purchase_orders', 'department_id');
        $this->dropColumnIfExists('purchase_orders', 'cost_center_id');

        $this->dropColumnIfExists('sales', 'department_id');
        $this->dropColumnIfExists('sales', 'cost_center_id');
        $this->dropColumnIfExists('sales', 'tax_cents');

        $this->dropColumnIfExists('production_orders', 'department_id');
        $this->dropColumnIfExists('production_orders', 'cost_center_id');

        if (Schema::hasTable('master_data_records')) {
            DB::table('master_data_records')
                ->whereIn('domain', ['departments', 'cost-centers', 'ncms', 'cfops', 'taxes'])
                ->delete();
        }

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')
                ->whereIn('slug', [
                    'admin-data.branches.read',
                    'admin-data.branches.create',
                    'admin-data.branches.update',
                    'admin-data.warehouse-locations.read',
                    'admin-data.warehouse-locations.create',
                    'admin-data.warehouse-locations.update',
                    'admin-data.departments.read',
                    'admin-data.departments.create',
                    'admin-data.departments.update',
                    'admin-data.cost-centers.read',
                    'admin-data.cost-centers.create',
                    'admin-data.cost-centers.update',
                    'admin-data.ncms.read',
                    'admin-data.ncms.create',
                    'admin-data.ncms.update',
                    'admin-data.cfops.read',
                    'admin-data.cfops.create',
                    'admin-data.cfops.update',
                    'admin-data.taxes.read',
                    'admin-data.taxes.create',
                    'admin-data.taxes.update',
                    'purchasing.fiscal-entries.read',
                    'purchasing.fiscal-entries.create',
                    'purchasing.fiscal-entries.update',
                ])
                ->delete();
        }
    }

    public function down(): void
    {
        // Intentionally left empty: this migration removes out-of-scope structures.
    }

    private function dropColumnIfExists(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $indexName = 'ix_'.$table.'_'.$column;

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($indexName): void {
                $blueprint->dropIndex($indexName);
            });
        } catch (Throwable) {
            // Ignore: index may not exist for this column in some environments.
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column): void {
            $blueprint->dropColumn($column);
        });
    }
};
