<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreStockLedgerMovementRequest extends FormRequest
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
            'movement_type' => ['required', 'string', 'in:RECEIPT,ISSUE,RESERVE,RELEASE,TRANSFER_OUT,TRANSFER_IN,INSPECTION_HOLD,INSPECTION_RELEASE'],
            'quantity' => ['required', 'numeric', 'min:0.000001'],
            'allocation_strategy' => ['nullable', 'string', 'in:FIFO,FEFO'],
            'lot_number' => ['nullable', 'string', 'max:80'],
            'expires_at' => ['nullable', 'date'],
            'reference_type' => ['nullable', 'string', 'max:120'],
            'reference_id' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'movement_at' => ['nullable', 'date'],
        ];
    }
}
