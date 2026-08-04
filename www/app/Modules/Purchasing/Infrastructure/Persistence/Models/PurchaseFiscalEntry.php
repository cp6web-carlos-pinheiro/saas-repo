<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class PurchaseFiscalEntry extends TenantModel
{
    use HasFactory;

    protected $table = 'purchase_fiscal_entries';

    protected $fillable = [
        'company_id',
        'entry_number',
        'purchase_order_id',
        'supplier_id',
        'document_number',
        'issue_date',
        'entry_date',
        'status',
        'posted_by',
        'posted_at',
        'cancelled_by',
        'cancelled_at',
        'amount_cents',
        'financial_reference',
        'financial_posted_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'entry_date' => 'date',
        'posted_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'amount_cents' => 'integer',
        'financial_posted_at' => 'datetime',
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

    public function posting(): HasOne
    {
        return $this->hasOne(PurchaseFiscalEntryPosting::class, 'purchase_fiscal_entry_id');
    }
}
