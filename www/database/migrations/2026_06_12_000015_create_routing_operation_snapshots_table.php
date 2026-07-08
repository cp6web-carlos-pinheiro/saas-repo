<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('routing_operation_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('routing_version_snapshot_id')
                ->constrained('routing_version_snapshots')
                ->cascadeOnDelete();
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

            $table->unique(
                ['routing_version_snapshot_id', 'operation_no'],
                'uq_routing_operation_snapshot_version_op'
            );
            $table->unique(
                ['routing_version_snapshot_id', 'sequence'],
                'uq_routing_operation_snapshot_version_sequence'
            );
            $table->index(['company_id', 'routing_version_snapshot_id'], 'ix_routing_operation_snapshot_header');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_operation_snapshots');
    }
};
