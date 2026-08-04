<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class InventoryPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Read Inventory', 'slug' => 'inventory.read', 'module' => 'inventory'],
            ['name' => 'Update Inventory', 'slug' => 'inventory.update', 'module' => 'inventory'],
            ['name' => 'Read Plants', 'slug' => 'inventory.plants.read', 'module' => 'inventory'],
            ['name' => 'Create Plants', 'slug' => 'inventory.plants.create', 'module' => 'inventory'],
            ['name' => 'Update Plants', 'slug' => 'inventory.plants.update', 'module' => 'inventory'],
            ['name' => 'Read Warehouses', 'slug' => 'inventory.warehouses.read', 'module' => 'inventory'],
            ['name' => 'Create Warehouses', 'slug' => 'inventory.warehouses.create', 'module' => 'inventory'],
            ['name' => 'Update Warehouses', 'slug' => 'inventory.warehouses.update', 'module' => 'inventory'],
            ['name' => 'Read Inventory Lots', 'slug' => 'inventory.lots.read', 'module' => 'inventory'],
            ['name' => 'Create Inventory Lots', 'slug' => 'inventory.lots.create', 'module' => 'inventory'],
            ['name' => 'Trace Inventory Lots', 'slug' => 'inventory.lots.trace', 'module' => 'inventory'],
            ['name' => 'Read Inventory Serials', 'slug' => 'inventory.serials.read', 'module' => 'inventory'],
            ['name' => 'Create Inventory Serials', 'slug' => 'inventory.serials.create', 'module' => 'inventory'],
            ['name' => 'Trace Inventory Serials', 'slug' => 'inventory.serials.trace', 'module' => 'inventory'],
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
