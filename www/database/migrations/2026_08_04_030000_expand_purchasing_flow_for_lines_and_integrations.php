<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchase_quotation_lines')) {
            Schema::create('purchase_quotation_lines', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('purchase_quotation_id')->constrained('purchase_quotations')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('purchase_requisition_line_id')->nullable()->constrained('purchase_requisition_lines')->nullOnDelete();
                $table->decimal('quantity', 14, 6)->default(0);
                $table->decimal('unit_price', 14, 6)->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'purchase_quotation_id'], 'ix_purchase_quotation_line_header');
            });
        }

        if (! Schema::hasTable('purchase_receipt_lines')) {
            Schema::create('purchase_receipt_lines', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('purchase_receipt_id')->constrained('purchase_receipts')->cascadeOnDelete();
                $table->foreignId('purchase_order_line_id')->nullable()->constrained('purchase_order_lines')->nullOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->decimal('quantity_received', 14, 6)->default(0);
                $table->string('lot_number', 80)->nullable();
                $table->unsignedBigInteger('stock_ledger_movement_id')->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'purchase_receipt_id'], 'ix_purchase_receipt_line_header');
                $table->index(['company_id', 'stock_ledger_movement_id'], 'ix_purchase_receipt_line_ledger');
            });
        }

        if (! Schema::hasTable('purchase_fiscal_entry_postings')) {
            Schema::create('purchase_fiscal_entry_postings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('purchase_fiscal_entry_id')->constrained('purchase_fiscal_entries')->cascadeOnDelete();
                $table->string('status', 20)->default('POSTED');
                $table->string('financial_reference', 80)->nullable();
                $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('posted_at')->nullable();
                $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reversed_at')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'purchase_fiscal_entry_id'], 'uq_purchase_fiscal_entry_posting_header');
            });
        }

        Schema::table('purchase_requisitions', function (Blueprint $table): void {
            if (! Schema::hasColumn('purchase_requisitions', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('purchase_requisitions', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('purchase_orders', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('purchase_orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
        });

        Schema::table('purchase_quotations', function (Blueprint $table): void {
            if (! Schema::hasColumn('purchase_quotations', 'received_by')) {
                $table->foreignId('received_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('purchase_quotations', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('received_by');
            }

            if (! Schema::hasColumn('purchase_quotations', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('received_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('purchase_quotations', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            if (! Schema::hasColumn('purchase_quotations', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('purchase_quotations', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
        });

        Schema::table('purchase_receipts', function (Blueprint $table): void {
            if (! Schema::hasColumn('purchase_receipts', 'posted_by')) {
                $table->foreignId('posted_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('purchase_receipts', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('posted_by');
            }

            if (! Schema::hasColumn('purchase_receipts', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('posted_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('purchase_receipts', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
        });

        Schema::table('purchase_fiscal_entries', function (Blueprint $table): void {
            if (! Schema::hasColumn('purchase_fiscal_entries', 'posted_by')) {
                $table->foreignId('posted_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('purchase_fiscal_entries', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('posted_by');
            }

            if (! Schema::hasColumn('purchase_fiscal_entries', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('posted_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('purchase_fiscal_entries', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }

            if (! Schema::hasColumn('purchase_fiscal_entries', 'financial_reference')) {
                $table->string('financial_reference', 80)->nullable()->after('amount_cents');
            }

            if (! Schema::hasColumn('purchase_fiscal_entries', 'financial_posted_at')) {
                $table->timestamp('financial_posted_at')->nullable()->after('financial_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_fiscal_entry_postings');
        Schema::dropIfExists('purchase_receipt_lines');
        Schema::dropIfExists('purchase_quotation_lines');
    }
};
