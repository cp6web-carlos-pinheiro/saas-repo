<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales', 'picking_by')) {
                $table->foreignId('picking_by')->nullable()->constrained('users')->nullOnDelete()->after('operational_status');
            }

            if (! Schema::hasColumn('sales', 'picking_at')) {
                $table->timestamp('picking_at')->nullable()->after('picking_by');
            }

            if (! Schema::hasColumn('sales', 'invoiced_by')) {
                $table->foreignId('invoiced_by')->nullable()->constrained('users')->nullOnDelete()->after('picking_at');
            }

            if (! Schema::hasColumn('sales', 'invoiced_at')) {
                $table->timestamp('invoiced_at')->nullable()->after('invoiced_by');
            }

            if (! Schema::hasColumn('sales', 'shipped_by')) {
                $table->foreignId('shipped_by')->nullable()->constrained('users')->nullOnDelete()->after('invoiced_at');
            }

            if (! Schema::hasColumn('sales', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('shipped_by');
            }

            if (! Schema::hasColumn('sales', 'delivered_by')) {
                $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete()->after('shipped_at');
            }

            if (! Schema::hasColumn('sales', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('delivered_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            foreach (['delivered_by', 'shipped_by', 'invoiced_by', 'picking_by'] as $foreignKeyColumn) {
                if (Schema::hasColumn('sales', $foreignKeyColumn)) {
                    try {
                        $table->dropConstrainedForeignId($foreignKeyColumn);
                    } catch (Throwable) {
                        $table->dropColumn($foreignKeyColumn);
                    }
                }
            }

            foreach (['delivered_at', 'shipped_at', 'invoiced_at', 'picking_at'] as $timestampColumn) {
                if (Schema::hasColumn('sales', $timestampColumn)) {
                    $table->dropColumn($timestampColumn);
                }
            }
        });
    }
};