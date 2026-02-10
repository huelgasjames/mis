<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rams = [
            [
                'asset_tag' => 'MISD-SU-0001',
                'brand' => 'Corsair',
                'model' => 'Vengeance LPX',
                'capacity' => '16GB',
                'type' => 'DDR4',
                'speed' => '3200MHz',
                'modules_count' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'asset_tag' => 'MISD-SU-0002',
                'brand' => 'G.Skill',
                'model' => 'Trident Z RGB',
                'capacity' => '32GB',
                'type' => 'DDR4',
                'speed' => '3600MHz',
                'modules_count' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('ram')->insert($rams);
    }
}
