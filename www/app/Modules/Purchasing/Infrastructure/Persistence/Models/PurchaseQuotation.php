<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'amount_cents',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
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
}
