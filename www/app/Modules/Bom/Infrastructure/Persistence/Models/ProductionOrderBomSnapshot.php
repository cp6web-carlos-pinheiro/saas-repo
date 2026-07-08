<?php

declare(strict_types=1);

namespace App\Modules\Bom\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductionOrderBomSnapshot extends TenantModel
{
    use HasFactory;

    protected $table = 'production_order_bom_snapshots';

    protected $fillable = [
        'company_id',
        'production_order_id',
        'product_id',
        'production_order_quantity',
        'reference_date',
        'source_bom_header_id',
        'source_bom_version_number',
        'snapshot_hash',
        'has_cycle',
        'frozen_at',
        'created_by',
    ];

    protected $casts = [
        'production_order_quantity' => 'float',
        'reference_date' => 'date',
        'has_cycle' => 'bool',
        'frozen_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function sourceBomHeader(): BelongsTo
    {
        return $this->belongsTo(BomHeader::class, 'source_bom_header_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductionOrderBomItemSnapshot::class, 'production_order_bom_snapshot_id');
    }
}
