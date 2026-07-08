<?php

declare(strict_types=1);

namespace App\Modules\Genealogy\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class GenealogyNode extends TenantModel
{
    use HasFactory;

    protected $table = 'genealogy_nodes';

    protected $fillable = [
        'company_id',
        'node_type',
        'source_id',
        'source_reference',
        'product_id',
        'warehouse_id',
        'metadata',
    ];

    protected $casts = [
        'source_id' => 'integer',
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

    public function outgoingRelations(): HasMany
    {
        return $this->hasMany(GenealogyRelation::class, 'parent_node_id');
    }

    public function incomingRelations(): HasMany
    {
        return $this->hasMany(GenealogyRelation::class, 'child_node_id');
    }
}