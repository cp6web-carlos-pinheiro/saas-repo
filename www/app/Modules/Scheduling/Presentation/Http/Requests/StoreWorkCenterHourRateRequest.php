<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreWorkCenterHourRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'change_reason' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
