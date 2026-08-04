<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Supplier extends TenantModel
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'person_type',
        'tax_id',
        'email',
        'phone',
        'status',
        'default_lead_time_days',
        'payment_terms',
        'default_cfop_id',
        'tax_profile_id',
        'metadata',
    ];

    protected $casts = [
        'default_lead_time_days' => 'integer',
        'metadata' => 'array',
    ];

    public function productRules(): HasMany
    {
        return $this->hasMany(SupplierProduct::class, 'supplier_id');
    }
}
