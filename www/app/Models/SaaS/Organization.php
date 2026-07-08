<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'domain',
        'segment',
        'operation_size',
        'timezone',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Tenant\Infrastructure\Persistence\Models\Company::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function trials(): HasMany
    {
        return $this->hasMany(Trial::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
