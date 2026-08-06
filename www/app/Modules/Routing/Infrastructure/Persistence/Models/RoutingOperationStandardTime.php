<?php

declare(strict_types=1);

namespace App\Modules\Routing\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RoutingOperationStandardTime extends TenantModel
{
    use HasFactory;

    protected $table = 'routing_operation_standard_times';

    protected $fillable = [
        'company_id', 'routing_operation_id', 'version_number', 'status', 'time_basis', 'setup_scope', 'base_quantity',
        'setup_time_minutes', 'runtime_minutes', 'queue_time_minutes', 'move_time_minutes',
        'efficiency_factor', 'yield_factor', 'effective_from', 'effective_to', 'created_by',
        'approved_by', 'approved_at', 'change_reason', 'metadata',
    ];

    protected $casts = [
        'base_quantity' => 'float',
        'setup_time_minutes' => 'float',
        'runtime_minutes' => 'float',
        'queue_time_minutes' => 'float',
        'move_time_minutes' => 'float',
        'efficiency_factor' => 'float',
        'yield_factor' => 'float',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function routingOperation(): BelongsTo
    {
        return $this->belongsTo(RoutingOperation::class, 'routing_operation_id');
    }
}
