<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePurchaseRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'required_date' => ['nullable', 'date'],
            'source_type' => ['nullable', 'string', 'max:80'],
            'source_reference_id' => ['nullable', 'integer', 'min:1'],
            'source_reference_type' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'lines.*.warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'lines.*.supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'lines.*.suggested_quantity' => ['nullable', 'numeric', 'min:0.000001'],
            'lines.*.requested_quantity' => ['required', 'numeric', 'min:0.000001'],
            'lines.*.moq_applied' => ['nullable', 'numeric', 'min:0.000001'],
            'lines.*.lead_time_days' => ['nullable', 'integer', 'min:0'],
            'lines.*.need_by_date' => ['required', 'date'],
            'lines.*.order_date' => ['required', 'date'],
            'lines.*.source_requirement_key' => ['nullable', 'string', 'max:180'],
            'lines.*.mrp_reference_date' => ['nullable', 'date'],
            'lines.*.metadata' => ['nullable', 'array'],
        ];
    }
}
