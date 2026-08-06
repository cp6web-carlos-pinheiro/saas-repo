<?php

declare(strict_types=1);

namespace App\Modules\Production\Infrastructure\Persistence\Models;

use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderSnapshot as ProductionOrderSnapshotModel;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionOrderRoutingOperationSnapshot extends TenantModel
{
    use HasFactory;

    protected $table = 'production_order_routing_operation_snapshots';

    protected $fillable = [
        'company_id',
        'production_order_snapshot_id',
        'routing_version_id',
        'standard_time_id',
        'standard_time_version',
        'work_center_id',
        'operation_no',
        'operation_code',
        'operation_name',
        'sequence',
        'setup_time_minutes',
        'runtime_minutes',
        'queue_time_minutes',
        'move_time_minutes',
        'is_outsourced',
    ];

    protected $casts = [
        'setup_time_minutes' => 'float',
        'runtime_minutes' => 'float',
        'queue_time_minutes' => 'float',
        'move_time_minutes' => 'float',
        'standard_time_id' => 'integer',
        'standard_time_version' => 'integer',
        'is_outsourced' => 'bool',
    ];

    public function productionOrderSnapshot(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderSnapshotModel::class, 'production_order_snapshot_id');
    }

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class, 'work_center_id');
    }
}
