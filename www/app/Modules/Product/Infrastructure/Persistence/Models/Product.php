<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Persistence\Models;

use App\Modules\Bom\Infrastructure\Persistence\Models\BomHeader;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Product extends TenantModel
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'company_id',
        'sku',
        'description',
        'product_type',
        'uom',
        'safety_stock',
        'lead_time_days',
        'lot_control',
        'serial_control',
        'is_active',
    ];

    protected $casts = [
        'safety_stock' => 'integer',
        'lead_time_days' => 'integer',
        'lot_control' => 'bool',
        'serial_control' => 'bool',
        'is_active' => 'bool',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(ProductVersion::class);
    }

    public function bomHeaders(): HasMany
    {
        return $this->hasMany(BomHeader::class);
    }
}
