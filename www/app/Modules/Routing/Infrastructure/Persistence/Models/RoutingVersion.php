<?php

declare(strict_types=1);

namespace App\Modules\Routing\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingOperation as RoutingOperationModel;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingVersionSnapshot as RoutingVersionSnapshotModel;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class RoutingVersion extends TenantModel
{
    use HasFactory;

    protected $table = 'routing_versions';

    protected $fillable = [
        'company_id',
        'product_id',
        'version_number',
        'status',
        'effective_from',
        'effective_to',
        'description',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'approved_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(RoutingOperationModel::class, 'routing_version_id')
            ->orderBy('sequence');
    }

    public function snapshot(): HasOne
    {
        return $this->hasOne(RoutingVersionSnapshotModel::class, 'routing_version_id');
    }
}
