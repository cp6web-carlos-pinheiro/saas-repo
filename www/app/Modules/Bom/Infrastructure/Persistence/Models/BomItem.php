<?php

declare(strict_types=1);

namespace App\Modules\Bom\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
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
        'unit_id',
        'line_no',
        'quantity_per',
        'uom',
    ];

    protected $casts = [
        'unit_id' => 'integer',
        'quantity_per' => 'float',
    ];

    public function bomHeader(): BelongsTo
    {
        return $this->belongsTo(BomHeader::class, 'bom_header_id');
    }

    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
