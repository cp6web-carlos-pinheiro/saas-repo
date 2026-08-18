<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('plans', 'amount_cents')) {
            Schema::table('plans', function (Blueprint $table): void {
                $table->unsignedInteger('amount_cents')->default(0)->after('billing_cycle_label');
            });
        }

        DB::table('plans')->whereNull('amount_cents')->update(['amount_cents' => 0]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('plans', 'amount_cents')) {
            Schema::table('plans', function (Blueprint $table): void {
                $table->dropColumn('amount_cents');
            });
        }
    }
};
