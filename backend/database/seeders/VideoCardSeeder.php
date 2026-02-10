<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VideoCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $videoCards = [
            [
                'asset_tag' => 'MISD-SU-0001',
                'brand' => 'N/A',
                'model' => 'Integrated Graphics',
                'memory' => 'Shared',
                'memory_type' => 'DDR4',
                'interface' => 'PCIe 3.0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'asset_tag' => 'MISD-SU-0002',
                'brand' => 'NVIDIA',
                'model' => 'GeForce RTX 3060',
                'memory' => '12GB',
                'memory_type' => 'GDDR6',
                'interface' => 'PCIe 4.0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('video_card')->insert($videoCards);
    }
}
