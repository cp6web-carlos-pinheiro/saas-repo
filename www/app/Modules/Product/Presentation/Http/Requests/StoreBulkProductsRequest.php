<?php

declare(strict_types=1);

namespace App\Modules\Product\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreBulkProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku' => ['required', 'string', 'max:80'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.product_type' => ['required', 'string', 'in:FG,WIP,RAW,CONSUMABLE'],
            'items.*.uom' => ['nullable', 'string', 'max:20'],
            'items.*.unit_id' => ['required', 'integer', 'exists:units,id'],
            'items.*.safety_stock' => ['required', 'integer', 'min:0'],
            'items.*.lead_time_days' => ['required', 'integer', 'min:0'],
            'items.*.lot_control' => ['required', 'boolean'],
            'items.*.serial_control' => ['required', 'boolean'],
            'items.*.is_active' => ['sometimes', 'boolean'],
        ];
    }
}
