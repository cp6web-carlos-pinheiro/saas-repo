<?php

declare(strict_types=1);

namespace App\Modules\Sales\Infrastructure\Persistence\Models;

use App\Modules\Customer\Infrastructure\Persistence\Models\Customer;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Sale extends TenantModel
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'company_id',
        'customer_id',
        'sale_date',
        'status',
        'operational_status',
        'picking_by',
        'picking_at',
        'invoiced_by',
        'invoiced_at',
        'shipped_by',
        'shipped_at',
        'delivered_by',
        'delivered_at',
        'subtotal_cents',
        'discount_cents',
        'tax_cents',
        'amount_cents',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'picking_at' => 'datetime',
        'invoiced_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'subtotal_cents' => 'integer',
        'discount_cents' => 'integer',
        'tax_cents' => 'integer',
        'amount_cents' => 'integer',
        'metadata' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SaleLine::class, 'sale_id')->orderBy('id');
    }

    public function pickingBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'picking_by');
    }

    public function invoicedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invoiced_by');
    }

    public function shippedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }
}