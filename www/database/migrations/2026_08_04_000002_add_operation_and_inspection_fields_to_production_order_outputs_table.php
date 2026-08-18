<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_order_outputs', function (Blueprint $table): void {
            if (! Schema::hasColumn('production_order_outputs', 'operation_no')) {
                $table->unsignedInteger('operation_no')->nullable()->after('quantity_scrapped');
            }

            if (! Schema::hasColumn('production_order_outputs', 'work_center_id')) {
                $table->foreignId('work_center_id')->nullable()->constrained('work_centers')->nullOnDelete()->after('operation_no');
            }

            if (! Schema::hasColumn('production_order_outputs', 'setup_time_minutes')) {
                $table->decimal('setup_time_minutes', 10, 2)->default(0)->after('work_center_id');
            }

            if (! Schema::hasColumn('production_order_outputs', 'process_time_minutes')) {
                $table->decimal('process_time_minutes', 10, 2)->default(0)->after('setup_time_minutes');
            }

            if (! Schema::hasColumn('production_order_outputs', 'inspection_status')) {
                $table->string('inspection_status', 20)->default('APPROVED')->after('process_time_minutes');
            }

            if (! Schema::hasColumn('production_order_outputs', 'inspected_at')) {
                $table->timestamp('inspected_at')->nullable()->after('inspection_status');
            }

            if (! Schema::hasColumn('production_order_outputs', 'inspection_notes')) {
                $table->text('inspection_notes')->nullable()->after('inspected_at');
            }

            $table->index(['company_id', 'production_order_id', 'operation_no'], 'ix_prod_order_output_operation');
            $table->index(['company_id', 'inspection_status'], 'ix_prod_order_output_inspection');
        });
    }

    public function down(): void
    {
        Schema::table('production_order_outputs', function (Blueprint $table): void {
            if (Schema::hasColumn('production_order_outputs', 'inspection_notes')) {
                $table->dropColumn('inspection_notes');
            }

            if (Schema::hasColumn('production_order_outputs', 'inspected_at')) {
                $table->dropColumn('inspected_at');
            }

            if (Schema::hasColumn('production_order_outputs', 'inspection_status')) {
                $table->dropColumn('inspection_status');
            }

            if (Schema::hasColumn('production_order_outputs', 'process_time_minutes')) {
                $table->dropColumn('process_time_minutes');
            }

            if (Schema::hasColumn('production_order_outputs', 'setup_time_minutes')) {
                $table->dropColumn('setup_time_minutes');
            }

            if (Schema::hasColumn('production_order_outputs', 'work_center_id')) {
                $table->dropConstrainedForeignId('work_center_id');
            }

            if (Schema::hasColumn('production_order_outputs', 'operation_no')) {
                $table->dropColumn('operation_no');
            }
        });
    }
};
