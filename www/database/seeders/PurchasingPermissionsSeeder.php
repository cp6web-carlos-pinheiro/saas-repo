<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;

final class PurchasingPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Read Suppliers', 'slug' => 'purchasing.suppliers.read', 'module' => 'purchasing'],
            ['name' => 'Create Suppliers', 'slug' => 'purchasing.suppliers.create', 'module' => 'purchasing'],
            ['name' => 'Update Suppliers', 'slug' => 'purchasing.suppliers.update', 'module' => 'purchasing'],
            ['name' => 'Manage Supplier Product Rules', 'slug' => 'purchasing.suppliers.rules.manage', 'module' => 'purchasing'],
            ['name' => 'Read Requisitions', 'slug' => 'purchasing.requisitions.read', 'module' => 'purchasing'],
            ['name' => 'Create Requisitions', 'slug' => 'purchasing.requisitions.create', 'module' => 'purchasing'],
            ['name' => 'Create Requisitions from MRP', 'slug' => 'purchasing.requisitions.from-mrp', 'module' => 'purchasing'],
            ['name' => 'Convert Requisitions to PO', 'slug' => 'purchasing.requisitions.convert', 'module' => 'purchasing'],
            ['name' => 'Read Purchase Orders', 'slug' => 'purchasing.orders.read', 'module' => 'purchasing'],
            ['name' => 'Approve Purchase Orders', 'slug' => 'purchasing.orders.approve', 'module' => 'purchasing'],
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
