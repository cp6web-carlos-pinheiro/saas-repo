<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

final class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'slug',
        'module',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')
            ->withTimestamps();
    }

    public function userOverrides(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PermissionUserOverride::class, 'permission_id');
    }

    public function label(): string
    {
        $key = 'permissions.slugs.'.$this->slug;
        $translated = __($key);

        return $translated !== $key ? $translated : $this->name;
    }

    public static function moduleLabel(string $module): string
    {
        $key = 'permissions.modules.'.$module;
        $translated = __($key);

        return $translated !== $key ? $translated : Str::headline($module);
    }

    public function description(): string
    {
        $key = 'permissions.descriptions.'.$this->slug;
        $translated = __($key);

        return $translated !== $key ? $translated : $this->name;
    }

    public static function moduleDescription(string $module): string
    {
        $key = 'permissions.module_descriptions.'.$module;
        $translated = __($key);

        return $translated !== $key ? $translated : '';
    }
}
