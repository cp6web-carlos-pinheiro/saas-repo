<?php

declare(strict_types=1);

namespace App\Modules\Eco\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ApproveEngineeringChangeOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }
}
