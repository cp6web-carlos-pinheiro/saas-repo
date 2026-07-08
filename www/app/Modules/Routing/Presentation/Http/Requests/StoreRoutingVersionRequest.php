<?php

declare(strict_types=1);

namespace App\Modules\Routing\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRoutingVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'version_number' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', 'in:DRAFT'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
