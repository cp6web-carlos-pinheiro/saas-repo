<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('sku', 80);
            $table->string('description', 255);
            $table->string('product_type', 20); // FG, WIP, RAW, CONSUMABLE
            $table->string('uom', 20);
            $table->unsignedInteger('safety_stock')->default(0);
            $table->unsignedInteger('lead_time_days')->default(0);
            $table->boolean('lot_control')->default(false);
            $table->boolean('serial_control')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'sku']);
            $table->index(['company_id', 'product_type']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
