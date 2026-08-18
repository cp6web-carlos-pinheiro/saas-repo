<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_order_material_consumptions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id', 'fk_po_mat_con_company')->references('id')->on('companies')->cascadeOnDelete();
            $table->unsignedBigInteger('production_order_id');
            $table->foreign('production_order_id', 'fk_po_mat_con_order')->references('id')->on('production_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('lot_number', 80)->nullable();
            $table->decimal('quantity_consumed', 18, 6);
            $table->decimal('quantity_scrapped', 18, 6)->default(0);
            $table->unsignedBigInteger('ledger_movement_id')->nullable(); // FK into stock_ledger_movements
            $table->string('reference_bom_component_id', 64)->nullable(); // snapshot component key
            $table->timestamp('consumed_at');
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'production_order_id'], 'ix_po_mat_consumption_order');
            $table->index(['company_id', 'product_id'], 'ix_po_mat_consumption_product');
            $table->index(['company_id', 'production_order_id', 'product_id'], 'ix_po_mat_consumption_order_product');
            $table->index(['company_id', 'lot_number'], 'ix_po_mat_consumption_lot');
            $table->index(['company_id', 'consumed_at'], 'ix_po_mat_consumption_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_material_consumptions');
    }
};
