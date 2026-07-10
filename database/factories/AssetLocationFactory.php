<?php

namespace Database\Factories;

use App\Models\AssetLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetLocationFactory extends Factory
{
    protected $model = AssetLocation::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city() . ' - ' . fake()->randomElement(['Floor 1', 'Floor 2', 'Floor 3', 'Server Room', 'Warehouse']),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
