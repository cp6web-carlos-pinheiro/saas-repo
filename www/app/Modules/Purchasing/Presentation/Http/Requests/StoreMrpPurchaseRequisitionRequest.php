<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreMrpPurchaseRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference_date' => ['nullable', 'date'],
            'source_reference_id' => ['nullable', 'integer', 'min:1'],
            'source_reference_type' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'purchase_suggestions' => ['required', 'array', 'min:1'],
            'purchase_suggestions.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'purchase_suggestions.*.warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'purchase_suggestions.*.quantity' => ['required', 'numeric', 'min:0.000001'],
            'purchase_suggestions.*.need_by_date' => ['required', 'date'],
            'purchase_suggestions.*.order_date' => ['nullable', 'date'],
            'purchase_suggestions.*.lead_time_days' => ['nullable', 'integer', 'min:0'],
            'purchase_suggestions.*.source_requirement_key' => ['nullable', 'string', 'max:180'],
            'purchase_suggestions.*.reference_date' => ['nullable', 'date'],
            'purchase_suggestions.*.metadata' => ['nullable', 'array'],
        ];
    }
}
