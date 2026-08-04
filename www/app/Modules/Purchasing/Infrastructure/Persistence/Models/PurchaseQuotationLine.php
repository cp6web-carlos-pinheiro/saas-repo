<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchaseQuotationLine extends TenantModel
{
    use HasFactory;

    protected $table = 'purchase_quotation_lines';

    protected $fillable = [
        'company_id',
        'purchase_quotation_id',
        'product_id',
        'purchase_requisition_line_id',
        'quantity',
        'unit_price',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'float',
        'metadata' => 'array',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(PurchaseQuotation::class, 'purchase_quotation_id');
    }

    public function requisitionLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisitionLine::class, 'purchase_requisition_line_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
