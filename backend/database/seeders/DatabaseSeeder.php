<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application database.
     */
    public function run(): void
    {
        // Run MISD-focused seeders in correct order
        $this->call([
            \Database\Seeders\CategorySeeder::class,
            \Database\Seeders\DepartmentSeeder::class,
            \Database\Seeders\LaboratorySeeder::class,
            \Database\Seeders\UserSeeder::class,
            \Database\Seeders\LaboratoryInventorySeeder::class,
            \Database\Seeders\ProcessorSeeder::class,
            \Database\Seeders\MotherboardSeeder::class,
            \Database\Seeders\VideoCardSeeder::class,
            \Database\Seeders\DvdRomSeeder::class,
            \Database\Seeders\PsuSeeder::class,
            \Database\Seeders\RamSeeder::class,
            \Database\Seeders\StorageSeeder::class,
        ]);
    }
}
