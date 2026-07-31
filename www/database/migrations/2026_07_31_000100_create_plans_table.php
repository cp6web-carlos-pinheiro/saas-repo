<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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

        $now = now();

        DB::table('plans')->insert([
            [
                'code' => 'free_trial',
                'label' => 'Gratis 14 dias',
                'description' => 'Acesso gratuito por 14 dias. Disponivel uma unica vez e sem renovacao.',
                'payment_method' => 'Sem cobranca',
                'billing_cycle_label' => 'Uso unico de 14 dias',
                'trial_days' => 14,
                'interval_months' => null,
                'renewable' => false,
                'allow_once' => true,
                'default_status' => 'trialing',
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'monthly',
                'label' => 'Plano mensal',
                'description' => 'O valor e cobrado mensalmente no cartao de credito.',
                'payment_method' => 'Cartao de credito',
                'billing_cycle_label' => 'Cobranca mensal',
                'trial_days' => null,
                'interval_months' => 1,
                'renewable' => true,
                'allow_once' => false,
                'default_status' => 'active',
                'is_active' => true,
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'semiannual',
                'label' => 'Plano semestral',
                'description' => 'O valor e cobrado semestralmente no cartao de credito.',
                'payment_method' => 'Cartao de credito',
                'billing_cycle_label' => 'Cobranca semestral',
                'trial_days' => null,
                'interval_months' => 6,
                'renewable' => true,
                'allow_once' => false,
                'default_status' => 'active',
                'is_active' => true,
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'annual',
                'label' => 'Plano anual',
                'description' => 'O valor e cobrado anualmente no cartao de credito.',
                'payment_method' => 'Cartao de credito',
                'billing_cycle_label' => 'Cobranca anual',
                'trial_days' => null,
                'interval_months' => 12,
                'renewable' => true,
                'allow_once' => false,
                'default_status' => 'active',
                'is_active' => true,
                'sort_order' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
