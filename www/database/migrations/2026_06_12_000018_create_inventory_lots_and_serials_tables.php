<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_lots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('lot_number', 80);
            $table->date('manufactured_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE | QUARANTINED | CONSUMED | OBSOLETE
            $table->unsignedBigInteger('source_movement_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'warehouse_id', 'product_id', 'lot_number'], 'uq_inventory_lot_scope');
            $table->index(['company_id', 'product_id', 'expires_at'], 'ix_inventory_lot_product_expiry');
            $table->index(['company_id', 'status'], 'ix_inventory_lot_status');
        });

        Schema::create('inventory_serials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('inventory_lot_id')->nullable()->constrained('inventory_lots')->nullOnDelete();
            $table->string('serial_number', 120);
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE | SHIPPED | SCRAPPED | CONSUMED
            $table->unsignedBigInteger('source_movement_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'product_id', 'serial_number'], 'uq_inventory_serial_scope');
            $table->index(['company_id', 'warehouse_id', 'product_id'], 'ix_inventory_serial_scope');
            $table->index(['company_id', 'status'], 'ix_inventory_serial_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_serials');
        Schema::dropIfExists('inventory_lots');
    }
};
