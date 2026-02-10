<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcessorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $processors = [
            [
                'asset_tag' => 'MISD-SU-0001',
                'brand' => 'Intel',
                'model' => 'Core i5-10400',
                'speed' => '2.9 GHz',
                'cores' => '6',
                'threads' => '12',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more sample processors as needed
            [
                'asset_tag' => 'MISD-SU-0002',
                'brand' => 'AMD',
                'model' => 'Ryzen 5 5600X',
                'speed' => '3.7 GHz',
                'cores' => '6',
                'threads' => '12',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('processor')->insert($processors);
    }
}
