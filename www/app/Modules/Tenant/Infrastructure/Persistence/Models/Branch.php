<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Branch extends TenantModel
{
    use HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class, 'branch_id');
    }
}
