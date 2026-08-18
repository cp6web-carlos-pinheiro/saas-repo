<?php

declare(strict_types=1);

namespace App\Modules\Production\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionOperationOutput extends TenantModel
{
    protected $table = 'production_operation_outputs';

    protected $appends = ['quantity_completed', 'produced_at', 'operation_no'];

    protected $fillable = ['company_id', 'production_order_id', 'production_order_operation_id', 'work_center_id', 'setup_time_minutes', 'process_time_minutes', 'quantity_good', 'quantity_scrapped', 'quantity_rework', 'lot_number', 'inspection_status', 'inspected_at', 'inspection_notes', 'scrap_cause_code', 'destination', 'operator_id', 'created_by', 'production_resource_id', 'reported_at', 'notes', 'metadata'];

    protected $casts = ['setup_time_minutes' => 'float', 'process_time_minutes' => 'float', 'quantity_good' => 'float', 'quantity_scrapped' => 'float', 'quantity_rework' => 'float', 'inspected_at' => 'datetime', 'reported_at' => 'datetime', 'metadata' => 'array'];

    public function operation(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderOperation::class, 'production_order_operation_id');
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function getQuantityCompletedAttribute(): float
    {
        return (float) $this->quantity_good;
    }

    public function getProducedAtAttribute(): mixed
    {
        return $this->reported_at;
    }

    public function getOperationNoAttribute(): ?int
    {
        return $this->operation?->operation_no;
    }
}
