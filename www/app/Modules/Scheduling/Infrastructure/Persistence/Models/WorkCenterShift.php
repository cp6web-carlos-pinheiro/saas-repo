<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorkCenterShift extends TenantModel
{
    use HasFactory;

    protected $table = 'work_center_shifts';

    protected $fillable = [
        'company_id',
        'work_center_id',
        'name',
        'shift_start',
        'shift_end',
        'capacity_hours',
        'is_active',
    ];

    protected $casts = [
        'capacity_hours' => 'float',
        'is_active' => 'bool',
    ];

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class);
    }
}
