<?php

declare(strict_types=1);

namespace App\Modules\Routing\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ObsoleteRoutingVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
