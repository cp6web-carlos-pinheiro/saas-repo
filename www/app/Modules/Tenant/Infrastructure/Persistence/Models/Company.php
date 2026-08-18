<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Infrastructure\Persistence\Models;

use App\Models\SaaS\AccountInvitation;
use App\Models\SaaS\OnboardingProfile;
use App\Models\SaaS\Subscription;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'code',
        'slug',
        'domain',
        'segment',
        'operation_size',
        'timezone',
        'preferences',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'preferences' => 'array',
    ];

    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function latestSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany('id');
    }

    public function trials(): HasMany
    {
        return $this->hasMany(Trial::class);
    }

    public function onboardingProfiles(): HasMany
    {
        return $this->hasMany(OnboardingProfile::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(AccountInvitation::class);
    }
}
