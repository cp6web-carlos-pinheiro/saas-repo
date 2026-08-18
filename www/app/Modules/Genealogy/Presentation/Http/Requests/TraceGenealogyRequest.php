<?php

declare(strict_types=1);

namespace App\Modules\Genealogy\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TraceGenealogyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'node_type' => ['required', 'string', 'in:PRODUCT,LOT,SERIAL,PRODUCTION_ORDER,MATERIAL'],
            'source_id' => ['required', 'integer', 'min:1'],
            'direction' => ['nullable', 'string', 'in:forward,backward'],
        ];
    }
}
