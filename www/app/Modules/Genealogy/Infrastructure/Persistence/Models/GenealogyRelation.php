<?php

declare(strict_types=1);

namespace App\Modules\Genealogy\Infrastructure\Persistence\Models;

use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Inventory\Infrastructure\Persistence\Models\StockLedgerMovement;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GenealogyRelation extends TenantModel
{
    use HasFactory;

    protected $table = 'genealogy_relations';

    protected $fillable = [
        'company_id',
        'parent_node_id',
        'child_node_id',
        'relation_type',
        'quantity',
        'uom',
        'production_order_id',
        'stock_movement_id',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'float',
        'metadata' => 'array',
    ];

    public function parentNode(): BelongsTo
    {
        return $this->belongsTo(GenealogyNode::class, 'parent_node_id');
    }

    public function childNode(): BelongsTo
    {
        return $this->belongsTo(GenealogyNode::class, 'child_node_id');
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockLedgerMovement::class, 'stock_movement_id');
    }
}