<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RoleTemplate extends Model
{
    use HasFactory;

    protected $table = 'role_templates';

    protected $fillable = [
        'key',
        'name',
        'module_focus',
        'is_active',
        'current_version',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'current_version' => 'int',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(RoleTemplateVersion::class);
    }
}
