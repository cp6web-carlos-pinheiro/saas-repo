<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Role extends TenantModel
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Tenant\Infrastructure\Persistence\Models\Company::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot(['company_id'])
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
            ->withTimestamps();
    }
}
