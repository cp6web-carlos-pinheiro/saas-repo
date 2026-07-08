<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bom_headers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('status', 20)->default('DRAFT'); // DRAFT | APPROVED | OBSOLETE
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->string('description', 255)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'product_id', 'version_number'], 'uq_bom_header_company_product_version');
            $table->index(['company_id', 'product_id', 'status'], 'ix_bom_header_status');
            $table->index(['company_id', 'effective_from', 'effective_to'], 'ix_bom_header_effective');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_headers');
    }
};
