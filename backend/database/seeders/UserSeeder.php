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
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'MISD Laboratory Head',
                'email' => 'labhead@misd.inventory.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'MISD Technical Lead',
                'email' => 'techlead@misd.inventory.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'MISD Systems Administrator',
                'email' => 'sysadmin@misd.inventory.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'MISD Network Administrator',
                'email' => 'netadmin@misd.inventory.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'MISD Database Administrator',
                'email' => 'dbadmin@misd.inventory.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'MISD Security Officer',
                'email' => 'security@misd.inventory.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'MISD Help Desk',
                'email' => 'helpdesk@misd.inventory.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'MISD Support Technician',
                'email' => 'support@misd.inventory.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'MISD Research Lead',
                'email' => 'research@misd.inventory.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'MISD Training Coordinator',
                'email' => 'training@misd.inventory.com',
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('MISD-focused user accounts seeded successfully!');
    }
}
