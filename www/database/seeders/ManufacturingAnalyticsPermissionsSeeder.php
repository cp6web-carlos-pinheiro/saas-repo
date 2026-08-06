<?php
declare(strict_types=1);
namespace Database\Seeders;
use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use Illuminate\Database\Seeder;
final class ManufacturingAnalyticsPermissionsSeeder extends Seeder
{
    public function run(): void { foreach(['analytics.manufacturing.read','analytics.standard-times.recommend','analytics.standard-times.decide','manufacturing-reports.read','manufacturing-reports.export'] as $slug){Permission::query()->updateOrCreate(['slug'=>$slug],['name'=>ucwords(str_replace(['.','-'],' ',$slug)),'module'=>'analysis']);} }
}
