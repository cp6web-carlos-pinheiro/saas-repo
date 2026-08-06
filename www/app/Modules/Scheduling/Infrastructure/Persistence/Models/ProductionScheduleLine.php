<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Infrastructure\Persistence\Models;

use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderOperation;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionScheduleLine extends TenantModel
{
    use HasFactory;

    protected $table = 'production_schedule_lines';

    protected $fillable = [
        'company_id', 'production_schedule_id', 'production_order_id', 'production_order_operation_id',
        'work_center_id', 'production_resource_id', 'planned_start_at', 'planned_end_at',
        'total_time_minutes', 'capacity_time_minutes', 'lead_time_minutes', 'segments', 'status', 'metadata',
    ];

    protected $casts = [
        'planned_start_at' => 'datetime',
        'planned_end_at' => 'datetime',
        'total_time_minutes' => 'float',
        'capacity_time_minutes' => 'float',
        'lead_time_minutes' => 'float',
        'segments' => 'array',
        'metadata' => 'array',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ProductionSchedule::class, 'production_schedule_id');
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function productionOrderOperation(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderOperation::class, 'production_order_operation_id');
    }
}
