<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_order_operations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('production_order_routing_operation_snapshot_id')->nullable()
                ->constrained(
                    'production_order_routing_operation_snapshots',
                    'id',
                    'fk_po_operation_routing_snapshot'
                )
                ->nullOnDelete();
            $table->unsignedBigInteger('routing_operation_id')->nullable();
            $table->unsignedBigInteger('standard_time_id')->nullable();
            $table->unsignedInteger('standard_time_version')->nullable();
            $table->unsignedInteger('operation_no');
            $table->string('operation_code', 50);
            $table->string('operation_name', 150);
            $table->unsignedInteger('sequence');
            $table->foreignId('work_center_id')->constrained('work_centers')->restrictOnDelete();
            $table->foreignId('production_resource_id')->nullable()->constrained('production_resources')->nullOnDelete();
            $table->string('status', 20)->default('PLANNED');
            $table->decimal('quantity_planned', 18, 6);
            $table->string('setup_scope', 20)->default('ROUTING');
            $table->decimal('setup_time_minutes', 10, 2)->default(0);
            $table->decimal('runtime_time_minutes', 10, 2)->default(0);
            $table->decimal('queue_time_minutes', 10, 2)->default(0);
            $table->decimal('move_time_minutes', 10, 2)->default(0);
            $table->decimal('productive_time_minutes', 10, 2)->default(0);
            $table->decimal('lead_time_minutes', 10, 2)->default(0);
            $table->decimal('total_time_minutes', 10, 2)->default(0);
            $table->dateTime('planned_start_at')->nullable();
            $table->dateTime('planned_end_at')->nullable();
            $table->json('calculation_metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'production_order_id', 'sequence'], 'uq_production_order_operation_sequence');
            $table->index(['company_id', 'work_center_id', 'status'], 'ix_production_order_operation_center_status');
            $table->index(['company_id', 'planned_start_at', 'planned_end_at'], 'ix_production_order_operation_window');
        });

        Schema::create('production_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('plant_id')->nullable()->constrained('plants')->nullOnDelete();
            $table->string('schedule_number', 50);
            $table->unsignedInteger('version_number')->default(1);
            $table->string('status', 20)->default('DRAFT'); // DRAFT | APPROVED | PUBLISHED | CANCELLED
            $table->date('reference_date');
            $table->string('mode', 20)->default('finite');
            $table->string('direction', 20)->default('forward');
            $table->string('sequencing_rule', 40)->default('priority_due_date');
            $table->json('parameters')->nullable();
            $table->string('source_run_key', 64)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->text('change_reason')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'schedule_number'], 'uq_production_schedule_number');
            $table->index(['company_id', 'status', 'reference_date'], 'ix_production_schedule_status_date');
        });

        Schema::create('production_schedule_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('production_schedule_id')->constrained('production_schedules')->cascadeOnDelete();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('production_order_operation_id')->constrained('production_order_operations')->cascadeOnDelete();
            $table->foreignId('work_center_id')->constrained('work_centers')->restrictOnDelete();
            $table->foreignId('production_resource_id')->nullable()->constrained('production_resources')->nullOnDelete();
            $table->dateTime('planned_start_at');
            $table->dateTime('planned_end_at');
            $table->decimal('total_time_minutes', 10, 2)->default(0);
            $table->decimal('capacity_time_minutes', 10, 2)->default(0);
            $table->decimal('lead_time_minutes', 10, 2)->default(0);
            $table->json('segments')->nullable();
            $table->string('status', 20)->default('PLANNED');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['production_schedule_id', 'production_order_operation_id'], 'uq_schedule_operation');
            $table->index(['company_id', 'work_center_id', 'planned_start_at', 'planned_end_at'], 'ix_schedule_line_capacity_window');
        });

        Schema::create('mrp_plan_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('run_key', 64);
            $table->string('status', 20)->default('COMPLETED'); // RUNNING | COMPLETED | FAILED | CANCELLED
            $table->date('reference_date');
            $table->string('planning_bucket', 20)->default('daily');
            $table->string('priority_rule', 40)->default('priority_due_date');
            $table->json('request_payload');
            $table->json('result_summary')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'run_key'], 'uq_mrp_plan_run_key');
            $table->index(['company_id', 'reference_date', 'status'], 'ix_mrp_plan_run_date_status');
        });

        Schema::create('mrp_suggestions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('mrp_plan_run_id')->constrained('mrp_plan_runs')->cascadeOnDelete();
            $table->string('suggestion_key', 180);
            $table->string('suggestion_type', 20); // PURCHASE | PRODUCTION
            $table->string('status', 30)->default('GENERATED'); // GENERATED | IN_REVIEW | APPROVED | REJECTED | CONVERTED | PARTIALLY_CONVERTED | CANCELLED
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->decimal('original_quantity', 18, 6);
            $table->decimal('approved_quantity', 18, 6)->nullable();
            $table->date('need_by_date');
            $table->date('release_date')->nullable();
            $table->unsignedInteger('priority')->default(1000);
            $table->unsignedInteger('bom_version_number')->nullable();
            $table->unsignedBigInteger('routing_version_id')->nullable();
            $table->string('source_requirement_key', 180)->nullable();
            $table->string('source_reference_type', 120)->nullable();
            $table->unsignedBigInteger('source_reference_id')->nullable();
            $table->unsignedBigInteger('production_order_id')->nullable();
            $table->unsignedBigInteger('purchase_requisition_id')->nullable();
            $table->text('decision_reason')->nullable();
            $table->json('original_payload')->nullable();
            $table->json('adjusted_payload')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'suggestion_key'], 'uq_mrp_suggestion_key');
            $table->index(['company_id', 'status', 'suggestion_type'], 'ix_mrp_suggestion_status_type');
            $table->index(['company_id', 'product_id', 'need_by_date'], 'ix_mrp_suggestion_product_date');
        });

        Schema::create('mrp_suggestion_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('mrp_suggestion_id')->constrained('mrp_suggestions')->cascadeOnDelete();
            $table->string('event_type', 40);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'mrp_suggestion_id', 'created_at'], 'ix_mrp_suggestion_events_history');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mrp_suggestion_events');
        Schema::dropIfExists('mrp_suggestions');
        Schema::dropIfExists('mrp_plan_runs');
        Schema::dropIfExists('production_schedule_lines');
        Schema::dropIfExists('production_schedules');
        Schema::dropIfExists('production_order_operations');
    }
};
