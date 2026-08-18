<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('bom_header_id')->constrained('bom_headers')->cascadeOnDelete();
            $table->foreignId('component_product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('line_no')->default(1);
            $table->decimal('quantity_per', 18, 6);
            $table->decimal('scrap_factor', 8, 4)->default(0);
            $table->string('uom', 20)->nullable();
            $table->timestamps();

            $table->unique(['bom_header_id', 'line_no'], 'uq_bom_item_header_line');
            $table->index(['company_id', 'bom_header_id'], 'ix_bom_item_header');
            $table->index(['company_id', 'component_product_id'], 'ix_bom_item_component');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};
