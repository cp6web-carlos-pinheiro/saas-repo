<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PurchaseQuotation extends TenantModel
{
    use HasFactory;

    protected $table = 'purchase_quotations';

    protected $fillable = [
        'company_id',
        'quotation_number',
        'purchase_requisition_id',
        'supplier_id',
        'quotation_date',
        'valid_until',
        'status',
        'received_by',
        'received_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'amount_cents',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'received_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'amount_cents' => 'integer',
        'metadata' => 'array',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseQuotationLine::class, 'purchase_quotation_id')
            ->orderBy('id');
    }
}
