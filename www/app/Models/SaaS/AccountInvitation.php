<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AccountInvitation extends Model
{
    use HasFactory;

    protected $table = 'account_invitations';

    protected $fillable = [
        'company_id',
        'organization_id',
        'invited_by_user_id',
        'accepted_by_user_id',
        'email',
        'name',
        'role_slug',
        'token',
        'expires_at',
        'sent_at',
        'accepted_at',
        'revoked_at',
        'meta',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'meta' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }
}
