<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('routing_version_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('routing_version_id')->constrained('routing_versions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('status', 20);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('description', 255)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            // DATETIME avoids legacy MySQL's implicit zero-date default for required TIMESTAMP columns.
            $table->dateTime('frozen_at');
            $table->string('snapshot_hash', 64);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'routing_version_id'], 'uq_routing_version_snapshot_company_version');
            $table->index(['company_id', 'product_id'], 'ix_routing_version_snapshot_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_version_snapshots');
    }
};
