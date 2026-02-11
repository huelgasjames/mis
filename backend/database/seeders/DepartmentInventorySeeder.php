<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DepartmentInventory;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartmentInventorySeeder extends Seeder
{
    public function run(): void
    {
        // Get MISD departments
        $misdDept = Department::where('name', 'MISD')->first();
        $comlabDept = Department::where('name', 'MISD - Computer Laboratory')->first();
        $techSupportDept = Department::where('name', 'MISD - Technical Support')->first();
        $networkOpsDept = Department::where('name', 'MISD - Network Operations')->first();
        $sysDevDept = Department::where('name', 'MISD - Systems Development')->first();

        if (!$misdDept) {
            $this->command->warn('MISD department not found. Please run DepartmentSeeder first.');
            return;
        }

        // Check all required departments
        $missingDepts = [];
        if (!$comlabDept) $missingDepts[] = 'MISD - Computer Laboratory';
        if (!$techSupportDept) $missingDepts[] = 'MISD - Technical Support';
        if (!$networkOpsDept) $missingDepts[] = 'MISD - Network Operations';
        if (!$sysDevDept) $missingDepts[] = 'MISD - Systems Development';
        
        if (!empty($missingDepts)) {
            $this->command->warn('Missing departments: ' . implode(', ', $missingDepts));
            $this->command->warn('Using MISD department as fallback.');
        }

        // Use fallback for missing departments
        $comlabDept = $comlabDept ?? $misdDept;
        $techSupportDept = $techSupportDept ?? $misdDept;
        $networkOpsDept = $networkOpsDept ?? $misdDept;
        $sysDevDept = $sysDevDept ?? $misdDept;

        $inventory = [
            [
                'asset_tag' => 'MISD-SU-003',
                'computer_name' => 'POWERLOGIC1',
                'category' => 'Desktop',
                'processor' => 'Intel Core i7-10700',
                'motherboard' => 'ASUS Prime Z490-A',
                'video_card' => 'NVIDIA GTX 1660 Super',
                'dvd_rom' => 'DVD-RW Drive',
                'psu' => '650W Corsair',
                'ram' => '16GB DDR4',
                'storage' => '512GB SSD + 1TB HDD',
                'serial_number' => 'SPHV',
                'status' => 'Working',
                'description' => 'Main admin workstation for MISD department',
                'location' => 'Office',
                'department_id' => $misdDept->id,
                'pc_num' => 'MISD-001',
                'deployment_date' => Carbon::now()->subMonths(6),
                'last_maintenance' => Carbon::now()->subMonth(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'asset_tag' => 'PNC-PC-002',
                'computer_name' => 'MISD-ADMIN-02',
                'category' => 'Desktop',
                'processor' => 'Intel Core i5-10400',
                'motherboard' => 'MSI B460M',
                'video_card' => 'Integrated Intel UHD 630',
                'dvd_rom' => null,
                'psu' => '500W Cooler Master',
                'ram' => '8GB DDR4',
                'storage' => '256GB SSD',
                'serial_number' => 'SN123456790',
                'status' => 'Working',
                'description' => 'Secondary admin workstation',
                'location' => 'Office',
                'department_id' => $misdDept->id,
                'pc_num' => 'MISD-002',
                'deployment_date' => Carbon::now()->subMonths(4),
                'last_maintenance' => Carbon::now()->subMonths(2),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'asset_tag' => 'PNC-LT-001',
                'computer_name' => 'MISD-LAPTOP-01',
                'category' => 'Laptop',
                'processor' => 'Intel Core i7-1165G7',
                'motherboard' => 'Dell Latitude 5420',
                'video_card' => 'Integrated Intel Iris Xe',
                'dvd_rom' => null,
                'psu' => '65W Dell Adapter',
                'ram' => '16GB DDR4',
                'storage' => '512GB NVMe SSD',
                'serial_number' => 'SN123456791',
                'status' => 'Deployed',
                'description' => 'Mobile workstation for field support',
                'location' => 'Comlab',
                'department_id' => $misdDept->id,
                'pc_num' => 'MISD-003',
                'deployment_date' => Carbon::now()->subMonths(3),
                'last_maintenance' => Carbon::now()->subMonth(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'asset_tag' => 'PNC-SRV-001',
                'computer_name' => 'MISD-SERVER-01',
                'category' => 'Server',
                'processor' => 'Intel Xeon E-2288G',
                'motherboard' => 'Supermicro X11SCH-F',
                'video_card' => 'Matrox G200eR2',
                'dvd_rom' => null,
                'psu' => '800W Redundant',
                'ram' => '32GB ECC DDR4',
                'storage' => '2TB RAID 10 SSD',
                'serial_number' => 'SN123456792',
                'status' => 'Working',
                'description' => 'Main application server for MISD',
                'location' => 'Office',
                'department_id' => $misdDept->id,
                'pc_num' => 'MISD-SRV-001',
                'deployment_date' => Carbon::now()->subYear(),
                'last_maintenance' => Carbon::now()->subWeek(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Add Computer Laboratory PCs if department exists
        if ($comlabDept) {
            $inventory = array_merge($inventory, [
                [
                    'asset_tag' => 'PNC-CL-001',
                    'computer_name' => 'COMLAB-PC-01',
                    'category' => 'Desktop',
                    'processor' => 'Intel Core i3-10100',
                    'motherboard' => 'Gigabyte H410M',
                    'video_card' => 'Integrated Intel UHD 630',
                    'dvd_rom' => 'DVD-RW Drive',
                    'psu' => '450W PSU',
                    'ram' => '8GB DDR4',
                    'storage' => '256GB SSD',
                    'serial_number' => 'SN123456793',
                    'status' => 'Working',
                    'description' => 'Computer Lab workstation 1',
                    'location' => 'Comlab',
                    'department_id' => $comlabDept->id,
                    'pc_num' => 'COMLAB-001',
                    'deployment_date' => Carbon::now()->subMonths(8),
                    'last_maintenance' => Carbon::now()->subMonths(3),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'asset_tag' => 'PNC-CL-002',
                    'computer_name' => 'COMLAB-PC-02',
                    'category' => 'Desktop',
                    'processor' => 'Intel Core i3-10100',
                    'motherboard' => 'Gigabyte H410M',
                    'video_card' => 'Integrated Intel UHD 630',
                    'dvd_rom' => 'DVD-RW Drive',
                    'psu' => '450W PSU',
                    'ram' => '8GB DDR4',
                    'storage' => '256GB SSD',
                    'serial_number' => 'SN123456794',
                    'status' => 'Working',
                    'description' => 'Computer Lab workstation 2',
                    'location' => 'Comlab',
                    'department_id' => $comlabDept->id,
                    'pc_num' => 'COMLAB-002',
                    'deployment_date' => Carbon::now()->subMonths(8),
                    'last_maintenance' => Carbon::now()->subMonths(3),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'asset_tag' => 'PNC-CL-003',
                    'computer_name' => 'COMLAB-PC-03',
                    'category' => 'Desktop',
                    'processor' => 'Intel Core i3-10100',
                    'motherboard' => 'Gigabyte H410M',
                    'video_card' => 'Integrated Intel UHD 630',
                    'dvd_rom' => null,
                    'psu' => '450W PSU',
                    'ram' => '8GB DDR4',
                    'storage' => '256GB SSD',
                    'serial_number' => 'SN123456795',
                    'status' => 'Defective',
                    'description' => 'Computer Lab workstation 3 - needs repair',
                    'location' => 'Comlab',
                    'department_id' => $comlabDept->id,
                    'pc_num' => 'COMLAB-003',
                    'deployment_date' => Carbon::now()->subMonths(8),
                    'last_maintenance' => Carbon::now()->subMonths(6),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
        }

        // Add Technical Support PCs if department exists
        if ($techSupportDept) {
            $inventory = array_merge($inventory, [
                [
                    'asset_tag' => 'PNC-TS-001',
                    'computer_name' => 'TECHSUPPORT-01',
                    'category' => 'Desktop',
                    'processor' => 'Intel Core i5-11400',
                    'motherboard' => 'ASUS TUF Gaming B560M-PLUS',
                    'video_card' => 'NVIDIA GTX 1050 Ti',
                    'dvd_rom' => null,
                    'psu' => '550W EVGA',
                    'ram' => '16GB DDR4',
                    'storage' => '1TB NVMe SSD',
                    'serial_number' => 'SN123456796',
                    'status' => 'Working',
                    'description' => 'Technical support diagnostic workstation',
                    'location' => 'Office',
                    'department_id' => $techSupportDept->id,
                    'pc_num' => 'TECHSUPPORT-001',
                    'deployment_date' => Carbon::now()->subMonths(5),
                    'last_maintenance' => Carbon::now()->subWeeks(2),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'asset_tag' => 'PNC-TS-002',
                    'computer_name' => 'TECHSUPPORT-02',
                    'category' => 'Laptop',
                    'processor' => 'AMD Ryzen 5 5500U',
                    'motherboard' => 'HP ProBook 445 G8',
                    'video_card' => 'Integrated AMD Radeon Graphics',
                    'dvd_rom' => null,
                    'psu' => '65W HP Adapter',
                    'ram' => '8GB DDR4',
                    'storage' => '256GB NVMe SSD',
                    'serial_number' => 'SN123456797',
                    'status' => 'Deployed',
                    'description' => 'Mobile technical support laptop',
                    'location' => 'Comlab',
                    'department_id' => $techSupportDept->id,
                    'pc_num' => 'TECHSUPPORT-002',
                    'deployment_date' => Carbon::now()->subMonths(2),
                    'last_maintenance' => Carbon::now()->subWeek(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
        }

        // Add Network Operations PCs if department exists
        if ($networkOpsDept) {
            $inventory = array_merge($inventory, [
                [
                    'asset_tag' => 'PNC-NO-001',
                    'computer_name' => 'NETOPS-01',
                    'category' => 'Desktop',
                    'processor' => 'Intel Core i7-10700K',
                    'motherboard' => 'ASUS ROG Strix Z490-E',
                    'video_card' => 'NVIDIA RTX 3060',
                    'dvd_rom' => null,
                    'psu' => '750W Seasonic',
                    'ram' => '32GB DDR4',
                    'storage' => '2TB NVMe SSD',
                    'serial_number' => 'SN123456798',
                    'status' => 'Working',
                    'description' => 'Network operations monitoring workstation',
                    'location' => 'Office',
                    'department_id' => $networkOpsDept->id,
                    'pc_num' => 'NETOPS-001',
                    'deployment_date' => Carbon::now()->subMonths(3),
                    'last_maintenance' => Carbon::now()->subWeeks(3),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
        }

        // Add Systems Development PCs if department exists
        if ($sysDevDept) {
            $inventory = array_merge($inventory, [
                [
                    'asset_tag' => 'PNC-SD-001',
                    'computer_name' => 'SYSDEV-01',
                    'category' => 'Desktop',
                    'processor' => 'AMD Ryzen 9 5900X',
                    'motherboard' => 'MSI MAG X570 Tomahawk',
                    'video_card' => 'NVIDIA RTX 3070',
                    'dvd_rom' => null,
                    'psu' => '850W Corsair',
                    'ram' => '64GB DDR4',
                    'storage' => '1TB NVMe SSD + 4TB HDD',
                    'serial_number' => 'SN123456799',
                    'status' => 'Working',
                    'description' => 'High-performance development workstation',
                    'location' => 'Office',
                    'department_id' => $sysDevDept->id,
                    'pc_num' => 'SYSDEV-001',
                    'deployment_date' => Carbon::now()->subMonths(4),
                    'last_maintenance' => Carbon::now()->subWeeks(2),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'asset_tag' => 'PNC-SD-002',
                    'computer_name' => 'SYSDEV-02',
                    'category' => 'Desktop',
                    'processor' => 'Intel Core i7-12700K',
                    'motherboard' => 'Gigabyte Z690 AORUS ELITE',
                    'video_card' => 'NVIDIA RTX 3080',
                    'dvd_rom' => null,
                    'psu' => '1000W EVGA',
                    'ram' => '32GB DDR5',
                    'storage' => '2TB NVMe SSD',
                    'serial_number' => 'SN123456800',
                    'status' => 'In Storage',
                    'description' => 'Development workstation - awaiting deployment',
                    'location' => null,
                    'department_id' => $sysDevDept->id,
                    'pc_num' => 'STORAGE-001',
                    'deployment_date' => null,
                    'last_maintenance' => Carbon::now()->subMonth(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
        }

        // Insert all inventory items
        $count = 0;
        foreach ($inventory as $item) {
            try {
                DepartmentInventory::updateOrCreate(
                    ['asset_tag' => $item['asset_tag']],
                    $item
                );
                $count++;
            } catch (\Exception $e) {
                $this->command->error("Failed to insert {$item['asset_tag']}: " . $e->getMessage());
            }
        }

        $this->command->info("Department inventory seeded successfully! ({$count} items)");
    }
}
