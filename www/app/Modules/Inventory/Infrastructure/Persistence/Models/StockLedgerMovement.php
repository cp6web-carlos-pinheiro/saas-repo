<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Modules\Inventory\Infrastructure\Persistence\Models\StockLedgerAllocation as StockLedgerAllocationModel;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class StockLedgerMovement extends TenantModel
{
    use HasFactory;

    protected $table = 'stock_ledger_movements';

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'product_id',
        'movement_type',
        'source_bucket',
        'target_bucket',
        'quantity',
        'allocation_strategy',
        'lot_number',
        'expires_at',
        'reference_type',
        'reference_id',
        'notes',
        'metadata',
        'movement_at',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'float',
        'expires_at' => 'date',
        'metadata' => 'array',
        'movement_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(StockLedgerAllocationModel::class, 'issue_movement_id');
    }
}
