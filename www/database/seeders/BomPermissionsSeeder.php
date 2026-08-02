<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class BomPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Permission::query()->updateOrCreate(
            ['slug' => 'bom.explode'],
            [
                'name' => 'Explode BOM',
                'module' => 'products',
            ]
        );
    }
}
