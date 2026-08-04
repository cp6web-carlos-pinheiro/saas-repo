<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales', 'operational_status')) {
                $table->string('operational_status', 20)->default('PENDING')->after('status');
            }

            if (! Schema::hasColumn('sales', 'subtotal_cents')) {
                $table->unsignedBigInteger('subtotal_cents')->default(0)->after('operational_status');
            }

            if (! Schema::hasColumn('sales', 'discount_cents')) {
                $table->unsignedBigInteger('discount_cents')->default(0)->after('subtotal_cents');
            }

            if (! Schema::hasColumn('sales', 'tax_cents')) {
                $table->unsignedBigInteger('tax_cents')->default(0)->after('discount_cents');
            }
        });

        if (Schema::hasColumn('sales', 'code')) {
            Schema::table('sales', function (Blueprint $table): void {
                try {
                    $table->dropUnique('sales_company_id_code_unique');
                } catch (Throwable) {
                }

                $table->dropColumn('code');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales', 'code')) {
                $table->string('code', 50)->nullable()->after('company_id');
            }

            if (Schema::hasColumn('sales', 'tax_cents')) {
                $table->dropColumn('tax_cents');
            }

            if (Schema::hasColumn('sales', 'discount_cents')) {
                $table->dropColumn('discount_cents');
            }

            if (Schema::hasColumn('sales', 'subtotal_cents')) {
                $table->dropColumn('subtotal_cents');
            }

            if (Schema::hasColumn('sales', 'operational_status')) {
                $table->dropColumn('operational_status');
            }
        });
    }
};