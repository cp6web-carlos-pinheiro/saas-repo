<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_orders', function (Blueprint $table): void {
            $table->index(['company_id', 'source_reference_type', 'source_reference_id', 'id'], 'ix_po_sale_reference');
        });

        Schema::table('purchase_requisitions', function (Blueprint $table): void {
            $table->index(['company_id', 'source_reference_type', 'source_reference_id', 'id'], 'ix_pr_sale_reference');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->index(['company_id', 'purchase_requisition_id', 'id'], 'ix_purchase_order_requisition');
        });

        Schema::table('purchase_order_lines', function (Blueprint $table): void {
            $table->index(['company_id', 'product_id', 'id'], 'ix_po_line_product_latest');
        });

        Schema::table('supplier_products', function (Blueprint $table): void {
            $table->index(['company_id', 'product_id', 'is_active', 'is_preferred', 'id'], 'ix_supplier_product_active_preferred');
        });

        Schema::table('work_center_hour_rates', function (Blueprint $table): void {
            $table->index(['company_id', 'work_center_id', 'status', 'effective_from'], 'ix_hour_rate_active_effective');
        });

        Schema::table('inventory_reservations', function (Blueprint $table): void {
            $table->index(['company_id', 'reference_type', 'reference_id', 'status', 'reserved_at'], 'ix_inventory_reservation_reference');
        });

        Schema::table('inventory_balances', function (Blueprint $table): void {
            $table->index(['company_id', 'product_id', 'qty_available'], 'ix_inventory_balance_product_available');
        });

        Schema::table('bom_headers', function (Blueprint $table): void {
            $table->index(['company_id', 'product_id', 'status', 'effective_from', 'version_number'], 'ix_bom_header_active_effective');
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index(['company_id', 'occurred_at'], 'ix_audit_log_company_history');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', fn (Blueprint $table) => $table->dropIndex('ix_audit_log_company_history'));
        Schema::table('bom_headers', fn (Blueprint $table) => $table->dropIndex('ix_bom_header_active_effective'));
        Schema::table('inventory_balances', fn (Blueprint $table) => $table->dropIndex('ix_inventory_balance_product_available'));
        Schema::table('inventory_reservations', fn (Blueprint $table) => $table->dropIndex('ix_inventory_reservation_reference'));
        Schema::table('work_center_hour_rates', fn (Blueprint $table) => $table->dropIndex('ix_hour_rate_active_effective'));
        Schema::table('supplier_products', fn (Blueprint $table) => $table->dropIndex('ix_supplier_product_active_preferred'));
        Schema::table('purchase_order_lines', fn (Blueprint $table) => $table->dropIndex('ix_po_line_product_latest'));
        Schema::table('purchase_orders', fn (Blueprint $table) => $table->dropIndex('ix_purchase_order_requisition'));
        Schema::table('purchase_requisitions', fn (Blueprint $table) => $table->dropIndex('ix_pr_sale_reference'));
        Schema::table('production_orders', fn (Blueprint $table) => $table->dropIndex('ix_po_sale_reference'));
    }
};
