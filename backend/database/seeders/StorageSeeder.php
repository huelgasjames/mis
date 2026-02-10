<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $storages = [
            [
                'asset_tag' => 'MISD-SU-0001',
                'brand' => 'Samsung',
                'model' => '970 EVO Plus',
                'type' => 'SSD',
                'capacity' => '512GB',
                'interface' => 'NVMe',
                'form_factor' => 'M.2',
                'rpm' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'asset_tag' => 'MISD-SU-0002',
                'brand' => 'Seagate',
                'model' => 'Barracuda',
                'type' => 'HDD',
                'capacity' => '1TB',
                'interface' => 'SATA',
                'form_factor' => '3.5"',
                'rpm' => 7200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('storage')->insert($storages);
    }
}
