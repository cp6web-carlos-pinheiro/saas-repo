<?php

declare(strict_types=1);

namespace App\Modules\Production\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderSnapshot as ProductionOrderSnapshotModel;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class ProductionOrder extends TenantModel
{
    use HasFactory;

    protected $table = 'production_orders';

    protected $fillable = [
        'company_id',
        'product_id',
        'warehouse_id',
        'bom_header_id',
        'bom_version_number',
        'routing_version_id',
        'routing_version_number',
        'source_type',
        'source_reference_id',
        'source_reference_type',
        'order_number',
        'status',
        'quantity_planned',
        'quantity_produced',
        'quantity_scrapped',
        'scheduled_start_date',
        'scheduled_end_date',
        'released_at',
        'started_at',
        'completed_at',
        'created_by',
        'released_by',
        'completed_by',
        'metadata',
    ];

    protected $casts = [
        'quantity_planned' => 'float',
        'quantity_produced' => 'float',
        'quantity_scrapped' => 'float',
        'scheduled_start_date' => 'date',
        'scheduled_end_date' => 'date',
        'released_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(ProductionOperationOutput::class, 'production_order_id');
    }

    public function snapshot(): HasOne
    {
        return $this->hasOne(ProductionOrderSnapshotModel::class, 'production_order_id');
    }

    public function materialConsumptions(): HasMany
    {
        return $this->hasMany(ProductionOrderMaterialConsumption::class, 'production_order_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(ProductionOrderOperation::class, 'production_order_id')->orderBy('sequence');
    }
}
