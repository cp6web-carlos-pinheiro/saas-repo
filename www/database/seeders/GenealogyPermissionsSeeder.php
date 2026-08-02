<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class GenealogyPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Trace Genealogy', 'slug' => 'genealogy.trace', 'module' => 'audit'],
            ['name' => 'Create Genealogy Relations', 'slug' => 'genealogy.relations.create', 'module' => 'audit'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'module' => $permission['module'],
                ]
            );
        }
    }
}