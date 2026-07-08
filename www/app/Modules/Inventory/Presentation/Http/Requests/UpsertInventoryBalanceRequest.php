<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpsertInventoryBalanceRequest extends FormRequest
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
            'qty_available' => ['required', 'numeric', 'min:0'],
            'qty_reserved' => ['required', 'numeric', 'min:0'],
            'qty_in_transit' => ['required', 'numeric', 'min:0'],
            'qty_inspection' => ['required', 'numeric', 'min:0'],
        ];
    }
}
