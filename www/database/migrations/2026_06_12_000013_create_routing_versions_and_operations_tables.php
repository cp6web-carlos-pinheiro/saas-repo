<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routing_versions', function (Blueprint $table): void {
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

            $table->unique(['company_id', 'product_id', 'version_number'], 'uq_routing_version_company_product_version');
            $table->index(['company_id', 'product_id', 'status'], 'ix_routing_version_status');
            $table->index(['company_id', 'effective_from', 'effective_to'], 'ix_routing_version_effective');
        });

        Schema::create('routing_operations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('routing_version_id')->constrained('routing_versions')->cascadeOnDelete();
            $table->foreignId('work_center_id')->constrained('work_centers')->restrictOnDelete();
            $table->unsignedInteger('operation_no');
            $table->string('operation_code', 50);
            $table->string('operation_name', 150);
            $table->unsignedInteger('sequence');
            $table->decimal('setup_time_minutes', 10, 2)->default(0);
            $table->decimal('runtime_minutes', 10, 2)->default(0);
            $table->decimal('queue_time_minutes', 10, 2)->default(0);
            $table->decimal('move_time_minutes', 10, 2)->default(0);
            $table->boolean('is_outsourced')->default(false);
            $table->timestamps();

            $table->unique(['routing_version_id', 'operation_no'], 'uq_routing_operation_version_op');
            $table->unique(['routing_version_id', 'sequence'], 'uq_routing_operation_version_sequence');
            $table->index(['company_id', 'routing_version_id'], 'ix_routing_operation_version');
            $table->index(['company_id', 'work_center_id'], 'ix_routing_operation_work_center');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_operations');
        Schema::dropIfExists('routing_versions');
    }
};
