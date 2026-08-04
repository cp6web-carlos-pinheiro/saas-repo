<?php

declare(strict_types=1);

namespace App\Modules\Production\Infrastructure\Persistence\Models;

use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionOrderOutput extends TenantModel
{
    use HasFactory;

    protected $table = 'production_order_outputs';

    protected $fillable = [
        'company_id',
        'production_order_id',
        'quantity_completed',
        'quantity_scrapped',
        'operation_no',
        'work_center_id',
        'setup_time_minutes',
        'process_time_minutes',
        'inspection_status',
        'inspected_at',
        'inspection_notes',
        'lot_number',
        'produced_at',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'quantity_completed' => 'float',
        'quantity_scrapped' => 'float',
        'operation_no' => 'integer',
        'setup_time_minutes' => 'float',
        'process_time_minutes' => 'float',
        'inspected_at' => 'datetime',
        'produced_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class, 'work_center_id');
    }
}
