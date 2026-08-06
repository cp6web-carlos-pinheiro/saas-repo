<?php

declare(strict_types=1);

namespace App\Modules\Bom\Infrastructure\Persistence\Models;

use App\Modules\Bom\Infrastructure\Persistence\Models\ProductionOrderBomSnapshot as ProductionOrderBomSnapshotModel;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionOrderBomItemSnapshot extends TenantModel
{
    use HasFactory;

    protected $table = 'production_order_bom_item_snapshots';

    protected $fillable = [
        'company_id',
        'production_order_bom_snapshot_id',
        'source_bom_header_id',
        'source_bom_version_number',
        'parent_product_id',
        'component_product_id',
        'unit_id',
        'line_no',
        'level',
        'quantity_per',
        'quantity_required',
        'quantity_accumulated',
        'path',
        'is_cycle',
    ];

    protected $casts = [
        'unit_id' => 'integer',
        'quantity_per' => 'float',
        'quantity_required' => 'float',
        'quantity_accumulated' => 'float',
        'is_cycle' => 'bool',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderBomSnapshotModel::class, 'production_order_bom_snapshot_id');
    }

    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }
}
