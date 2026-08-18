<?php

declare(strict_types=1);

namespace App\Modules\MRP\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RecalculateMrpPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference_date' => ['nullable', 'date'],
            'planning_bucket' => ['nullable', 'string', 'in:daily,weekly'],
            'priority_rule' => ['nullable', 'string', 'in:priority_due_date,due_date_priority'],
            'async' => ['nullable', 'boolean'],
            'idempotency_key' => ['nullable', 'string', 'max:120'],
            'recompute_scope' => ['nullable', 'array'],
            'recompute_scope.product_ids' => ['nullable', 'array'],
            'recompute_scope.product_ids.*' => ['integer', 'exists:products,id'],
            'demand_lines' => ['required', 'array', 'min:1'],
            'demand_lines.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'demand_lines.*.warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'demand_lines.*.quantity' => ['required', 'numeric', 'min:0.000001'],
            'demand_lines.*.need_by_date' => ['required', 'date'],
            'demand_lines.*.bom_version_number' => ['nullable', 'integer', 'min:1'],
            'demand_lines.*.routing_version_id' => ['nullable', 'integer', 'exists:routing_versions,id'],
            'demand_lines.*.priority' => ['nullable', 'integer', 'min:1'],
            'demand_lines.*.source_type' => ['nullable', 'string', 'max:80'],
            'demand_lines.*.source_reference_id' => ['nullable', 'integer', 'min:1'],
            'demand_lines.*.source_reference_type' => ['nullable', 'string', 'max:120'],
            'demand_lines.*.metadata' => ['nullable', 'array'],
        ];
    }
}
