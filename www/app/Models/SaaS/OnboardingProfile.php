<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OnboardingProfile extends Model
{
    use HasFactory;

    protected $table = 'onboarding_profiles';

    protected $fillable = [
        'company_id',
        'user_id',
        'import_data',
        'connect_integrations',
        'invite_team',
        'progress',
        'completed_at',
    ];

    protected $casts = [
        'import_data' => 'bool',
        'connect_integrations' => 'bool',
        'invite_team' => 'bool',
        'completed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
