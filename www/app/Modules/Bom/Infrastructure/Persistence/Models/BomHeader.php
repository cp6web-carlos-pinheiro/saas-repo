<?php

declare(strict_types=1);

namespace App\Modules\Bom\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class BomHeader extends TenantModel
{
    use HasFactory;

    protected $table = 'bom_headers';

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

    public function items(): HasMany
    {
        return $this->hasMany(BomItem::class, 'bom_header_id');
    }
}
