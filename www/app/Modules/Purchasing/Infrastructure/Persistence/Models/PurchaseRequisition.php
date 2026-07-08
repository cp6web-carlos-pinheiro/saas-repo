<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PurchaseRequisition extends TenantModel
{
    use HasFactory;

    protected $table = 'purchase_requisitions';

    protected $fillable = [
        'company_id',
        'requisition_number',
        'status',
        'required_date',
        'source_type',
        'source_reference_id',
        'source_reference_type',
        'requested_by',
        'approved_by',
        'approved_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'required_date' => 'date',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionLine::class, 'purchase_requisition_id')
            ->orderBy('id');
    }
}
