<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_calendar_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('work_center_id')->constrained('work_centers')->cascadeOnDelete();
            $table->date('calendar_date');
            $table->boolean('is_working_day')->default(true);
            $table->decimal('available_capacity', 10, 2)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'work_center_id', 'calendar_date'], 'uq_calendar_day');
            $table->index(['company_id', 'calendar_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_calendar_days');
    }
};
