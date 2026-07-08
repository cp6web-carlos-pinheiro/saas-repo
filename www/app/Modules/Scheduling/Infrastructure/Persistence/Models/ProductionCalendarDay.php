<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Infrastructure\Persistence\Models;

use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionCalendarDay extends TenantModel
{
    use HasFactory;

    protected $table = 'production_calendar_days';

    protected $fillable = [
        'company_id',
        'work_center_id',
        'calendar_date',
        'is_working_day',
        'available_capacity',
        'notes',
    ];

    protected $casts = [
        'calendar_date' => 'date',
        'is_working_day' => 'bool',
        'available_capacity' => 'float',
    ];

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class);
    }
}
