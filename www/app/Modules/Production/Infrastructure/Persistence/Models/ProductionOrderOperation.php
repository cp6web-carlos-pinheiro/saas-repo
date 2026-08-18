<?php

declare(strict_types=1);

namespace App\Modules\Production\Infrastructure\Persistence\Models;

use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionResource;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductionOrderOperation extends TenantModel
{
    use HasFactory;

    protected $table = 'production_order_operations';

    protected $fillable = [
        'company_id', 'production_order_id', 'production_order_routing_operation_snapshot_id', 'routing_operation_id',
        'standard_time_id', 'standard_time_version', 'operation_no', 'operation_code', 'operation_name', 'sequence',
        'work_center_id', 'production_resource_id', 'status', 'quantity_planned', 'setup_scope',
        'setup_time_minutes', 'runtime_time_minutes', 'queue_time_minutes', 'move_time_minutes',
        'productive_time_minutes', 'lead_time_minutes', 'total_time_minutes', 'planned_start_at', 'planned_end_at',
        'calculation_metadata',
        'actual_production_resource_id', 'operator_id', 'quantity_processed', 'quantity_good', 'quantity_scrapped', 'quantity_rework',
        'actual_productive_minutes', 'actual_pause_minutes', 'actual_started_at', 'actual_completed_at',
    ];

    protected $casts = [
        'quantity_planned' => 'float',
        'setup_time_minutes' => 'float',
        'runtime_time_minutes' => 'float',
        'queue_time_minutes' => 'float',
        'move_time_minutes' => 'float',
        'productive_time_minutes' => 'float',
        'lead_time_minutes' => 'float',
        'total_time_minutes' => 'float',
        'planned_start_at' => 'datetime',
        'planned_end_at' => 'datetime',
        'calculation_metadata' => 'array',
        'quantity_processed' => 'float', 'quantity_good' => 'float', 'quantity_scrapped' => 'float', 'quantity_rework' => 'float',
        'actual_productive_minutes' => 'float', 'actual_pause_minutes' => 'float',
        'actual_started_at' => 'datetime', 'actual_completed_at' => 'datetime',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class, 'work_center_id');
    }

    public function productionResource(): BelongsTo
    {
        return $this->belongsTo(ProductionResource::class, 'production_resource_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ProductionOperationEvent::class, 'production_order_operation_id')->orderBy('occurred_at');
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(ProductionOperationOutput::class, 'production_order_operation_id');
    }

    public function qualityRecords(): HasMany
    {
        return $this->hasMany(ProductionQualityRecord::class, 'production_order_operation_id');
    }
}
