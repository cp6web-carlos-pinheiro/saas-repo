<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_centers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->string('resource_type', 20); // MACHINE | LINE
            $table->decimal('capacity_per_day', 10, 2);
            $table->decimal('efficiency_factor', 5, 2)->default(100.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'plant_id']);
            $table->index(['company_id', 'resource_type']);
        });

        Schema::create('work_center_shifts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('work_center_id')->constrained('work_centers')->cascadeOnDelete();
            $table->string('name', 80);
            $table->time('shift_start');
            $table->time('shift_end');
            $table->decimal('capacity_hours', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'work_center_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_center_shifts');
        Schema::dropIfExists('work_centers');
    }
};
