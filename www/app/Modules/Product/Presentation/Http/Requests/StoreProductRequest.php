<?php

declare(strict_types=1);

namespace App\Modules\Product\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:255'],
            'product_type' => ['required', 'string', 'in:FG,WIP,RAW,CONSUMABLE'],
            'uom' => ['nullable', 'string', 'max:20'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'safety_stock' => ['required', 'integer', 'min:0'],
            'lead_time_days' => ['required', 'integer', 'min:0'],
            'lot_control' => ['required', 'boolean'],
            'serial_control' => ['required', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
