<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('engineering_change_orders')) {
            Schema::create('engineering_change_orders', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('eco_number', 40);
                $table->string('title', 180);
                $table->text('description')->nullable();
                $table->string('status', 20)->default('DRAFT');
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('submitted_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('rejected_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->foreignId('implemented_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('implemented_at')->nullable();
                $table->json('impact_summary')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'eco_number'], 'uq_eco_number_company');
                $table->index(['company_id', 'status']);
                $table->index(['company_id', 'effective_from']);
            });
        }

        if (!Schema::hasTable('engineering_change_order_lines')) {
            Schema::create('engineering_change_order_lines', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('engineering_change_order_id', 'fk_eco_lines_eco_id')
                    ->constrained('engineering_change_orders', 'id')
                    ->cascadeOnDelete();
                $table->string('target_domain', 20); // PRODUCT | BOM | ROUTING
                $table->unsignedBigInteger('target_entity_id');
                $table->string('change_type', 40)->default('VERSION_CHANGE');
                $table->unsignedInteger('from_version_number')->nullable();
                $table->unsignedInteger('to_version_number')->nullable();
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->string('impact_level', 20)->default('MEDIUM');
                $table->text('change_summary')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'target_domain', 'target_entity_id'], 'idx_eco_lines_target');
                $table->index(['company_id', 'engineering_change_order_id'], 'idx_eco_lines_order');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('engineering_change_order_lines');
        Schema::dropIfExists('engineering_change_orders');
    }
};