<?php

declare(strict_types=1);

namespace App\Modules\Routing\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateRoutingOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_center_id' => ['required', 'integer', 'exists:work_centers,id'],
            'operation_no' => ['required', 'integer', 'min:1'],
            'operation_code' => ['required', 'string', 'max:50'],
            'operation_name' => ['required', 'string', 'max:150'],
            'sequence' => ['required', 'integer', 'min:1'],
            'setup_time_minutes' => ['nullable', 'numeric', 'min:0'],
            'runtime_minutes' => ['nullable', 'numeric', 'min:0'],
            'queue_time_minutes' => ['nullable', 'numeric', 'min:0'],
            'move_time_minutes' => ['nullable', 'numeric', 'min:0'],
            'is_outsourced' => ['sometimes', 'boolean'],
        ];
    }
}
