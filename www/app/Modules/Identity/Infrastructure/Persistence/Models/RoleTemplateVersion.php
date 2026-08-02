<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RoleTemplateVersion extends Model
{
    use HasFactory;

    protected $table = 'role_template_versions';

    protected $fillable = [
        'role_template_id',
        'version',
        'display_name',
        'permissions',
        'notes',
        'published_by_user_id',
        'published_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'version' => 'int',
        'published_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(RoleTemplate::class, 'role_template_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by_user_id');
    }
}
