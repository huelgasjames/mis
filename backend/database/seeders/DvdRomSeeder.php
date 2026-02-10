<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DvdRomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dvdRoms = [
            [
                'asset_tag' => 'MISD-SU-0001',
                'brand' => 'N/A',
                'model' => 'No DVD ROM',
                'type' => 'None',
                'speed' => 'N/A',
                'has_writer' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'asset_tag' => 'MISD-SU-0002',
                'brand' => 'LG',
                'model' => 'GH24NSD1',
                'type' => 'DVD-RW',
                'speed' => '24x',
                'has_writer' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('dvd_rom')->insert($dvdRoms);
    }
}
