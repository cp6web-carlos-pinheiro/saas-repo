<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master production order snapshot: ties together BOM + routing at OP creation/release
        Schema::create('production_order_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            // BOM side (FK to the already-frozen BOM snapshot)
            $table->foreignId('bom_snapshot_id')->nullable()->constrained('production_order_bom_snapshots')->nullOnDelete();
            $table->unsignedBigInteger('bom_header_id')->nullable();
            $table->unsignedInteger('bom_version_number')->nullable();
            // Routing side (FK to the approved routing version snapshot used at creation time)
            $table->unsignedBigInteger('routing_version_snapshot_id')->nullable();
            $table->unsignedBigInteger('routing_version_id')->nullable();
            $table->unsignedInteger('routing_version_number')->nullable();
            // Frozen quantities
            $table->decimal('quantity_planned', 18, 6);
            $table->decimal('quantity_scrapped_target', 18, 6)->default(0);
            // Integrity
            $table->string('snapshot_hash', 64);
            // DATETIME avoids legacy MySQL's implicit zero-date default for required TIMESTAMP columns.
            $table->dateTime('frozen_at');
            $table->foreignId('frozen_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'production_order_id'], 'uq_prod_order_snapshot_company_order');
            $table->index(['company_id', 'bom_snapshot_id'], 'ix_prod_order_snapshot_bom');
            $table->index(['company_id', 'routing_version_snapshot_id'], 'ix_prod_order_snapshot_routing');
        });

        // Immutable copy of routing operations at the time the production order was frozen
        Schema::create('production_order_routing_operation_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id', 'fk_prod_op_snap_company')->references('id')->on('companies')->cascadeOnDelete();
            $table->unsignedBigInteger('production_order_snapshot_id');
            $table->foreign('production_order_snapshot_id', 'fk_prod_op_snap_header')->references('id')->on('production_order_snapshots')->cascadeOnDelete();
            $table->unsignedBigInteger('routing_version_id');
            $table->unsignedBigInteger('work_center_id');
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

            $table->unique(['production_order_snapshot_id', 'sequence'], 'uq_prod_op_snapshot_op_sequence');
            $table->index(['company_id', 'production_order_snapshot_id'], 'ix_prod_op_snapshot_header');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_routing_operation_snapshots');
        Schema::dropIfExists('production_order_snapshots');
    }
};
