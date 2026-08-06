<?php

declare(strict_types=1);

namespace App\Modules\MRP\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class MrpPlanRun extends TenantModel
{
    use HasFactory;

    protected $table = 'mrp_plan_runs';

    protected $fillable = [
        'company_id', 'run_key', 'status', 'reference_date', 'planning_bucket', 'priority_rule',
        'request_payload', 'result_summary', 'created_by',
    ];

    protected $casts = [
        'reference_date' => 'date',
        'request_payload' => 'array',
        'result_summary' => 'array',
    ];

    public function suggestions(): HasMany
    {
        return $this->hasMany(MrpSuggestion::class, 'mrp_plan_run_id');
    }
}
