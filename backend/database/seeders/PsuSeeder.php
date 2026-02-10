<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PsuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $psus = [
            [
                'asset_tag' => 'MISD-SU-0001',
                'brand' => 'PowerLogic',
                'model' => 'TB-3C108F',
                'wattage' => '500W',
                'efficiency_rating' => '80+ Bronze',
                'form_factor' => 'ATX',
                'has_modular_cabling' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'asset_tag' => 'MISD-SU-0002',
                'brand' => 'Corsair',
                'model' => 'RM650x',
                'wattage' => '650W',
                'efficiency_rating' => '80+ Gold',
                'form_factor' => 'ATX',
                'has_modular_cabling' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('psu')->insert($psus);
    }
}
