<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Tenant\Infrastructure\Persistence\Models\Unit;
use Illuminate\Database\Seeder;

final class GlobalUnitsSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['code' => 'UN', 'name' => 'Unidade'],
            ['code' => 'PC', 'name' => 'Peca'],
            ['code' => 'KG', 'name' => 'Quilograma'],
            ['code' => 'G', 'name' => 'Grama'],
            ['code' => 'MG', 'name' => 'Miligramas'],
            ['code' => 'T', 'name' => 'Tonelada'],
            ['code' => 'L', 'name' => 'Litro'],
            ['code' => 'ML', 'name' => 'Mililitro'],
            ['code' => 'M3', 'name' => 'Metro cubico'],
            ['code' => 'M2', 'name' => 'Metro quadrado'],
            ['code' => 'M', 'name' => 'Metro'],
            ['code' => 'CM', 'name' => 'Centimetro'],
            ['code' => 'MM', 'name' => 'Milimetro'],
            ['code' => 'KM', 'name' => 'Quilometro'],
            ['code' => 'CX', 'name' => 'Caixa'],
            ['code' => 'PCT', 'name' => 'Pacote'],
            ['code' => 'FD', 'name' => 'Fardo'],
            ['code' => 'SC', 'name' => 'Saco'],
            ['code' => 'PAL', 'name' => 'Palete'],
            ['code' => 'RL', 'name' => 'Rolo'],
            ['code' => 'PAR', 'name' => 'Par'],
            ['code' => 'JG', 'name' => 'Jogo'],
            ['code' => 'H', 'name' => 'Hora'],
            ['code' => 'MIN', 'name' => 'Minuto'],
        ];

        foreach ($units as $unit) {
            Unit::query()->withoutGlobalScope('tenant')->updateOrCreate(
                [
                    'company_id' => null,
                    'code' => $unit['code'],
                ],
                [
                    'name' => $unit['name'],
                    'description' => 'Unidade global padrao',
                    'is_active' => true,
                    'created_by' => null,
                    'updated_by' => null,
                    'metadata' => ['scope' => 'global'],
                ]
            );
        }
    }
}
