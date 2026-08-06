<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('manufacturing_analytics_recommendations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('production_order_operation_id')->nullable()
                ->constrained('production_order_operations', 'id', 'fk_analytics_recommendation_operation')
                ->nullOnDelete();
            $table->unsignedBigInteger('routing_operation_id')->nullable();
            $table->unsignedBigInteger('standard_time_id')->nullable();
            $table->unsignedInteger('standard_time_version')->nullable();
            $table->string('status', 20)->default('PENDING');
            $table->decimal('current_time_minutes', 10, 2)->nullable();
            $table->decimal('suggested_time_minutes', 10, 2)->nullable();
            $table->unsignedInteger('sample_size')->default(0);
            $table->json('statistics')->nullable();
            $table->json('filters')->nullable();
            $table->text('decision_reason')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status', 'created_at'], 'ix_analytics_recommendation_status');
        });
    }
    public function down(): void { Schema::dropIfExists('manufacturing_analytics_recommendations'); }
};
