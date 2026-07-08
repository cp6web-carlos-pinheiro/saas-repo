<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductVersion extends TenantModel
{
    use HasFactory;

    protected $table = 'product_versions';

    protected $fillable = [
        'company_id',
        'product_id',
        'version_number',
        'status',
        'effective_from',
        'effective_to',
        'compatibility_rule',
        'change_summary',
        'payload',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'approved_at' => 'datetime',
        'payload' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
