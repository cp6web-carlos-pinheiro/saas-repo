<?php

declare(strict_types=1);

namespace App\Modules\Production\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreManualProductionOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'quantity_planned' => ['required', 'numeric', 'min:0.000001'],
            'quantity_scrapped' => ['nullable', 'numeric', 'min:0'],
            'bom_version_number' => ['nullable', 'integer', 'min:1'],
            'routing_version_id' => ['nullable', 'integer', 'exists:routing_versions,id'],
            'routing_version_number' => ['nullable', 'integer', 'min:1'],
            'reference_date' => ['nullable', 'date'],
            'scheduled_start_date' => ['nullable', 'date'],
            'scheduled_end_date' => ['nullable', 'date', 'after_or_equal:scheduled_start_date'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
