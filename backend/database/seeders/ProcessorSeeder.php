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
            [
                'asset_tag' => 'MISD-SU-0002',
                'brand' => 'AMD',
                'model' => 'Ryzen 7 5800X',
                'speed' => '3.8 GHz',
                'cores' => '8',
                'threads' => '16',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'asset_tag' => 'MISD-LAB-0001',
                'brand' => 'Intel',
                'model' => 'Core i5-12400',
                'speed' => '2.5 GHz',
                'cores' => '6',
                'threads' => '12',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'asset_tag' => 'MISD-SYS-0001',
                'brand' => 'Intel',
                'model' => 'Xeon Silver 4210R',
                'speed' => '2.4 GHz',
                'cores' => '10',
                'threads' => '20',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'asset_tag' => 'MISD-NET-0001',
                'brand' => 'Intel',
                'model' => 'Xeon D-1521',
                'speed' => '1.7 GHz',
                'cores' => '4',
                'threads' => '8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('processor')->insert($processors);
    }
}
