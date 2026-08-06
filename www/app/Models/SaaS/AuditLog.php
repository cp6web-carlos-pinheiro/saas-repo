<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'company_id',
        'event',
        'severity',
        'ip_address',
        'user_agent',
        'context',
        'occurred_at',
    ];

    protected $casts = [
        'context' => 'array',
        'occurred_at' => 'datetime',
    ];
}
