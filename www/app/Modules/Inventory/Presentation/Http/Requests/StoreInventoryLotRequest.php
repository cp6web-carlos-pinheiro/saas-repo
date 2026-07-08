<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreInventoryLotRequest extends FormRequest
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
            'lot_number' => ['required', 'string', 'max:80'],
            'manufactured_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:manufactured_at'],
            'status' => ['nullable', 'string', 'in:ACTIVE,QUARANTINED,CONSUMED,OBSOLETE'],
            'source_movement_id' => ['nullable', 'integer', 'exists:stock_ledger_movements,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
