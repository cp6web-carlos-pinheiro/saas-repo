<?php

declare(strict_types=1);

namespace App\Modules\Production\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProductionOrderOutputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity_completed' => ['required', 'numeric', 'min:0.000001'],
            'quantity_scrapped' => ['nullable', 'numeric', 'min:0'],
            'operation_no' => ['nullable', 'integer', 'min:1'],
            'work_center_id' => ['nullable', 'integer', 'exists:work_centers,id'],
            'setup_time_minutes' => ['nullable', 'numeric', 'min:0'],
            'process_time_minutes' => ['nullable', 'numeric', 'min:0'],
            'inspection_status' => ['nullable', 'string', 'in:APPROVED,PENDING,REJECTED'],
            'inspected_at' => ['nullable', 'date'],
            'inspection_notes' => ['nullable', 'string'],
            'lot_number' => ['nullable', 'string', 'max:80'],
            'produced_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
