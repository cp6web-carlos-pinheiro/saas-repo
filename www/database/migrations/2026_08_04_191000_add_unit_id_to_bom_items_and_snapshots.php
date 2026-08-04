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

        if (Schema::hasTable('bom_items') && Schema::hasTable('units')) {
            $items = DB::table('bom_items')
                ->select(['id', 'company_id', 'uom'])
                ->whereNull('unit_id')
                ->whereNotNull('uom')
                ->get();

            foreach ($items as $item) {
                $uom = mb_strtoupper(trim((string) $item->uom));

                if ($uom === '') {
                    continue;
                }

                $unitId = DB::table('units')
                    ->where('company_id', (int) $item->company_id)
                    ->whereRaw('UPPER(code) = ?', [$uom])
                    ->value('id');

                if ($unitId !== null) {
                    DB::table('bom_items')
                        ->where('id', (int) $item->id)
                        ->update(['unit_id' => (int) $unitId]);
                }
            }
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
