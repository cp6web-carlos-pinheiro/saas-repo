<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AdjustInventoryBalanceRequest extends FormRequest
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
            'delta_available' => ['nullable', 'numeric'],
            'delta_reserved' => ['nullable', 'numeric'],
            'delta_in_transit' => ['nullable', 'numeric'],
            'delta_inspection' => ['nullable', 'numeric'],
        ];
    }
}
