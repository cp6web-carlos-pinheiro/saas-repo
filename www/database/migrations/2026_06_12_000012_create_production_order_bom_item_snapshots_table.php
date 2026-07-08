<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_order_bom_item_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('production_order_bom_snapshot_id')
                ->constrained('production_order_bom_snapshots', 'id', 'fk_pob_item_snapshot_parent')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('source_bom_header_id');
            $table->unsignedInteger('source_bom_version_number');
            $table->unsignedBigInteger('parent_product_id');
            $table->unsignedBigInteger('component_product_id');
            $table->unsignedInteger('line_no');
            $table->unsignedInteger('level');
            $table->decimal('quantity_per', 18, 6);
            $table->decimal('scrap_factor', 8, 4)->default(0);
            $table->decimal('quantity_required', 18, 6);
            $table->decimal('quantity_accumulated', 24, 6);
            $table->string('path', 4000);
            $table->boolean('is_cycle')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'production_order_bom_snapshot_id'], 'ix_prod_order_bom_item_snapshot_header');
            $table->index(['company_id', 'component_product_id'], 'ix_prod_order_bom_item_snapshot_component');
            $table->unique(
                [
                    'production_order_bom_snapshot_id',
                    'level',
                    'parent_product_id',
                    'component_product_id',
                    'line_no',
                ],
                'uq_prod_order_bom_item_snapshot_node'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_bom_item_snapshots');
    }
};
