<?php

namespace Database\Factories;

use App\Models\LaboratoryInventory;
use App\Models\Laboratory;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LaboratoryInventory>
 */
class LaboratoryInventoryFactory extends Factory
{
    protected $model = LaboratoryInventory::class;

    public function definition(): array
    {
        $categories = Category::pluck('id')->toArray();
        $laboratories = Laboratory::pluck('id')->toArray();
        
        $status = $this->faker->randomElement(['Deployed', 'Available', 'Under Repair', 'Defective']);
        $condition = $this->faker->randomElement(['Excellent', 'Good', 'Fair', 'Poor']);
        
        return [
            'uuid' => $this->faker->uuid(),
            'asset_tag' => 'PCC-' . $this->faker->unique()->numerify('#####'),
            'computer_name' => 'PC-' . $this->faker->unique()->numerify('####'),
            'lab_pc_num' => $this->faker->unique()->numerify('###'),
            'category_id' => $this->faker->randomElement($categories),
            'processor' => $this->faker->randomElement([
                'Intel Core i5-12400F', 'Intel Core i7-12700K', 'AMD Ryzen 5 5600X', 
                'AMD Ryzen 7 5800X', 'Intel Core i9-12900K', 'AMD Ryzen 9 5900X'
            ]),
            'motherboard' => $this->faker->randomElement([
                'ASUS Prime B560M-A', 'MSI B560M PRO-VDH', 'Gigabyte B560M DS3H',
                'ASUS ROG Strix B560-F', 'MSI MAG B560 TOMAHAWK'
            ]),
            'video_card' => $this->faker->randomElement([
                'NVIDIA GeForce RTX 3060', 'NVIDIA GeForce RTX 3070', 'AMD Radeon RX 6600',
                'AMD Radeon RX 6700 XT', 'NVIDIA GeForce GTX 1660 Super'
            ]),
            'dvd_rom' => $this->faker->randomElement(['DVD-RW', 'DVD-ROM', 'None']),
            'psu' => $this->faker->randomElement([
                'Corsair CV550 550W', 'Seasonic Focus GX-550', 'EVGA 550 BQ',
                'Thermaltake Smart 550W', 'Cooler Master MWE 550'
            ]),
            'ram' => $this->faker->randomElement(['8GB DDR4', '16GB DDR4', '32GB DDR4', '64GB DDR4']),
            'storage' => $this->faker->randomElement([
                '256GB SSD', '512GB SSD', '1TB SSD', '2TB HDD', '1TB HDD + 256GB SSD'
            ]),
            'serial_number' => $this->faker->unique()->numerify('SN############'),
            'status' => $status,
            'assigned_to' => $status === 'Deployed' ? $this->faker->name() : null,
            'condition' => $condition,
            'notes' => $this->faker->optional(0.3)->sentence(),
            'laboratory_id' => $this->faker->randomElement($laboratories),
            'deployment_date' => $status === 'Deployed' ? $this->faker->dateTimeBetween('-2 years', 'now') : null,
            'last_maintenance' => $this->faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            'repair_start_date' => $status === 'Under Repair' ? $this->faker->dateTimeBetween('-30 days', 'now') : null,
            'repair_end_date' => $status === 'Under Repair' ? null : $this->faker->optional(0.2)->dateTimeBetween('-30 days', 'now'),
            'repair_description' => $status === 'Under Repair' ? $this->faker->sentence() : null,
            'repaired_by' => $status === 'Under Repair' ? $this->faker->name() : null,
        ];
    }

    public function deployed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Deployed',
            'deployment_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'assigned_to' => $this->faker->name(),
        ]);
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Available',
            'deployment_date' => null,
            'assigned_to' => null,
        ]);
    }

    public function underRepair(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Under Repair',
            'repair_start_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'repair_description' => $this->faker->sentence(),
            'repaired_by' => $this->faker->name(),
        ]);
    }

    public function defective(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Defective',
            'condition' => $this->faker->randomElement(['Fair', 'Poor']),
        ]);
    }

    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'Excellent',
        ]);
    }

    public function good(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'Good',
        ]);
    }

    public function desktop(): static
    {
        $desktopCategory = Category::where('code', 'DT')->first();
        return $this->state(fn (array $attributes) => [
            'category_id' => $desktopCategory?->id,
        ]);
    }

    public function laptop(): static
    {
        $laptopCategory = Category::where('code', 'LT')->first();
        return $this->state(fn (array $attributes) => [
            'category_id' => $laptopCategory?->id,
        ]);
    }

    public function server(): static
    {
        $serverCategory = Category::where('code', 'SR')->first();
        return $this->state(fn (array $attributes) => [
            'category_id' => $serverCategory?->id,
        ]);
    }

    public function monitor(): static
    {
        $monitorCategory = Category::where('code', 'MN')->first();
        return $this->state(fn (array $attributes) => [
            'category_id' => $monitorCategory?->id,
        ]);
    }
}
