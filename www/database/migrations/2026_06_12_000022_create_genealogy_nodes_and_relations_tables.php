<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('genealogy_nodes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('node_type', 40); // LOT | SERIAL | PRODUCTION_ORDER | MATERIAL
            $table->unsignedBigInteger('source_id');
            $table->string('source_reference', 120)->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'node_type', 'source_id'], 'uq_genealogy_node_source');
            $table->index(['company_id', 'node_type']);
            $table->index(['company_id', 'product_id']);
        });

        Schema::create('genealogy_relations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('parent_node_id')->constrained('genealogy_nodes')->cascadeOnDelete();
            $table->foreignId('child_node_id')->constrained('genealogy_nodes')->cascadeOnDelete();
            $table->string('relation_type', 40); // CONSUMES | PRODUCES | DERIVES | MOVES_TO
            $table->decimal('quantity', 18, 6)->nullable();
            $table->string('uom', 20)->nullable();
            $table->foreignId('production_order_id')->nullable()->constrained('production_orders')->nullOnDelete();
            $table->foreignId('stock_movement_id')->nullable()->constrained('stock_ledger_movements')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'relation_type']);
            $table->index(['company_id', 'production_order_id']);
            $table->index(['company_id', 'stock_movement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_relations');
        Schema::dropIfExists('genealogy_nodes');
    }
};