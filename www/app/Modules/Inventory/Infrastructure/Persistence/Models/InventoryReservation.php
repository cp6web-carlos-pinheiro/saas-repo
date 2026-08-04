<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class InventoryReservation extends TenantModel
{
    use HasFactory;

    protected $table = 'inventory_reservations';

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'product_id',
        'reservation_origin',
        'priority',
        'quantity',
        'status',
        'reference_type',
        'reference_id',
        'reserved_at',
        'expires_at',
        'released_at',
        'created_by',
        'released_by',
        'release_reason',
        'metadata',
    ];

    protected $casts = [
        'priority' => 'integer',
        'quantity' => 'float',
        'reserved_at' => 'datetime',
        'expires_at' => 'datetime',
        'released_at' => 'datetime',
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
}