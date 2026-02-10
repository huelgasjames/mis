<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MotherboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $motherboards = [
            [
                'asset_tag' => 'MISD-SU-0001',
                'brand' => 'XFX',
                'model' => 'XFX-AMD-B450',
                'chipset' => 'AMD B450',
                'socket_type' => 'AM4',
                'form_factor' => 'ATX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'asset_tag' => 'MISD-SU-0002',
                'brand' => 'ASUS',
                'model' => 'PRIME B550M-A',
                'chipset' => 'AMD B550',
                'socket_type' => 'AM4',
                'form_factor' => 'Micro-ATX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('motherboard')->insert($motherboards);
    }
}
