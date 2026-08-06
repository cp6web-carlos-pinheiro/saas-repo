<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_resources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->foreignId('work_center_id')->constrained('work_centers')->restrictOnDelete();
            $table->string('code', 80);
            $table->string('name', 150);
            $table->string('resource_type', 30); // MACHINE | EQUIPMENT | TOOL | LINE | OUTSOURCED
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE | INACTIVE | MAINTENANCE | BLOCKED | DECOMMISSIONED
            $table->decimal('capacity_per_day', 10, 2)->nullable();
            $table->decimal('efficiency_factor', 7, 3)->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code'], 'uq_production_resource_company_code');
            $table->index(['company_id', 'work_center_id', 'status'], 'ix_production_resource_center_status');
            $table->index(['company_id', 'plant_id'], 'ix_production_resource_plant');
        });

        Schema::create('work_center_hour_rates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('work_center_id')->constrained('work_centers')->restrictOnDelete();
            $table->decimal('hourly_rate', 18, 6);
            $table->string('currency', 3)->default('BRL');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE | OBSOLETE
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('change_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'work_center_id', 'effective_from'], 'ix_hour_rate_center_effective');
            $table->index(['company_id', 'status'], 'ix_hour_rate_status');
        });

        Schema::create('routing_operation_standard_times', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('routing_operation_id')->constrained('routing_operations')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('status', 20)->default('DRAFT'); // DRAFT | APPROVED | OBSOLETE
            $table->string('time_basis', 20)->default('PER_PROCESS'); // PER_PROCESS | PER_UNIT | PER_BATCH
            $table->string('setup_scope', 20)->default('ROUTING'); // ROUTING | OPERATION
            $table->decimal('base_quantity', 18, 6)->default(1);
            $table->decimal('setup_time_minutes', 10, 2)->default(0);
            $table->decimal('runtime_minutes', 10, 2)->default(0);
            $table->decimal('queue_time_minutes', 10, 2)->default(0);
            $table->decimal('move_time_minutes', 10, 2)->default(0);
            $table->decimal('efficiency_factor', 7, 3)->default(100);
            $table->decimal('yield_factor', 7, 4)->default(100);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('change_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'routing_operation_id', 'version_number'], 'uq_standard_time_operation_version');
            $table->index(['company_id', 'routing_operation_id', 'status'], 'ix_standard_time_operation_status');
            $table->index(['company_id', 'effective_from', 'effective_to'], 'ix_standard_time_effective');
        });

        Schema::table('routing_operation_snapshots', function (Blueprint $table): void {
            $table->unsignedBigInteger('standard_time_id')->nullable()->after('routing_version_id');
            $table->unsignedInteger('standard_time_version')->nullable()->after('standard_time_id');
            $table->index(['company_id', 'standard_time_id'], 'ix_routing_snapshot_standard_time');
        });

        Schema::table('production_order_routing_operation_snapshots', function (Blueprint $table): void {
            $table->unsignedBigInteger('standard_time_id')->nullable()->after('routing_version_id');
            $table->unsignedInteger('standard_time_version')->nullable()->after('standard_time_id');
            $table->index(['company_id', 'standard_time_id'], 'ix_prod_order_snapshot_standard_time');
        });
    }

    public function down(): void
    {
        Schema::table('production_order_routing_operation_snapshots', function (Blueprint $table): void {
            $table->dropIndex('ix_prod_order_snapshot_standard_time');
            $table->dropColumn(['standard_time_id', 'standard_time_version']);
        });

        Schema::table('routing_operation_snapshots', function (Blueprint $table): void {
            $table->dropIndex('ix_routing_snapshot_standard_time');
            $table->dropColumn(['standard_time_id', 'standard_time_version']);
        });

        Schema::dropIfExists('routing_operation_standard_times');
        Schema::dropIfExists('work_center_hour_rates');
        Schema::dropIfExists('production_resources');
    }
};
