<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->date('sale_date');
            $table->string('status', 20)->default('DRAFT');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('canceled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('canceled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->string('operational_status', 20)->default('PENDING');
            $table->foreignId('picking_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('picking_at')->nullable();
            $table->foreignId('invoiced_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('invoiced_at')->nullable();
            $table->foreignId('shipped_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('shipped_at')->nullable();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('delivered_at')->nullable();
            $table->unsignedBigInteger('subtotal_cents')->default(0);
            $table->unsignedBigInteger('discount_cents')->default(0);
            $table->unsignedBigInteger('tax_cents')->default(0);
            $table->unsignedBigInteger('amount_cents')->default(0);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'operational_status']);
            $table->index(['company_id', 'sale_date']);
            $table->index(['company_id', 'customer_id']);
        });

        Schema::create('sale_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 14, 6)->default(0);
            $table->decimal('unit_price', 14, 6)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'sale_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_lines');
        Schema::dropIfExists('sales');
    }
};