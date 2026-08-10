<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_order_bom_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->unsignedBigInteger('production_order_id');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('production_order_quantity', 18, 6)->default(1);
            $table->date('reference_date');
            $table->foreignId('source_bom_header_id')->nullable()->constrained('bom_headers')->nullOnDelete();
            $table->unsignedInteger('source_bom_version_number');
            $table->string('snapshot_hash', 64);
            $table->boolean('has_cycle')->default(false);
            // DATETIME avoids legacy MySQL's implicit zero-date default for required TIMESTAMP columns.
            $table->dateTime('frozen_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'production_order_id'], 'uq_prod_order_bom_snapshot_company_order');
            $table->index(['company_id', 'product_id'], 'ix_prod_order_bom_snapshot_product');
            $table->index(['company_id', 'source_bom_header_id'], 'ix_prod_order_bom_snapshot_source_header');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_bom_snapshots');
    }
};
