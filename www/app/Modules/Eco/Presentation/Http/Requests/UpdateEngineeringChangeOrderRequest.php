<?php

declare(strict_types=1);

namespace App\Modules\Eco\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEngineeringChangeOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'metadata' => ['nullable', 'array'],
            'lines' => ['sometimes', 'array', 'min:1'],
            'lines.*.target_domain' => ['required_with:lines', 'string', 'in:PRODUCT,BOM,ROUTING,STANDARD_TIME'],
            'lines.*.target_entity_id' => ['required_with:lines', 'integer', 'min:1'],
            'lines.*.change_type' => ['nullable', 'string', 'max:40'],
            'lines.*.from_version_number' => ['nullable', 'integer', 'min:1'],
            'lines.*.to_version_number' => ['nullable', 'integer', 'min:1'],
            'lines.*.effective_from' => ['nullable', 'date'],
            'lines.*.effective_to' => ['nullable', 'date'],
            'lines.*.impact_level' => ['nullable', 'string', 'in:LOW,MEDIUM,HIGH,CRITICAL'],
            'lines.*.change_summary' => ['nullable', 'string'],
            'lines.*.metadata' => ['nullable', 'array'],
        ];
    }
}
