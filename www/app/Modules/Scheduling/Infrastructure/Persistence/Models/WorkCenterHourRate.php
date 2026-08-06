<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorkCenterHourRate extends TenantModel
{
    use HasFactory;

    protected $table = 'work_center_hour_rates';

    protected $fillable = [
        'company_id', 'work_center_id', 'hourly_rate', 'currency', 'effective_from', 'effective_to',
        'status', 'approved_by', 'approved_at', 'change_reason', 'metadata',
    ];

    protected $casts = [
        'hourly_rate' => 'float',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class);
    }
}
