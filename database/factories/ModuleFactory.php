<?php

namespace Database\Factories;

use App\Models\Feature;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    protected $model = Module::class;

    public function definition(): array
    {
        return [
            'feature_id' => Feature::factory(),
            'name' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(1),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
