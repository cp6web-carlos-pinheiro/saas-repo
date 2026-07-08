<?php

declare(strict_types=1);

namespace App\Modules\Production\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionOrderMaterialConsumption extends TenantModel
{
    use HasFactory;

    protected $table = 'production_order_material_consumptions';

    protected $fillable = [
        'company_id',
        'production_order_id',
        'product_id',
        'warehouse_id',
        'lot_number',
        'quantity_consumed',
        'quantity_scrapped',
        'ledger_movement_id',
        'reference_bom_component_id',
        'consumed_at',
        'operator_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'quantity_consumed' => 'float',
        'quantity_scrapped' => 'float',
        'consumed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
