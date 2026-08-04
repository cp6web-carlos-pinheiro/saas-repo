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
            ['name' => 'Read Branches', 'slug' => 'admin-data.branches.read', 'module' => 'inventory'],
            ['name' => 'Create Branches', 'slug' => 'admin-data.branches.create', 'module' => 'inventory'],
            ['name' => 'Update Branches', 'slug' => 'admin-data.branches.update', 'module' => 'inventory'],
            ['name' => 'Read Warehouse Locations', 'slug' => 'admin-data.warehouse-locations.read', 'module' => 'inventory'],
            ['name' => 'Create Warehouse Locations', 'slug' => 'admin-data.warehouse-locations.create', 'module' => 'inventory'],
            ['name' => 'Update Warehouse Locations', 'slug' => 'admin-data.warehouse-locations.update', 'module' => 'inventory'],
            ['name' => 'Read Departments', 'slug' => 'admin-data.departments.read', 'module' => 'inventory'],
            ['name' => 'Create Departments', 'slug' => 'admin-data.departments.create', 'module' => 'inventory'],
            ['name' => 'Update Departments', 'slug' => 'admin-data.departments.update', 'module' => 'inventory'],
            ['name' => 'Read Cost Centers', 'slug' => 'admin-data.cost-centers.read', 'module' => 'inventory'],
            ['name' => 'Create Cost Centers', 'slug' => 'admin-data.cost-centers.create', 'module' => 'inventory'],
            ['name' => 'Update Cost Centers', 'slug' => 'admin-data.cost-centers.update', 'module' => 'inventory'],
            ['name' => 'Read Units', 'slug' => 'admin-data.units.read', 'module' => 'inventory'],
            ['name' => 'Create Units', 'slug' => 'admin-data.units.create', 'module' => 'inventory'],
            ['name' => 'Update Units', 'slug' => 'admin-data.units.update', 'module' => 'inventory'],
            ['name' => 'Read Categories', 'slug' => 'admin-data.categories.read', 'module' => 'inventory'],
            ['name' => 'Create Categories', 'slug' => 'admin-data.categories.create', 'module' => 'inventory'],
            ['name' => 'Update Categories', 'slug' => 'admin-data.categories.update', 'module' => 'inventory'],
            ['name' => 'Read Brands', 'slug' => 'admin-data.brands.read', 'module' => 'inventory'],
            ['name' => 'Create Brands', 'slug' => 'admin-data.brands.create', 'module' => 'inventory'],
            ['name' => 'Update Brands', 'slug' => 'admin-data.brands.update', 'module' => 'inventory'],
            ['name' => 'Read NCM', 'slug' => 'admin-data.ncms.read', 'module' => 'inventory'],
            ['name' => 'Create NCM', 'slug' => 'admin-data.ncms.create', 'module' => 'inventory'],
            ['name' => 'Update NCM', 'slug' => 'admin-data.ncms.update', 'module' => 'inventory'],
            ['name' => 'Read CFOP', 'slug' => 'admin-data.cfops.read', 'module' => 'inventory'],
            ['name' => 'Create CFOP', 'slug' => 'admin-data.cfops.create', 'module' => 'inventory'],
            ['name' => 'Update CFOP', 'slug' => 'admin-data.cfops.update', 'module' => 'inventory'],
            ['name' => 'Read Taxes', 'slug' => 'admin-data.taxes.read', 'module' => 'inventory'],
            ['name' => 'Create Taxes', 'slug' => 'admin-data.taxes.create', 'module' => 'inventory'],
            ['name' => 'Update Taxes', 'slug' => 'admin-data.taxes.update', 'module' => 'inventory'],
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
