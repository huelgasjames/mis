<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'code' => $this->faker->unique()->lexify('???'),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(10),
            'is_editable' => true,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_editable' => false,
        ]);
    }

    public function editable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_editable' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => '550e8400-e29b-41d4-a716-446655440001',
            'code' => 'DT',
            'name' => 'Desktop',
            'description' => 'Desktop computer systems',
            'is_editable' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function laptop(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => '550e8400-e29b-41d4-a716-446655440002',
            'code' => 'LT',
            'name' => 'Laptop',
            'description' => 'Laptop computer systems',
            'is_editable' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);
    }

    public function server(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => '550e8400-e29b-41d4-a716-446655440003',
            'code' => 'SR',
            'name' => 'Server',
            'description' => 'Server computer systems',
            'is_editable' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }

    public function monitor(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => '550e8400-e29b-41d4-a716-446655440004',
            'code' => 'MN',
            'name' => 'Monitor',
            'description' => 'Computer monitors and displays',
            'is_editable' => false,
            'is_active' => true,
            'sort_order' => 4,
        ]);
    }
}
