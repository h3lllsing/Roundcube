<?php

namespace Database\Factories;

use App\Models\AssetCategory;
use App\Models\AssetType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetTypeFactory extends Factory
{
    protected $model = AssetType::class;

    public function definition(): array
    {
        $brands = ['Dell', 'HP', 'Lenovo', 'Cisco', 'Ubiquiti', 'Logitech', 'Sony'];
        return [
            'category_id' => AssetCategory::factory(),
            'name' => fake()->word(),
            'brand' => fake()->randomElement($brands),
            'model_number' => fake()->optional()->bothify('??-####'),
            'is_active' => true,
        ];
    }
}
