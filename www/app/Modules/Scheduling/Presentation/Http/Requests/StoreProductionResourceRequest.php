<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProductionResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
            'work_center_id' => ['required', 'integer', 'exists:work_centers,id'],
            'code' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:150'],
            'resource_type' => ['required', 'string', 'in:MACHINE,EQUIPMENT,TOOL,LINE,OUTSOURCED'],
            'status' => ['sometimes', 'string', 'in:ACTIVE,INACTIVE,MAINTENANCE,BLOCKED,DECOMMISSIONED'],
            'capacity_per_day' => ['nullable', 'numeric', 'min:0'],
            'efficiency_factor' => ['nullable', 'numeric', 'gt:0', 'max:1000'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
