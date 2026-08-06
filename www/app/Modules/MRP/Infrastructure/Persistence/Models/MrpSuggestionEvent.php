<?php

declare(strict_types=1);

namespace App\Modules\MRP\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Tenancy\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MrpSuggestionEvent extends TenantModel
{
    use HasFactory;

    protected $table = 'mrp_suggestion_events';

    protected $fillable = [
        'company_id', 'mrp_suggestion_id', 'event_type', 'from_status', 'to_status', 'created_by', 'reason', 'payload',
    ];

    protected $casts = ['payload' => 'array'];

    public function suggestion(): BelongsTo
    {
        return $this->belongsTo(MrpSuggestion::class, 'mrp_suggestion_id');
    }
}
