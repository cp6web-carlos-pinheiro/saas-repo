<?php

declare(strict_types=1);

namespace App\Modules\Sales\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SaleLine extends TenantModel
{
    use HasFactory;

    protected $table = 'sale_lines';

    protected $fillable = [
        'company_id',
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'float',
        'metadata' => 'array',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}