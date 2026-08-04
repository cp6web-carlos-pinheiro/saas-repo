<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchaseReceipt extends TenantModel
{
    use HasFactory;

    protected $table = 'purchase_receipts';

    protected $fillable = [
        'company_id',
        'receipt_number',
        'purchase_order_id',
        'supplier_id',
        'receipt_date',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'metadata' => 'array',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
