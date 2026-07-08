<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OnboardingProfile extends Model
{
    use HasFactory;

    protected $table = 'onboarding_profiles';

    protected $fillable = [
        'organization_id',
        'user_id',
        'segment',
        'operation_size',
        'timezone',
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

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
