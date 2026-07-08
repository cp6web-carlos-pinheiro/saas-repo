<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class ProductionPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Read Production Orders', 'slug' => 'production-orders.read', 'module' => 'production'],
            ['name' => 'Create Production Orders', 'slug' => 'production-orders.create', 'module' => 'production'],
            ['name' => 'Release Production Orders', 'slug' => 'production-orders.release', 'module' => 'production'],
            ['name' => 'Record Partial Production Orders', 'slug' => 'production-orders.partial', 'module' => 'production'],
            ['name' => 'Complete Production Orders', 'slug' => 'production-orders.complete', 'module' => 'production'],
            ['name' => 'Record Material Consumption', 'slug' => 'production-orders.consumption.create', 'module' => 'production'],
            ['name' => 'Read Material Consumption', 'slug' => 'production-orders.consumption.read', 'module' => 'production'],
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
