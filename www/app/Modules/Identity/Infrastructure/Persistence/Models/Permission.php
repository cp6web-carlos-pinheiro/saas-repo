<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Lang;
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

    public function label(): string
    {
        $translations = Lang::get('permissions.slugs');

        if (is_array($translations) && array_key_exists($this->slug, $translations)) {
            return (string) $translations[$this->slug];
        }

        return $this->name;
    }

    public static function moduleLabel(string $module): string
    {
        $key = 'permissions.modules.'.$module;
        $translated = __($key);

        return $translated !== $key ? $translated : Str::headline($module);
    }

    public function description(): string
    {
        $translations = Lang::get('permissions.descriptions');

        if (is_array($translations) && array_key_exists($this->slug, $translations)) {
            return (string) $translations[$this->slug];
        }

        return $this->name;
    }

    public static function moduleDescription(string $module): string
    {
        $key = 'permissions.module_descriptions.'.$module;
        $translated = __($key);

        return $translated !== $key ? $translated : '';
    }
}
