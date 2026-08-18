<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('status', 20)->default('DRAFT'); // DRAFT, APPROVED, OBSOLETE
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->string('compatibility_rule', 20)->default('NONE'); // NONE, BACKWARD, FORWARD, FULL
            $table->text('change_summary')->nullable();
            $table->json('payload');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'product_id', 'version_number'], 'uq_product_versions_company_product_number');
            $table->index(['company_id', 'product_id', 'status'], 'ix_product_versions_company_product_status');
            $table->index(['company_id', 'product_id', 'effective_from', 'effective_to'], 'ix_product_versions_effective_window');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_versions');
    }
};
