<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Get department IDs
        $deptIds = DB::table('departments')->pluck('id', 'name')->toArray();

        // Check if departments exist
        if (empty($deptIds)) {
            $this->command->warn('No departments found. Please run DepartmentSeeder first.');
            return;
        }

        // Get first available department as fallback
        $firstDeptId = reset($deptIds);

        $assets = [
            [
                'asset_tag' => 'MISD-SU-0001',
                'computer_name' => 'POWERLOGIC',
                'category' => 'Desktop',
                'processor' => 'Intel Core i5-10400',
                'motherboard' => 'XFX',
                'video_card' => 'N/A',
                'dvd_rom' => 'N/A',
                'psu' => 'TB-3C108F',
                'ram' => '16GB',
                'storage' => '512GB SSD',
                'serial_number' => 'SPHV',
                'status' => 'Working',
                'department_id' => $deptIds['MISD'] ?? $firstDeptId,
            ],
            [
                'asset_tag' => 'MISD-SU-0002',
                'computer_name' => 'Gaming-Rig-01',
                'category' => 'Desktop',
                'processor' => 'AMD Ryzen 7 5800X',
                'motherboard' => 'ASUS ROG Strix',
                'video_card' => 'NVIDIA RTX 3070',
                'dvd_rom' => 'Samsung DVD-RW',
                'psu' => 'Corsair RM750x',
                'ram' => '32GB',
                'storage' => '1TB NVMe SSD',
                'serial_number' => 'RTX3070-001',
                'status' => 'Working',
                'department_id' => $deptIds['MISD'] ?? $firstDeptId,
            ],
            [
                'asset_tag' => 'MISD-LAB-0001',
                'computer_name' => 'MISD-LAB-PC001',
                'category' => 'Desktop',
                'processor' => 'Intel Core i5-12400',
                'motherboard' => 'Dell OptiPlex 7090',
                'video_card' => 'Intel UHD Graphics 730',
                'dvd_rom' => 'N/A',
                'psu' => '260W',
                'ram' => '16GB',
                'storage' => '512GB SSD',
                'serial_number' => 'MISD-LAB-001',
                'status' => 'Working',
                'department_id' => $deptIds['MISD - Computer Laboratory'] ?? $firstDeptId,
            ],
            [
                'asset_tag' => 'MISD-SYS-0001',
                'computer_name' => 'MISD-SYS-SRV001',
                'category' => 'Server',
                'processor' => 'Intel Xeon Silver 4210R',
                'motherboard' => 'Dell PowerEdge R740',
                'video_card' => 'Matrox G200eR3',
                'dvd_rom' => 'N/A',
                'psu' => '750W Redundant',
                'ram' => '64GB DDR4 ECC',
                'storage' => '2x 1TB SAS HDD RAID 1',
                'serial_number' => 'MISD-SYS-001',
                'status' => 'Working',
                'department_id' => $deptIds['MISD - Technical Support'] ?? $firstDeptId,
            ],
            [
                'asset_tag' => 'MISD-NET-0001',
                'computer_name' => 'MISD-NET-RT001',
                'category' => 'Server',
                'processor' => 'Intel Xeon D-1521',
                'motherboard' => 'Cisco ISR 4321',
                'video_card' => 'N/A',
                'dvd_rom' => 'N/A',
                'psu' => 'Internal PSU',
                'ram' => '4GB DDR3',
                'storage' => '8GB Flash',
                'serial_number' => 'MISD-NET-001',
                'status' => 'Working',
                'department_id' => $deptIds['MISD - Network Operations'] ?? $firstDeptId,
            ],
        ];

        foreach ($assets as $asset) {
            DB::table('assets')->updateOrInsert(
                ['asset_tag' => $asset['asset_tag']],
                array_merge($asset, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $this->command->info('Assets seeded successfully!');
    }
}  
