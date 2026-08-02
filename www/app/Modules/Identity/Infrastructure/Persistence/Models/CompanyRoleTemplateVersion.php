<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Persistence\Models;

use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CompanyRoleTemplateVersion extends TenantModel
{
    use HasFactory;

    protected $table = 'company_role_template_versions';

    protected $fillable = [
        'company_id',
        'role_template_id',
        'role_id',
        'applied_version',
        'applied_by_user_id',
        'applied_at',
    ];

    protected $casts = [
        'applied_version' => 'int',
        'applied_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(RoleTemplate::class, 'role_template_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function applier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by_user_id');
    }
}
