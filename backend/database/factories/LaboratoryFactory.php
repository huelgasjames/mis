<?php

namespace Database\Factories;

use App\Models\Laboratory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Laboratory>
 */
class LaboratoryFactory extends Factory
{
    protected $model = Laboratory::class;

    public function definition(): array
    {
        $building = $this->faker->randomElement(['Main Building', 'Science Building', 'Engineering Building', 'Library Building']);
        $floor = $this->faker->numberBetween(1, 5);
        $room = $this->faker->numberBetween(101, 599);
        
        return [
            'name' => $this->faker->randomElement([
                'Computer Laboratory', 'Programming Lab', 'Networking Lab', 
                'Multimedia Lab', 'Research Lab', 'General Purpose Lab'
            ]) . ' ' . $this->faker->numberBetween(1, 10),
            'code' => 'LAB' . $this->faker->unique()->numerify('###'),
            'description' => $this->faker->sentence(10),
            'location' => "{$building}, Floor {$floor}, Room {$room}",
            'capacity' => $this->faker->numberBetween(20, 50),
            'supervisor' => $this->faker->name(),
            'contact_number' => $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement(['Active', 'Maintenance', 'Closed']),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Active',
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Maintenance',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Closed',
        ]);
    }

    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $this->faker->numberBetween(10, 25),
        ]);
    }

    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $this->faker->numberBetween(26, 40),
        ]);
    }

    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $this->faker->numberBetween(41, 60),
        ]);
    }
}
