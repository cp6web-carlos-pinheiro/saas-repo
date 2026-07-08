<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->unsignedBigInteger('bom_header_id')->nullable();
            $table->unsignedInteger('bom_version_number')->nullable();
            $table->unsignedBigInteger('routing_version_id')->nullable();
            $table->unsignedInteger('routing_version_number')->nullable();
            $table->string('source_type', 20); // MANUAL | MRP
            $table->unsignedBigInteger('source_reference_id')->nullable();
            $table->string('source_reference_type', 120)->nullable();
            $table->string('order_number', 50);
            $table->string('status', 20)->default('DRAFT'); // DRAFT | RELEASED | IN_PROGRESS | PARTIALLY_COMPLETED | COMPLETED | CANCELLED
            $table->decimal('quantity_planned', 18, 6);
            $table->decimal('quantity_produced', 18, 6)->default(0);
            $table->decimal('quantity_scrapped', 18, 6)->default(0);
            $table->date('scheduled_start_date')->nullable();
            $table->date('scheduled_end_date')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'order_number'], 'uq_production_order_company_number');
            $table->index(['company_id', 'product_id'], 'ix_production_order_product');
            $table->index(['company_id', 'warehouse_id'], 'ix_production_order_warehouse');
            $table->index(['company_id', 'status'], 'ix_production_order_status');
            $table->index(['company_id', 'source_type', 'source_reference_id'], 'ix_production_order_source');
        });

        Schema::create('production_order_outputs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->decimal('quantity_completed', 18, 6);
            $table->decimal('quantity_scrapped', 18, 6)->default(0);
            $table->string('lot_number', 80)->nullable();
            $table->timestamp('produced_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'production_order_id', 'produced_at'], 'ix_production_order_output_order_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_outputs');
        Schema::dropIfExists('production_orders');
    }
};
