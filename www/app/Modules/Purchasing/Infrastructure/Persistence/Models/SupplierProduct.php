<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SupplierProduct extends TenantModel
{
    use HasFactory;

    protected $table = 'supplier_products';

    protected $fillable = [
        'company_id',
        'supplier_id',
        'product_id',
        'supplier_sku',
        'moq',
        'lead_time_days',
        'unit_price',
        'is_preferred',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'moq' => 'float',
        'lead_time_days' => 'integer',
        'unit_price' => 'float',
        'is_preferred' => 'bool',
        'is_active' => 'bool',
        'metadata' => 'array',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
