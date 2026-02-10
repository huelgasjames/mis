<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // Clear all existing departments first
        DB::table('departments')->delete();

        // Create comprehensive MISD department structure
        $departments = [
            [
                'name' => 'MISD',
                'office_location' => 'Main Building',
            ],
            [
                'name' => 'MISD - Computer Laboratory',
                'office_location' => 'IT Building',
            ],
            [
                'name' => 'MISD - Technical Support',
                'office_location' => 'IT Building',
            ],
            [
                'name' => 'MISD - Network Operations',
                'office_location' => 'IT Building',
            ],
            [
                'name' => 'MISD - Systems Development',
                'office_location' => 'IT Building',
            ],
            [
                'name' => 'MISD - Database Administration',
                'office_location' => 'IT Building',
            ],
            [
                'name' => 'MISD - IT Security',
                'office_location' => 'IT Building',
            ],
            [
                'name' => 'MISD - Help Desk',
                'office_location' => 'IT Building',
            ],
            [
                'name' => 'MISD - Research & Development',
                'office_location' => 'IT Building',
            ],
            [
                'name' => 'MISD - Infrastructure',
                'office_location' => 'IT Building',
            ],
            [
                'name' => 'MISD - Training',
                'office_location' => 'IT Building',
            ],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        $this->command->info('MISD-focused department structure seeded successfully!');
    }
}
