<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing users first
        DB::table('users')->delete();

        // MISD-focused user accounts with different roles
        $users = [
            [
                'name' => 'MISD Administrator',
                'email' => 'admin@misd.inventory.com',
                'role' => 'admin',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MISD Laboratory Head',
                'email' => 'labhead@misd.inventory.com',
                'role' => 'manager',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MISD Technical Lead',
                'email' => 'techlead@misd.inventory.com',
                'role' => 'supervisor',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MISD Systems Administrator',
                'email' => 'sysadmin@misd.inventory.com',
                'role' => 'technician',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MISD Network Administrator',
                'email' => 'netadmin@misd.inventory.com',
                'role' => 'technician',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MISD Database Administrator',
                'email' => 'dbadmin@misd.inventory.com',
                'role' => 'technician',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MISD Security Officer',
                'email' => 'security@misd.inventory.com',
                'role' => 'supervisor',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MISD Help Desk',
                'email' => 'helpdesk@misd.inventory.com',
                'role' => 'staff',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MISD Support Technician',
                'email' => 'support@misd.inventory.com',
                'role' => 'technician',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MISD Research Lead',
                'email' => 'research@misd.inventory.com',
                'role' => 'manager',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MISD Training Coordinator',
                'email' => 'training@misd.inventory.com',
                'role' => 'staff',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('MISD-focused user accounts seeded successfully!');
    }
}
