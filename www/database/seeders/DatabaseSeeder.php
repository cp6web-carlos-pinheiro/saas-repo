<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SchedulingPermissionsSeeder::class,
            BomPermissionsSeeder::class,
            MrpPermissionsSeeder::class,
            GenealogyPermissionsSeeder::class,
            EcoPermissionsSeeder::class,
            PurchasingPermissionsSeeder::class,
            RoutingPermissionsSeeder::class,
            InventoryPermissionsSeeder::class,
            ProductionPermissionsSeeder::class,
            TenantFoundationSeeder::class,
            TenantRolesSeeder::class,
        ]);
    }
}
