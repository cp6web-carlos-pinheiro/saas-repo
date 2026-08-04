<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bom_items') && ! Schema::hasColumn('bom_items', 'unit_id')) {
            Schema::table('bom_items', static function (Blueprint $table): void {
                $table->unsignedBigInteger('unit_id')->nullable()->after('component_product_id');
            });
        }

        if (Schema::hasTable('production_order_bom_item_snapshots') && ! Schema::hasColumn('production_order_bom_item_snapshots', 'unit_id')) {
            Schema::table('production_order_bom_item_snapshots', static function (Blueprint $table): void {
                $table->unsignedBigInteger('unit_id')->nullable()->after('component_product_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bom_items') && Schema::hasColumn('bom_items', 'unit_id')) {
            Schema::table('bom_items', static function (Blueprint $table): void {
                $table->dropColumn('unit_id');
            });
        }

        if (Schema::hasTable('production_order_bom_item_snapshots') && Schema::hasColumn('production_order_bom_item_snapshots', 'unit_id')) {
            Schema::table('production_order_bom_item_snapshots', static function (Blueprint $table): void {
                $table->dropColumn('unit_id');
            });
        }
    }
};
