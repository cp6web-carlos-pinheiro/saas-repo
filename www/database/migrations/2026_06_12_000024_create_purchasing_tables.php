<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 180);
            $table->string('email', 180)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->unsignedInteger('default_lead_time_days')->default(0);
            $table->string('payment_terms', 80)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('supplier_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('supplier_sku', 80)->nullable();
            $table->decimal('moq', 14, 6)->default(1);
            $table->unsignedInteger('lead_time_days')->default(0);
            $table->decimal('unit_price', 14, 6)->nullable();
            $table->boolean('is_preferred')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'supplier_id', 'product_id'], 'uq_supplier_product');
            $table->index(['company_id', 'product_id', 'is_preferred']);
        });

        Schema::create('purchase_requisitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('requisition_number', 60);
            $table->string('status', 20)->default('DRAFT');
            $table->date('required_date')->nullable();
            $table->string('source_type', 80)->nullable();
            $table->unsignedBigInteger('source_reference_id')->nullable();
            $table->string('source_reference_type', 120)->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'requisition_number'], 'uq_requisition_number_company');
            $table->index(['company_id', 'status']);
        });

        Schema::create('purchase_requisition_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('purchase_requisition_id')->constrained('purchase_requisitions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('suggested_quantity', 14, 6)->default(0);
            $table->decimal('requested_quantity', 14, 6)->default(0);
            $table->decimal('moq_applied', 14, 6)->default(1);
            $table->unsignedInteger('lead_time_days')->default(0);
            $table->date('need_by_date');
            $table->date('order_date');
            $table->string('status', 20)->default('OPEN');
            $table->string('source_requirement_key', 180)->nullable();
            $table->date('mrp_reference_date')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'purchase_requisition_id'], 'idx_requisition_lines_requisition');
            $table->index(['company_id', 'supplier_id', 'status'], 'idx_requisition_lines_supplier_status');
        });

        Schema::create('purchase_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('purchase_order_number', 60);
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('purchase_requisition_id')->nullable()->constrained('purchase_requisitions')->nullOnDelete();
            $table->string('status', 20)->default('DRAFT');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'purchase_order_number'], 'uq_purchase_order_number_company');
            $table->index(['company_id', 'supplier_id', 'status']);
        });

        Schema::create('purchase_order_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('purchase_requisition_line_id')->nullable()->constrained('purchase_requisition_lines')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->decimal('quantity_ordered', 14, 6)->default(0);
            $table->decimal('quantity_received', 14, 6)->default(0);
            $table->decimal('unit_price', 14, 6)->nullable();
            $table->date('need_by_date')->nullable();
            $table->date('promised_date')->nullable();
            $table->string('status', 20)->default('OPEN');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'purchase_order_id'], 'idx_po_lines_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_lines');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchase_requisition_lines');
        Schema::dropIfExists('purchase_requisitions');
        Schema::dropIfExists('supplier_products');
        Schema::dropIfExists('suppliers');
    }
};
