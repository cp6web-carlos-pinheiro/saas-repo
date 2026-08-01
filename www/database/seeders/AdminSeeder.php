<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::query()->updateOrCreate(
            ['email' => 'admin@beyondgroup.com.br'],
            [
                'name' => 'Admin Beyond Group',
                'password' => Hash::make('i14lij69M!@#'),
                'is_active' => true,
            ]
        );
    }
}
