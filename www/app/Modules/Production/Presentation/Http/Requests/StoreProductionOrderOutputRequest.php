<?php

declare(strict_types=1);

namespace App\Modules\Production\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProductionOrderOutputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity_completed' => ['required', 'numeric', 'min:0.000001'],
            'quantity_scrapped' => ['nullable', 'numeric', 'min:0'],
            'lot_number' => ['nullable', 'string', 'max:80'],
            'produced_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
