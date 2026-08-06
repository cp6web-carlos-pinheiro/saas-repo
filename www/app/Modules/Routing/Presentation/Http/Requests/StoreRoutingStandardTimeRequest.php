<?php

declare(strict_types=1);

namespace App\Modules\Routing\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRoutingStandardTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'version_number' => ['nullable', 'integer', 'min:1'],
            'time_basis' => ['sometimes', 'string', 'in:PER_PROCESS,PER_UNIT,PER_BATCH'],
            'setup_scope' => ['sometimes', 'string', 'in:ROUTING,OPERATION'],
            'base_quantity' => ['sometimes', 'numeric', 'gt:0'],
            'setup_time_minutes' => ['sometimes', 'numeric', 'min:0'],
            'runtime_minutes' => ['sometimes', 'numeric', 'min:0'],
            'queue_time_minutes' => ['sometimes', 'numeric', 'min:0'],
            'move_time_minutes' => ['sometimes', 'numeric', 'min:0'],
            'efficiency_factor' => ['sometimes', 'numeric', 'gt:0', 'max:1000'],
            'yield_factor' => ['sometimes', 'numeric', 'gt:0', 'max:1000'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'change_reason' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
