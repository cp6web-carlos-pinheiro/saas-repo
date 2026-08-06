<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Trial extends Model
{
    use HasFactory;

    protected $table = 'trials';

    protected $fillable = [
        'user_id',
        'company_id',
        'trial_start_date',
        'trial_end_date',
        'grace_ends_at',
        'status',
        'expired_at',
        'is_expired',
        'email_domain',
        'registration_ip',
    ];

    protected $casts = [
        'trial_start_date' => 'datetime',
        'trial_end_date' => 'datetime',
        'grace_ends_at' => 'datetime',
        'expired_at' => 'datetime',
        'is_expired' => 'bool',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function daysRemaining(): int
    {
        $end = CarbonImmutable::instance($this->trial_end_date);
        $days = CarbonImmutable::now()->startOfDay()->diffInDays($end->startOfDay(), false);

        return (int) max(0, $days);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->is_expired && $this->trial_end_date->isFuture();
    }
}
