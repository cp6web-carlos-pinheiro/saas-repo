<?php

declare(strict_types=1);

namespace App\Modules\Customer\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class Customer extends TenantModel
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'person_type',
        'tax_id',
        'email',
        'phone',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
