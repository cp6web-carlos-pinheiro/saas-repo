<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class InventoryBalance extends TenantModel
{
    use HasFactory;

    protected $table = 'inventory_balances';

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'product_id',
        'qty_available',
        'qty_reserved',
        'qty_in_transit',
        'qty_inspection',
        'last_movement_at',
    ];

    protected $casts = [
        'qty_available' => 'float',
        'qty_reserved' => 'float',
        'qty_in_transit' => 'float',
        'qty_inspection' => 'float',
        'last_movement_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
