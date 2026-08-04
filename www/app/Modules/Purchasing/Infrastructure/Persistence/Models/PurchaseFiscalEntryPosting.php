<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchaseFiscalEntryPosting extends TenantModel
{
    use HasFactory;

    protected $table = 'purchase_fiscal_entry_postings';

    protected $fillable = [
        'company_id',
        'purchase_fiscal_entry_id',
        'status',
        'financial_reference',
        'posted_by',
        'posted_at',
        'reversed_by',
        'reversed_at',
        'payload',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
        'payload' => 'array',
    ];

    public function fiscalEntry(): BelongsTo
    {
        return $this->belongsTo(PurchaseFiscalEntry::class, 'purchase_fiscal_entry_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }
}
