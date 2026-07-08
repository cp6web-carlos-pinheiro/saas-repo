<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class EcoPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Read ECO', 'slug' => 'eco.read', 'module' => 'eco'],
            ['name' => 'Create ECO', 'slug' => 'eco.create', 'module' => 'eco'],
            ['name' => 'Update ECO', 'slug' => 'eco.update', 'module' => 'eco'],
            ['name' => 'Submit ECO', 'slug' => 'eco.submit', 'module' => 'eco'],
            ['name' => 'Approve ECO', 'slug' => 'eco.approve', 'module' => 'eco'],
            ['name' => 'Reject ECO', 'slug' => 'eco.reject', 'module' => 'eco'],
            ['name' => 'Implement ECO', 'slug' => 'eco.implement', 'module' => 'eco'],
            ['name' => 'Read ECO Impact', 'slug' => 'eco.impact.read', 'module' => 'eco'],
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