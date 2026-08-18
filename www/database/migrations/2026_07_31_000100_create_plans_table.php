<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('label', 180);
            $table->text('description')->nullable();
            $table->string('payment_method', 120)->nullable();
            $table->string('billing_cycle_label', 180)->nullable();
            $table->unsignedSmallInteger('trial_days')->nullable();
            $table->unsignedSmallInteger('interval_months')->nullable();
            $table->boolean('renewable')->default(true);
            $table->boolean('allow_once')->default(false);
            $table->string('default_status', 40)->default('active');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
