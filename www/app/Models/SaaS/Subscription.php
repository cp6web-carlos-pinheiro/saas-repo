<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'company_id',
        'trial_id',
        'provider',
        'provider_customer_id',
        'provider_subscription_id',
        'plan_code',
        'status',
        'starts_at',
        'ends_at',
        'canceled_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class);
    }
}
