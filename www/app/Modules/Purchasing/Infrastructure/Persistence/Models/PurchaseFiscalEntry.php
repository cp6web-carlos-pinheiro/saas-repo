<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'amount_cents',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'entry_date' => 'date',
        'amount_cents' => 'integer',
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
