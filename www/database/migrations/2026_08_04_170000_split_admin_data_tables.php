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
        if (! Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('code', 20);
                $table->string('name', 180);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'code'], 'uq_units_company_code');
                $table->unique(['company_id', 'name'], 'uq_units_company_name');
                $table->index(['company_id', 'is_active'], 'ix_units_company_active');
            });
        }

        if (! Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('code', 40);
                $table->string('name', 180);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'code'], 'uq_prod_cat_company_code');
                $table->unique(['company_id', 'name'], 'uq_prod_cat_company_name');
                $table->index(['company_id', 'is_active'], 'ix_prod_cat_company_active');
            });
        }

        if (! Schema::hasTable('product_brands')) {
            Schema::create('product_brands', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('code', 40);
                $table->string('name', 180);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'code'], 'uq_prod_brand_company_code');
                $table->unique(['company_id', 'name'], 'uq_prod_brand_company_name');
                $table->index(['company_id', 'is_active'], 'ix_prod_brand_company_active');
            });
        }

        $this->migrateLegacyDomain('units', 'units');
        $this->migrateLegacyDomain('categories', 'product_categories');
        $this->migrateLegacyDomain('brands', 'product_brands');
    }

    public function down(): void
    {
        if (Schema::hasTable('product_brands')) {
            Schema::drop('product_brands');
        }

        if (Schema::hasTable('product_categories')) {
            Schema::drop('product_categories');
        }

        if (Schema::hasTable('units')) {
            Schema::drop('units');
        }
    }

    private function migrateLegacyDomain(string $domain, string $targetTable): void
    {
        if (! Schema::hasTable('master_data_records') || ! Schema::hasTable($targetTable)) {
            return;
        }

        if (DB::table($targetTable)->exists()) {
            return;
        }

        $legacyRows = DB::table('master_data_records')
            ->where('domain', $domain)
            ->orderBy('id')
            ->get();

        foreach ($legacyRows as $row) {
            DB::table($targetTable)->insert([
                'id' => (int) $row->id,
                'company_id' => (int) $row->company_id,
                'code' => (string) $row->code,
                'name' => (string) $row->name,
                'description' => $row->description,
                'is_active' => (bool) $row->is_active,
                'created_by' => $row->created_by,
                'updated_by' => $row->updated_by,
                'metadata' => $row->metadata,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }
};
