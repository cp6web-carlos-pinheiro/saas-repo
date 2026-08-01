<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SaaS\Plan;
use Illuminate\Database\Seeder;

final class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'free_trial',
                'label' => 'Gratis 14 dias',
                'description' => 'Acesso gratuito por 14 dias. Disponível uma única vez e sem renovação.',
                'payment_method' => 'Sem cobrança',
                'billing_cycle_label' => 'Uso único de 14 dias',
                'amount_cents' => 0,
                'trial_days' => 14,
                'interval_months' => null,
                'renewable' => false,
                'allow_once' => true,
                'default_status' => 'trialing',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'code' => 'monthly',
                'label' => 'Plano mensal',
                'description' => 'O valor é cobrado mensalmente no cartão de crédito.',
                'payment_method' => 'Cartão de crédito',
                'billing_cycle_label' => 'Cobrança mensal',
                'amount_cents' => 9900,
                'trial_days' => null,
                'interval_months' => 1,
                'renewable' => true,
                'allow_once' => false,
                'default_status' => 'active',
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'code' => 'semiannual',
                'label' => 'Plano semestral',
                'description' => 'O valor é cobrado semestralmente no cartão de crédito.',
                'payment_method' => 'Cartão de crédito',
                'billing_cycle_label' => 'Cobrança semestral',
                'amount_cents' => 49900,
                'trial_days' => null,
                'interval_months' => 6,
                'renewable' => true,
                'allow_once' => false,
                'default_status' => 'active',
                'is_active' => true,
                'sort_order' => 30,
            ],
            [
                'code' => 'annual',
                'label' => 'Plano anual',
                'description' => 'O valor é cobrado anualmente no cartão de crédito.',
                'payment_method' => 'Cartão de crédito',
                'billing_cycle_label' => 'Cobrança anual',
                'amount_cents' => 89900,
                'trial_days' => null,
                'interval_months' => 12,
                'renewable' => true,
                'allow_once' => false,
                'default_status' => 'active',
                'is_active' => true,
                'sort_order' => 40,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['code' => $plan['code']],
                $plan
            );
        }
    }
}
