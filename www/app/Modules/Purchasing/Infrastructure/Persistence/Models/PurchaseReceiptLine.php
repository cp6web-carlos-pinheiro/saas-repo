<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchaseReceiptLine extends TenantModel
{
    use HasFactory;

    protected $table = 'purchase_receipt_lines';

    protected $fillable = [
        'company_id',
        'purchase_receipt_id',
        'purchase_order_line_id',
        'product_id',
        'warehouse_id',
        'quantity_received',
        'lot_number',
        'stock_ledger_movement_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'quantity_received' => 'float',
        'metadata' => 'array',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function orderLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLine::class, 'purchase_order_line_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
