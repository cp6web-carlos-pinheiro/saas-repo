<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Persistence\Models;

use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

final class User extends Authenticatable implements JWTSubject
{
    /**
     * @var array<int, string>
     */
    private const COMPANY_ADMIN_ROLE_SLUGS = ['master'];

    /**
     * @var array<int, string>
     */
    private const LEGACY_ADMIN_ROLE_SLUGS = ['admin', 'account-master'];

    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'preferred_locale',
        'current_company_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'preferred_locale' => 'string',
        'is_active' => 'bool',
    ];

    public function currentCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot(['company_id'])
            ->withTimestamps();
    }

    public function hasPermission(string $permissionSlug, int $companyId): bool
    {
        if ($this->isCompanyAdministrator($companyId)) {
            return true;
        }

        return $this->roles()
            ->wherePivot('company_id', $companyId)
            ->whereHas('permissions', static fn ($q) => $q->where('slug', $permissionSlug))
            ->exists();
    }

    private function isCompanyAdministrator(int $companyId): bool
    {
        return $this->roles()
            ->withoutGlobalScope('tenant')
            ->wherePivot('company_id', $companyId)
            ->where(function ($query): void {
                $query->whereIn('roles.slug', array_merge(self::COMPANY_ADMIN_ROLE_SLUGS, self::LEGACY_ADMIN_ROLE_SLUGS))
                    ->orWhere('roles.slug', 'like', 'user-access-%-administrator');
            })
            ->exists();
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'company_id' => $this->current_company_id,
            'email' => $this->email,
        ];
    }
}
