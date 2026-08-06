<?php

declare(strict_types=1);

namespace App\Modules\Production\Infrastructure\Persistence\Models;

use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionResource;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
