<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class BulkGenerateProductionCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
        ];
    }
}
