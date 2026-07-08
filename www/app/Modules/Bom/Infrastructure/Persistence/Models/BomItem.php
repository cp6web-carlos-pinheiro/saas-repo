<?php

declare(strict_types=1);

namespace App\Modules\Bom\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BomItem extends TenantModel
{
    use HasFactory;

    protected $table = 'bom_items';

    protected $fillable = [
        'company_id',
        'bom_header_id',
        'component_product_id',
        'line_no',
        'quantity_per',
        'scrap_factor',
        'uom',
    ];

    protected $casts = [
        'quantity_per' => 'float',
        'scrap_factor' => 'float',
    ];

    public function bomHeader(): BelongsTo
    {
        return $this->belongsTo(BomHeader::class, 'bom_header_id');
    }

    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }
}
