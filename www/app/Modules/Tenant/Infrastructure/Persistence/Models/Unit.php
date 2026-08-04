<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class Unit extends TenantModel
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'metadata' => 'array',
    ];
}
