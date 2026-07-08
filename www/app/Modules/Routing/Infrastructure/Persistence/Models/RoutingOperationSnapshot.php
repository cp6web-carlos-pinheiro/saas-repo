<?php

declare(strict_types=1);

namespace App\Modules\Routing\Infrastructure\Persistence\Models;

use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingVersionSnapshot as RoutingVersionSnapshotModel;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RoutingOperationSnapshot extends TenantModel
{
    use HasFactory;

    protected $table = 'routing_operation_snapshots';

    protected $fillable = [
        'company_id',
        'routing_version_snapshot_id',
        'routing_version_id',
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
        'is_outsourced' => 'bool',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(RoutingVersionSnapshotModel::class, 'routing_version_snapshot_id');
    }

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class, 'work_center_id');
    }
}
