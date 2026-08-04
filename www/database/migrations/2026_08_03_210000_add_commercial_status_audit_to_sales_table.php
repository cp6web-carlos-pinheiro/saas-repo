<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales', 'confirmed_by')) {
                $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            }

            if (! Schema::hasColumn('sales', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
            }

            if (! Schema::hasColumn('sales', 'canceled_by')) {
                $table->foreignId('canceled_by')->nullable()->constrained('users')->nullOnDelete()->after('confirmed_at');
            }

            if (! Schema::hasColumn('sales', 'canceled_at')) {
                $table->timestamp('canceled_at')->nullable()->after('canceled_by');
            }

            if (! Schema::hasColumn('sales', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable()->after('canceled_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (Schema::hasColumn('sales', 'cancel_reason')) {
                $table->dropColumn('cancel_reason');
            }

            foreach (['canceled_by', 'confirmed_by'] as $foreignKeyColumn) {
                if (Schema::hasColumn('sales', $foreignKeyColumn)) {
                    try {
                        $table->dropConstrainedForeignId($foreignKeyColumn);
                    } catch (Throwable) {
                        $table->dropColumn($foreignKeyColumn);
                    }
                }
            }

            foreach (['canceled_at', 'confirmed_at'] as $timestampColumn) {
                if (Schema::hasColumn('sales', $timestampColumn)) {
                    $table->dropColumn($timestampColumn);
                }
            }
        });
    }
};