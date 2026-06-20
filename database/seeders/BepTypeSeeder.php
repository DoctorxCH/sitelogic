<?php

namespace Database\Seeders;

use App\Models\BepType;
use Illuminate\Database\Seeder;

class BepTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'BEP1', 'number_of_units' => 1, 'is_active' => true],
            ['name' => 'BEP2', 'number_of_units' => 2, 'is_active' => true],
            ['name' => 'BEP3', 'number_of_units' => 3, 'is_active' => true],
            ['name' => 'BEP4', 'number_of_units' => 4, 'is_active' => true],
        ];

        foreach ($types as $type) {
            BepType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
