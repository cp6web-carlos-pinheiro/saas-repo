<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Persistence\Models;

use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PermissionUserOverride extends TenantModel
{
    use HasFactory;

    protected $table = 'permission_user_overrides';

    protected $fillable = [
        'company_id',
        'user_id',
        'permission_id',
        'is_allowed',
        'reason',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_allowed' => 'bool',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
