<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->randomElement([
                'Management Information Systems Department',
                'Computer Science Department',
                'Information Technology Department',
                'Engineering Department',
                'Business Administration Department',
                'Accounting Department',
                'Human Resources Department',
                'Marketing Department'
            ]),
            'office_location' => $this->faker->randomElement([
                'Main Building, Room 101',
                'Science Building, Room 205',
                'Engineering Building, Room 301',
                'Library Building, Room 102',
                'Administration Building, Room 201'
            ]),
        ];
    }

    public function misd(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Management Information Systems Department',
            'office_location' => 'Main Building, Room 101',
        ]);
    }

    public function computerScience(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Computer Science Department',
            'office_location' => 'Science Building, Room 205',
        ]);
    }

    public function it(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Information Technology Department',
            'office_location' => 'Engineering Building, Room 301',
        ]);
    }
}
