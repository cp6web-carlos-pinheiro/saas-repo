<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('name', 150);
                $table->string('code', 50);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'code'], 'uq_branch_company_code');
                $table->index(['company_id', 'is_active'], 'ix_branch_company_active');
            });
        }

        if (Schema::hasTable('plants') && ! Schema::hasColumn('plants', 'branch_id')) {
            Schema::table('plants', function (Blueprint $table): void {
                $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');
                $table->index(['company_id', 'branch_id'], 'ix_plants_company_branch');
            });
        }

        if (! Schema::hasTable('warehouse_locations')) {
            Schema::create('warehouse_locations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('warehouse_id');
                $table->string('name', 150);
                $table->string('code', 50);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'warehouse_id', 'code'], 'uq_wh_location_company_warehouse_code');
                $table->index(['company_id', 'warehouse_id', 'is_active'], 'ix_wh_location_company_warehouse_active');
            });
        }

        if (! Schema::hasTable('master_data_records')) {
            Schema::create('master_data_records', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('domain', 40);
                $table->string('code', 60);
                $table->string('name', 180);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'domain', 'code'], 'uq_master_data_company_domain_code');
                $table->unique(['company_id', 'domain', 'name'], 'uq_master_data_company_domain_name');
                $table->index(['company_id', 'domain', 'is_active'], 'ix_master_data_company_domain_active');
            });
        }

        $this->addUnsignedColumnIfMissing('products', 'unit_id');
        $this->addUnsignedColumnIfMissing('products', 'category_id');
        $this->addUnsignedColumnIfMissing('products', 'brand_id');
        $this->addUnsignedColumnIfMissing('products', 'ncm_id');

        $this->addUnsignedColumnIfMissing('suppliers', 'default_cfop_id');
        $this->addUnsignedColumnIfMissing('suppliers', 'tax_profile_id');

        $this->addUnsignedColumnIfMissing('customers', 'default_cfop_id');
        $this->addUnsignedColumnIfMissing('customers', 'tax_profile_id');

        $this->addUnsignedColumnIfMissing('purchase_requisitions', 'department_id');
        $this->addUnsignedColumnIfMissing('purchase_requisitions', 'cost_center_id');

        $this->addUnsignedColumnIfMissing('purchase_orders', 'department_id');
        $this->addUnsignedColumnIfMissing('purchase_orders', 'cost_center_id');

        $this->addUnsignedColumnIfMissing('sales', 'department_id');
        $this->addUnsignedColumnIfMissing('sales', 'cost_center_id');

        $this->addUnsignedColumnIfMissing('production_orders', 'department_id');
        $this->addUnsignedColumnIfMissing('production_orders', 'cost_center_id');
    }

    public function down(): void
    {
        if (Schema::hasTable('warehouse_locations')) {
            Schema::drop('warehouse_locations');
        }

        if (Schema::hasTable('master_data_records')) {
            Schema::drop('master_data_records');
        }

        if (Schema::hasTable('branches')) {
            Schema::drop('branches');
        }
    }

    private function addUnsignedColumnIfMissing(string $tableName, string $columnName): void
    {
        if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName): void {
            $table->unsignedBigInteger($columnName)->nullable();
            $table->index($columnName, 'ix_'.$table->getTable().'_'.$columnName);
        });
    }
};
