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
        if (Schema::hasTable('genealogy_relations') && ! Schema::hasColumn('genealogy_relations', 'unit_id')) {
            Schema::table('genealogy_relations', static function (Blueprint $table): void {
                $table->unsignedBigInteger('unit_id')->nullable()->after('uom');
            });
        }

        if (! Schema::hasTable('genealogy_relations') || ! Schema::hasTable('genealogy_nodes') || ! Schema::hasTable('products')) {
            return;
        }

        $relations = DB::table('genealogy_relations as r')
            ->join('genealogy_nodes as p', 'p.id', '=', 'r.parent_node_id')
            ->leftJoin('products as pp', function ($join): void {
                $join->on('pp.id', '=', 'p.product_id')->on('pp.company_id', '=', 'r.company_id');
            })
            ->join('genealogy_nodes as c', 'c.id', '=', 'r.child_node_id')
            ->leftJoin('products as cp', function ($join): void {
                $join->on('cp.id', '=', 'c.product_id')->on('cp.company_id', '=', 'r.company_id');
            })
            ->whereNull('r.unit_id')
            ->select(['r.id', 'pp.unit_id as parent_unit_id', 'cp.unit_id as child_unit_id'])
            ->get();

        foreach ($relations as $relation) {
            $unitId = $relation->parent_unit_id ?? $relation->child_unit_id;

            if ($unitId === null) {
                continue;
            }

            DB::table('genealogy_relations')
                ->where('id', (int) $relation->id)
                ->update(['unit_id' => (int) $unitId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('genealogy_relations') && Schema::hasColumn('genealogy_relations', 'unit_id')) {
            Schema::table('genealogy_relations', static function (Blueprint $table): void {
                $table->dropColumn('unit_id');
            });
        }
    }
};
