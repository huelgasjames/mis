<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComputerSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $deptIds = DB::table('departments')->pluck('id', 'name')->toArray();

        $computers = [
            [
                'asset_tag' => 'ASSET-1001',
                'computer_name' => 'IT-Workstation-01',
                'category' => 'Desktop',
                'processor' => 'Intel Core i5-10400',
                'ram' => '16GB',
                'storage' => '512GB SSD',
                'serial_number' => 'SN1001',
                'status' => 'Working',
                'department_id' => $deptIds['MISD'] ?? 1, // Default to MISD or first department
            ],
            [
                'asset_tag' => 'ASSET-1002',
                'computer_name' => 'HR-Laptop-01',
                'category' => 'Laptop',
                'processor' => 'Intel Core i3-10110U',
                'ram' => '8GB',
                'storage' => '256GB SSD',
                'serial_number' => 'SN1002',
                'status' => 'Working',
                'department_id' => $deptIds['HRMD'] ?? 1, // Default to HRMD or first department
            ],
            [
                'asset_tag' => 'ASSET-1003',
                'computer_name' => 'FIN-Desktop-01',
                'category' => 'Desktop',
                'processor' => 'AMD Ryzen 5 3600',
                'ram' => '16GB',
                'storage' => '1TB HDD',
                'serial_number' => 'SN1003',
                'status' => 'Defective',
                'department_id' => $deptIds['FD'] ?? 1, // Default to FD or first department
            ],
        ];

        foreach ($computers as $c) {
            DB::table('computers')->updateOrInsert(
                ['asset_tag' => $c['asset_tag']],
                array_merge($c, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
