<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_quotations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('quotation_number', 60);
            $table->foreignId('purchase_requisition_id')->nullable()->constrained('purchase_requisitions')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->integer('amount_cents')->default(0);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'quotation_number'], 'uq_purchase_quotation_number_company');
            $table->index(['company_id', 'status'], 'ix_purchase_quotation_company_status');
        });

        Schema::create('purchase_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('receipt_number', 60);
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('receipt_date');
            $table->string('status', 20)->default('DRAFT');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'receipt_number'], 'uq_purchase_receipt_number_company');
            $table->index(['company_id', 'status'], 'ix_purchase_receipt_company_status');
        });

        Schema::create('purchase_fiscal_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('entry_number', 60);
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('document_number', 80)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('entry_date');
            $table->string('status', 20)->default('DRAFT');
            $table->integer('amount_cents')->default(0);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'entry_number'], 'uq_purchase_fiscal_entry_number_company');
            $table->index(['company_id', 'status'], 'ix_purchase_fiscal_entry_company_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_fiscal_entries');
        Schema::dropIfExists('purchase_receipts');
        Schema::dropIfExists('purchase_quotations');
    }
};
