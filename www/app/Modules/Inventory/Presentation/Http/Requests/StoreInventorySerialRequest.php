<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreInventorySerialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'inventory_lot_id' => ['nullable', 'integer', 'exists:inventory_lots,id'],
            'serial_number' => ['required', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'in:ACTIVE,SHIPPED,SCRAPPED,CONSUMED'],
            'source_movement_id' => ['nullable', 'integer', 'exists:stock_ledger_movements,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
