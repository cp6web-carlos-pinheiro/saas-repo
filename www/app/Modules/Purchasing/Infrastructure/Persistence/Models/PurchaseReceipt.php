<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'posted_by',
        'posted_at',
        'cancelled_by',
        'cancelled_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'posted_at' => 'datetime',
        'cancelled_at' => 'datetime',
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

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseReceiptLine::class, 'purchase_receipt_id')
            ->orderBy('id');
    }
}
