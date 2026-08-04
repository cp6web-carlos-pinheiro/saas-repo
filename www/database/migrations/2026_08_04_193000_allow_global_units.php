<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('units') && Schema::hasColumn('units', 'company_id')) {
            Schema::table('units', static function (Blueprint $table): void {
                $table->unsignedBigInteger('company_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('units') && Schema::hasColumn('units', 'company_id')) {
            Schema::table('units', static function (Blueprint $table): void {
                $table->unsignedBigInteger('company_id')->nullable(false)->change();
            });
        }
    }
};
