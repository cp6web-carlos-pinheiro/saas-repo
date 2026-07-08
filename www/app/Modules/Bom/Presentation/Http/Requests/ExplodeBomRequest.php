<?php

declare(strict_types=1);

namespace App\Modules\Bom\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExplodeBomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'reference_date' => ['nullable', 'date'],
            'version_number' => ['nullable', 'integer', 'min:1'],
            'max_depth' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
