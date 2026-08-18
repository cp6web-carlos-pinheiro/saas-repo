<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RunProductionSchedulingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference_date' => ['nullable', 'date'],
            'mode' => ['nullable', 'string', 'in:finite,infinite'],
            'direction' => ['nullable', 'string', 'in:forward,backward'],
            'sequencing_rule' => ['nullable', 'string', 'in:priority_due_date,due_date_priority,release_date_priority,order_number'],
            'production_order_ids' => ['required', 'array', 'min:1'],
            'production_order_ids.*' => ['integer', 'exists:production_orders,id'],
        ];
    }
}
