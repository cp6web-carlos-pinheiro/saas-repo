<?php

declare(strict_types=1);

namespace App\Modules\Product\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ImportProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ];
    }
}