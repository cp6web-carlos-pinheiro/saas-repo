<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GlobalUnitsSeeder::class,
            AdminSeeder::class,
            PlanSeeder::class,
            SchedulingPermissionsSeeder::class,
            EngineeringResourcesPermissionsSeeder::class,
            BomPermissionsSeeder::class,
            MrpPermissionsSeeder::class,
            GenealogyPermissionsSeeder::class,
            EcoPermissionsSeeder::class,
            PurchasingPermissionsSeeder::class,
            RoutingPermissionsSeeder::class,
            InventoryPermissionsSeeder::class,
            ProductionPermissionsSeeder::class,
            CompanyAccessPermissionsSeeder::class,
            TenantFoundationSeeder::class,
            TenantRolesSeeder::class,
            PageTutorialSeeder::class,
        ]);
    }
}
