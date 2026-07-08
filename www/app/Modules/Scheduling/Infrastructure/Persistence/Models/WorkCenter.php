<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Infrastructure\Persistence\Models;

use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionCalendarDay;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenterShift;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Plant;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorkCenter extends TenantModel
{
    use HasFactory;

    protected $table = 'work_centers';

    protected $fillable = [
        'company_id',
        'plant_id',
        'code',
        'name',
        'resource_type',
        'capacity_per_day',
        'efficiency_factor',
        'is_active',
    ];

    protected $casts = [
        'capacity_per_day' => 'float',
        'efficiency_factor' => 'float',
        'is_active' => 'bool',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(WorkCenterShift::class);
    }

    public function calendarDays(): HasMany
    {
        return $this->hasMany(ProductionCalendarDay::class);
    }
}
