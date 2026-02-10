<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Laboratory;
use Illuminate\Support\Facades\DB;

class LaboratorySeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::table('laboratories')->delete();

        // Comprehensive MISD laboratory structure
        $laboratories = [
            [
                'name' => 'MISD Computer Laboratory 1',
                'code' => 'MISD-LAB1',
                'description' => 'Main MISD computer laboratory for programming and development',
                'location' => 'IT Building, Floor 2',
                'capacity' => 30,
                'supervisor' => 'MISD Laboratory Head',
                'contact_number' => '123-456-7890',
                'status' => 'Active',
            ],
            [
                'name' => 'MISD Computer Laboratory 2',
                'code' => 'MISD-LAB2',
                'description' => 'MISD advanced programming and networking laboratory',
                'location' => 'IT Building, Floor 3',
                'capacity' => 25,
                'supervisor' => 'MISD Technical Lead',
                'contact_number' => '123-456-7891',
                'status' => 'Active',
            ],
            [
                'name' => 'MISD Systems Laboratory',
                'code' => 'MISD-SYS',
                'description' => 'MISD systems administration and database laboratory',
                'location' => 'IT Building, Floor 1',
                'capacity' => 20,
                'supervisor' => 'MISD Systems Administrator',
                'contact_number' => '123-456-7892',
                'status' => 'Active',
            ],
            [
                'name' => 'MISD Network Laboratory',
                'code' => 'MISD-NET',
                'description' => 'MISD networking and security testing laboratory',
                'location' => 'IT Building, Floor 4',
                'capacity' => 15,
                'supervisor' => 'MISD Network Administrator',
                'contact_number' => '123-456-7893',
                'status' => 'Active',
            ],
            [
                'name' => 'MISD Research Laboratory',
                'code' => 'MISD-RES',
                'description' => 'MISD research and development laboratory',
                'location' => 'IT Building, Floor 5',
                'capacity' => 15,
                'supervisor' => 'MISD Research Lead',
                'contact_number' => '123-456-7894',
                'status' => 'Active',
            ],
            [
                'name' => 'MISD Training Laboratory',
                'code' => 'MISD-TRN',
                'description' => 'MISD training and workshop laboratory',
                'location' => 'IT Building, Floor 6',
                'capacity' => 20,
                'supervisor' => 'MISD Training Coordinator',
                'contact_number' => '123-456-7895',
                'status' => 'Active',
            ],
            [
                'name' => 'MISD Server Room',
                'code' => 'MISD-SRV',
                'description' => 'MISD server infrastructure and data center',
                'location' => 'IT Building, Basement',
                'capacity' => 10,
                'supervisor' => 'MISD Infrastructure Lead',
                'contact_number' => '123-456-7896',
                'status' => 'Active',
            ],
        ];

        foreach ($laboratories as $lab) {
            Laboratory::create($lab);
        }

        $this->command->info('MISD-focused laboratory structure seeded successfully!');
    }
}
