<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Persistence\Models;

use App\Modules\Inventory\Infrastructure\Persistence\Models\StockLedgerMovement as StockLedgerMovementModel;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class InventoryLot extends TenantModel
{
    use HasFactory;

    protected $table = 'inventory_lots';

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'product_id',
        'lot_number',
        'manufactured_at',
        'expires_at',
        'status',
        'source_movement_id',
        'metadata',
    ];

    protected $casts = [
        'manufactured_at' => 'date',
        'expires_at' => 'date',
        'metadata' => 'array',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function serials(): HasMany
    {
        return $this->hasMany(InventorySerial::class, 'inventory_lot_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockLedgerMovementModel::class, 'product_id', 'product_id');
    }
}
