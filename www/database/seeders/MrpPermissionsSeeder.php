<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class MrpPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Permission::query()->updateOrCreate(
            ['slug' => 'mrp.plan'],
            [
                'name' => 'Run MRP Planning',
                'module' => 'mrp',
            ]
        );
    }
}