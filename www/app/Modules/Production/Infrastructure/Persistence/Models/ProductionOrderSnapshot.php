<?php

declare(strict_types=1);

namespace App\Modules\Production\Infrastructure\Persistence\Models;

use App\Modules\Bom\Infrastructure\Persistence\Models\ProductionOrderBomSnapshot;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductionOrderSnapshot extends TenantModel
{
    use HasFactory;

    protected $table = 'production_order_snapshots';

    protected $fillable = [
        'company_id',
        'production_order_id',
        'product_id',
        'bom_snapshot_id',
        'bom_header_id',
        'bom_version_number',
        'routing_version_snapshot_id',
        'routing_version_id',
        'routing_version_number',
        'quantity_planned',
        'quantity_scrapped_target',
        'snapshot_hash',
        'frozen_at',
        'frozen_by',
    ];

    protected $casts = [
        'quantity_planned' => 'float',
        'quantity_scrapped_target' => 'float',
        'frozen_at' => 'datetime',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function bomSnapshot(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderBomSnapshot::class, 'bom_snapshot_id');
    }

    public function routingOperations(): HasMany
    {
        return $this->hasMany(ProductionOrderRoutingOperationSnapshot::class, 'production_order_snapshot_id')
            ->orderBy('sequence');
    }
}
