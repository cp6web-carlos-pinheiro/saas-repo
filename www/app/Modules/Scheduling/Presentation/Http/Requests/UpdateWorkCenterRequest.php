<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateWorkCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
            'name' => ['required', 'string', 'max:150'],
            'resource_type' => ['required', 'string', 'in:MACHINE,LINE'],
            'capacity_per_day' => ['required', 'numeric', 'min:0'],
            'efficiency_factor' => ['required', 'numeric', 'min:0', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
