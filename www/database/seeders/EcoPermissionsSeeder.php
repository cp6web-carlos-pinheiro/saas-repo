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
            ['name' => 'Read ECO', 'slug' => 'eco.read', 'module' => 'audit'],
            ['name' => 'Create ECO', 'slug' => 'eco.create', 'module' => 'audit'],
            ['name' => 'Update ECO', 'slug' => 'eco.update', 'module' => 'audit'],
            ['name' => 'Submit ECO', 'slug' => 'eco.submit', 'module' => 'audit'],
            ['name' => 'Approve ECO', 'slug' => 'eco.approve', 'module' => 'audit'],
            ['name' => 'Reject ECO', 'slug' => 'eco.reject', 'module' => 'audit'],
            ['name' => 'Implement ECO', 'slug' => 'eco.implement', 'module' => 'audit'],
            ['name' => 'Read ECO Impact', 'slug' => 'eco.impact.read', 'module' => 'audit'],
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
