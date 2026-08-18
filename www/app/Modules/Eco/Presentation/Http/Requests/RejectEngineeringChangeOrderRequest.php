<?php

declare(strict_types=1);

namespace App\Modules\Eco\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RejectEngineeringChangeOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
