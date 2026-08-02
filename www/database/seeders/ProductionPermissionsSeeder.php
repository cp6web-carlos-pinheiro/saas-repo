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
            ['name' => 'Read Production Orders', 'slug' => 'production-orders.read', 'module' => 'production_mrp'],
            ['name' => 'Create Production Orders', 'slug' => 'production-orders.create', 'module' => 'production_mrp'],
            ['name' => 'Release Production Orders', 'slug' => 'production-orders.release', 'module' => 'production_mrp'],
            ['name' => 'Record Partial Production Orders', 'slug' => 'production-orders.partial', 'module' => 'production_mrp'],
            ['name' => 'Complete Production Orders', 'slug' => 'production-orders.complete', 'module' => 'production_mrp'],
            ['name' => 'Record Material Consumption', 'slug' => 'production-orders.consumption.create', 'module' => 'production_mrp'],
            ['name' => 'Read Material Consumption', 'slug' => 'production-orders.consumption.read', 'module' => 'production_mrp'],
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
