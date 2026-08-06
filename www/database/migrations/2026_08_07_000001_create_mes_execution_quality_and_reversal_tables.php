<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('production_order_operations', function (Blueprint $table): void {
            $table->foreignId('actual_production_resource_id')->nullable()
                ->constrained('production_resources', 'id', 'fk_po_operation_actual_resource')
                ->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('quantity_processed', 18, 6)->default(0);
            $table->decimal('quantity_good', 18, 6)->default(0);
            $table->decimal('quantity_scrapped', 18, 6)->default(0);
            $table->decimal('quantity_rework', 18, 6)->default(0);
            $table->decimal('actual_productive_minutes', 10, 2)->default(0);
            $table->decimal('actual_pause_minutes', 10, 2)->default(0);
            $table->dateTime('actual_started_at')->nullable();
            $table->dateTime('actual_completed_at')->nullable();
        });

        Schema::table('production_order_material_consumptions', function (Blueprint $table): void {
            $table->foreignId('production_order_operation_id')->nullable()
                ->constrained('production_order_operations', 'id', 'fk_po_consumption_operation')
                ->nullOnDelete();
            $table->string('idempotency_key', 120)->nullable();
            $table->unsignedBigInteger('reversed_by_movement_id')->nullable();
            $table->index(['company_id', 'production_order_operation_id'], 'ix_mes_consumption_operation');
            $table->unique(['company_id', 'idempotency_key'], 'uq_mes_consumption_idempotency');
        });

        Schema::create('production_operation_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('production_order_operation_id')
                ->constrained('production_order_operations', 'id', 'fk_operation_event_order_operation')
                ->cascadeOnDelete();
            $table->string('event_type', 20);
            $table->string('idempotency_key', 120);
            $table->timestamp('occurred_at');
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('production_resource_id')->nullable()->constrained('production_resources')->nullOnDelete();
            $table->string('reason_code', 80)->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'idempotency_key'], 'uq_mes_operation_event_idempotency');
            $table->index(['company_id', 'production_order_operation_id', 'occurred_at'], 'ix_mes_operation_event_history');
        });

        Schema::create('production_operation_outputs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('production_order_operation_id')
                ->constrained('production_order_operations', 'id', 'fk_operation_output_order_operation')
                ->cascadeOnDelete();
            $table->decimal('quantity_good', 18, 6)->default(0);
            $table->decimal('quantity_scrapped', 18, 6)->default(0);
            $table->decimal('quantity_rework', 18, 6)->default(0);
            $table->string('lot_number', 80)->nullable();
            $table->string('inspection_status', 20)->default('PENDING');
            $table->string('scrap_cause_code', 80)->nullable();
            $table->string('destination', 30)->nullable();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('production_resource_id')->nullable()->constrained('production_resources')->nullOnDelete();
            $table->timestamp('reported_at');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'production_order_operation_id', 'reported_at'], 'ix_mes_operation_outputs');
        });

        Schema::create('production_quality_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('production_order_operation_id')->constrained('production_order_operations')->cascadeOnDelete();
            $table->string('record_type', 30)->default('NON_CONFORMITY');
            $table->string('status', 30)->default('PENDING');
            $table->decimal('quantity', 18, 6)->default(0);
            $table->string('cause_code', 80)->nullable();
            $table->string('destination', 30)->nullable();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('production_resource_id')->nullable()->constrained('production_resources')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'production_order_operation_id', 'status'], 'ix_mes_quality_operation_status');
        });

        Schema::create('production_rework_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('source_production_order_operation_id')
                ->constrained('production_order_operations', 'id', 'fk_rework_source_operation')
                ->cascadeOnDelete();
            $table->foreignId('rework_production_order_operation_id')->nullable()
                ->constrained('production_order_operations', 'id', 'fk_rework_target_operation')
                ->nullOnDelete();
            $table->decimal('quantity', 18, 6);
            $table->string('status', 20)->default('OPEN');
            $table->string('reason_code', 80)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('production_material_consumption_reversals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('production_order_material_consumption_id')
                ->constrained('production_order_material_consumptions', 'id', 'fk_consumption_reversal_consumption')
                ->cascadeOnDelete();
            $table->foreignId('original_ledger_movement_id')
                ->constrained('stock_ledger_movements', 'id', 'fk_consumption_reversal_original_ledger')
                ->cascadeOnDelete();
            $table->foreignId('reversal_ledger_movement_id')
                ->constrained('stock_ledger_movements', 'id', 'fk_consumption_reversal_ledger')
                ->cascadeOnDelete();
            $table->decimal('quantity', 18, 6);
            $table->string('reason', 255);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['company_id', 'production_order_material_consumption_id'], 'uq_mes_consumption_reversal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_material_consumption_reversals');
        Schema::dropIfExists('production_rework_orders');
        Schema::dropIfExists('production_quality_records');
        Schema::dropIfExists('production_operation_outputs');
        Schema::dropIfExists('production_operation_events');
        Schema::table('production_order_material_consumptions', function (Blueprint $table): void {
            $table->dropUnique('uq_mes_consumption_idempotency');
            $table->dropIndex('ix_mes_consumption_operation');
            $table->dropForeign('fk_po_consumption_operation');
            $table->dropColumn(['production_order_operation_id', 'idempotency_key', 'reversed_by_movement_id']);
        });
        Schema::table('production_order_operations', function (Blueprint $table): void {
            $table->dropForeign('fk_po_operation_actual_resource');
            $table->dropForeign(['operator_id']);
            $table->dropColumn(['actual_production_resource_id', 'operator_id', 'quantity_processed', 'quantity_good', 'quantity_scrapped', 'quantity_rework', 'actual_productive_minutes', 'actual_pause_minutes', 'actual_started_at', 'actual_completed_at']);
        });
    }
};
