<?php

declare(strict_types=1);

namespace App\Modules\Routing\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingOperationSnapshot as RoutingOperationSnapshotModel;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RoutingVersionSnapshot extends TenantModel
{
    use HasFactory;

    protected $table = 'routing_version_snapshots';

    protected $fillable = [
        'company_id',
        'routing_version_id',
        'product_id',
        'version_number',
        'status',
        'effective_from',
        'effective_to',
        'description',
        'approved_by',
        'approved_at',
        'frozen_at',
        'snapshot_hash',
        'created_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'approved_at' => 'datetime',
        'frozen_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(RoutingOperationSnapshotModel::class, 'routing_version_snapshot_id')
            ->orderBy('sequence');
    }
}
