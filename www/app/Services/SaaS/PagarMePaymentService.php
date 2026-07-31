<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Models\SaaS\Organization;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class PagarMePaymentService
{
    public function amountForPlan(string $planCode): int
    {
        return (int) config("services.pagarme.plans.{$planCode}", 0);
    }

    /**
     * @param  array{card_token:string,last_four:string}  $card
     * @return array{order_id:?string,charge_id:?string,customer_id:?string,last_four:string}
     */
    public function charge(User $user, Organization $organization, string $planCode, array $card): array
    {
        if ($this->usesSimulatedGateway()) {
            return [
                'order_id' => 'local_'.Str::uuid(),
                'charge_id' => null,
                'customer_id' => null,
                'last_four' => $card['last_four'],
            ];
        }

        $secretKey = (string) config('services.pagarme.secret_key');
        $amount = $this->amountForPlan($planCode);

        if ($secretKey === '' || $amount <= 0) {
            throw new PaymentFailedException(__('payment.configuration_error'));
        }

        try {
            $response = Http::acceptJson()
                ->withBasicAuth($secretKey, '')
                ->timeout((int) config('services.pagarme.timeout', 30))
                ->post(rtrim((string) config('services.pagarme.base_url'), '/').'/orders', [
                    'items' => [[
                        'amount' => $amount,
                        'description' => "Beyond MRP - {$planCode}",
                        'quantity' => 1,
                        'code' => "beyond-mrp-{$planCode}",
                    ]],
                    'customer' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'type' => 'individual',
                    ],
                    'payments' => [[
                        'payment_method' => 'credit_card',
                        'credit_card' => [
                            'installments' => 1,
                            'statement_descriptor' => 'BEYOND MRP',
                            'card_token' => $card['card_token'],
                        ],
                    ]],
                    'metadata' => [
                        'organization_id' => (string) $organization->id,
                        'plan_code' => $planCode,
                    ],
                ]);
        } catch (ConnectionException $exception) {
            report($exception);
            throw new PaymentFailedException(__('payment.unavailable'));
        }

        $payload = $response->json() ?: [];
        $status = (string) data_get($payload, 'status');
        $chargeStatus = (string) data_get($payload, 'charges.0.status');

        if (! $response->successful() || ! in_array($status ?: $chargeStatus, ['paid', 'authorized'], true)) {
            throw new PaymentFailedException($this->reasonFrom($payload, $response->status()));
        }

        return [
            'order_id' => data_get($payload, 'id'),
            'charge_id' => data_get($payload, 'charges.0.id'),
            'customer_id' => data_get($payload, 'customer.id'),
            'last_four' => $card['last_four'],
        ];
    }

    private function reasonFrom(array $payload, int $httpStatus): string
    {
        return (string) (data_get($payload, 'charges.0.last_transaction.gateway_response.message')
            ?? data_get($payload, 'errors.0.message')
            ?? data_get($payload, 'message')
            ?? __('payment.declined', ['code' => $httpStatus]));
    }

    public function usesSimulatedGateway(): bool
    {
        return in_array((string) config('app.env'), ['local', 'test', 'testing'], true);
    }
}
