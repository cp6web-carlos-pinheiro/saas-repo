<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_balances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('qty_available', 18, 6)->default(0);
            $table->decimal('qty_reserved', 18, 6)->default(0);
            $table->decimal('qty_in_transit', 18, 6)->default(0);
            $table->decimal('qty_inspection', 18, 6)->default(0);
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'warehouse_id', 'product_id'], 'uq_inventory_balance_company_wh_product');
            $table->index(['company_id', 'warehouse_id'], 'ix_inventory_balance_wh');
            $table->index(['company_id', 'product_id'], 'ix_inventory_balance_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_balances');
    }
};
