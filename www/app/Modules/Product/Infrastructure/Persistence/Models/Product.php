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
        'unit_id',
        'category_id',
        'brand_id',
        'ncm_id',
        'safety_stock',
        'lead_time_days',
        'lot_control',
        'serial_control',
        'is_active',
        'lifecycle_status',
        'technical_attributes',
        'commercial_attributes',
        'fiscal_attributes',
        'alternate_uoms',
        'image_urls',
        'attachment_urls',
    ];

    protected $casts = [
        'safety_stock' => 'integer',
        'lead_time_days' => 'integer',
        'lot_control' => 'bool',
        'serial_control' => 'bool',
        'is_active' => 'bool',
        'technical_attributes' => 'array',
        'commercial_attributes' => 'array',
        'fiscal_attributes' => 'array',
        'alternate_uoms' => 'array',
        'image_urls' => 'array',
        'attachment_urls' => 'array',
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
