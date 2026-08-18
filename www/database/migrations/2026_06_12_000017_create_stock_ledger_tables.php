<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ledger_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('movement_type', 30);
            $table->string('source_bucket', 20)->nullable();
            $table->string('target_bucket', 20)->nullable();
            $table->decimal('quantity', 18, 6);
            $table->string('allocation_strategy', 20)->nullable();
            $table->string('lot_number', 80)->nullable();
            $table->date('expires_at')->nullable();
            $table->string('reference_type', 120)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('movement_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'warehouse_id', 'product_id'], 'ix_stock_ledger_movement_scope');
            $table->index(['company_id', 'movement_type', 'movement_at'], 'ix_stock_ledger_movement_type_date');
            $table->index(['company_id', 'reference_type', 'reference_id'], 'ix_stock_ledger_movement_reference');
        });

        Schema::create('stock_ledger_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('issue_movement_id')->constrained('stock_ledger_movements')->cascadeOnDelete();
            $table->foreignId('receipt_movement_id')->constrained('stock_ledger_movements')->cascadeOnDelete();
            $table->decimal('quantity', 18, 6);
            $table->unsignedInteger('sequence_no');
            $table->timestamps();

            $table->unique(['issue_movement_id', 'receipt_movement_id', 'sequence_no'], 'uq_stock_ledger_allocation_line');
            $table->index(['company_id', 'issue_movement_id'], 'ix_stock_ledger_allocation_issue');
            $table->index(['company_id', 'receipt_movement_id'], 'ix_stock_ledger_allocation_receipt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledger_allocations');
        Schema::dropIfExists('stock_ledger_movements');
    }
};
