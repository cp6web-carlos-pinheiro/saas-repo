<?php

declare(strict_types=1);

namespace App\Modules\Production\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProductionOrderMaterialConsumptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'lot_number' => ['nullable', 'string', 'max:80'],
            'quantity_consumed' => ['required', 'numeric', 'min:0.000001'],
            'quantity_scrapped' => ['nullable', 'numeric', 'min:0'],
            'allocation_strategy' => ['nullable', 'string', 'in:FIFO,FEFO'],
            'reference_bom_component_id' => ['nullable', 'string', 'max:64'],
            'production_order_operation_id' => ['nullable', 'integer'],
            'idempotency_key' => ['nullable', 'string', 'max:120'],
            'allow_unplanned' => ['nullable', 'boolean'],
            'consumed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
