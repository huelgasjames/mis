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
                'department_id' => $deptIds['MISD'] ?? 1,
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
                'department_id' => $deptIds['MISD'] ?? 1,
            ],
            [
                'asset_tag' => 'HRMD-LP-0001',
                'computer_name' => 'HR-Laptop-01',
                'category' => 'Laptop',
                'processor' => 'Intel Core i5-1135G7',
                'motherboard' => 'HP OEM',
                'video_card' => 'Intel Iris Xe',
                'dvd_rom' => 'N/A',
                'psu' => 'Integrated',
                'ram' => '8GB',
                'storage' => '256GB SSD',
                'serial_number' => 'HP-8BG1234',
                'status' => 'Working',
                'department_id' => $deptIds['HRMD'] ?? 2,
            ],
            [
                'asset_tag' => 'FD-DT-0001',
                'computer_name' => 'Finance-Desktop',
                'category' => 'Desktop',
                'processor' => 'Intel Core i3-10100',
                'motherboard' => 'Gigabyte H410M',
                'video_card' => 'N/A',
                'dvd_rom' => 'LG DVD-ROM',
                'psu' => 'Cooler Master 500W',
                'ram' => '16GB',
                'storage' => '1TB HDD',
                'serial_number' => 'FIN-001',
                'status' => 'Defective',
                'department_id' => $deptIds['FD'] ?? 3,
            ],
            [
                'asset_tag' => 'CCS-SRV-0001',
                'computer_name' => 'CCS-Server-01',
                'category' => 'Server',
                'processor' => 'Intel Xeon E-2288G',
                'motherboard' => 'Supermicro X11SCA',
                'video_card' => 'Matrox G200e',
                'dvd_rom' => 'N/A',
                'psu' => '冗余 800W',
                'ram' => '64GB',
                'storage' => '2TB RAID 10',
                'serial_number' => 'SRV-CCS-001',
                'status' => 'Working',
                'department_id' => $deptIds['CCS'] ?? 4,
            ],
        ];

        foreach ($assets as $asset) {
            DB::table('assets')->updateOrInsert(
                ['asset_tag' => $asset['asset_tag']],
                array_merge($asset, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
