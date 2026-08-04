<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bom_items') && Schema::hasColumn('bom_items', 'scrap_factor')) {
            Schema::table('bom_items', static function (Blueprint $table): void {
                $table->dropColumn('scrap_factor');
            });
        }

        if (Schema::hasTable('production_order_bom_item_snapshots') && Schema::hasColumn('production_order_bom_item_snapshots', 'scrap_factor')) {
            Schema::table('production_order_bom_item_snapshots', static function (Blueprint $table): void {
                $table->dropColumn('scrap_factor');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bom_items') && ! Schema::hasColumn('bom_items', 'scrap_factor')) {
            Schema::table('bom_items', static function (Blueprint $table): void {
                $table->decimal('scrap_factor', 8, 4)->default(0)->after('quantity_per');
            });
        }

        if (Schema::hasTable('production_order_bom_item_snapshots') && ! Schema::hasColumn('production_order_bom_item_snapshots', 'scrap_factor')) {
            Schema::table('production_order_bom_item_snapshots', static function (Blueprint $table): void {
                $table->decimal('scrap_factor', 8, 4)->default(0)->after('quantity_per');
            });
        }
    }
};
