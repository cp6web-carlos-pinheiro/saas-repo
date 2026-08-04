<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PageTutorial extends Model
{
    use HasFactory;

    protected $table = 'page_tutorials';

    protected $fillable = [
        'route_name',
        'title',
        'content_html',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
