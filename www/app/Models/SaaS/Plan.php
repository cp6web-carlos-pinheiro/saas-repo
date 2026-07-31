<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    protected $fillable = [
        'code',
        'label',
        'description',
        'payment_method',
        'billing_cycle_label',
        'amount_cents',
        'trial_days',
        'interval_months',
        'renewable',
        'allow_once',
        'default_status',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'trial_days' => 'integer',
        'interval_months' => 'integer',
        'amount_cents' => 'integer',
        'renewable' => 'boolean',
        'allow_once' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_code', 'code');
    }
}
