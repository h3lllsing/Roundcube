<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $category = AssetCategory::factory()->create();
        $type = AssetType::factory()->create(['category_id' => $category->id]);
        $prefix = config('assets.tag_prefix', 'AST');
        return [
            'asset_tag' => $prefix . '-' . fake()->unique()->numerify('#####'),
            'category_id' => $category->id,
            'type_id' => $type->id,
            'serial_number' => fake()->optional(0.8)->bothify('SN-########'),
            'status' => fake()->randomElement(['available', 'assigned', 'lost', 'decommissioned']),
            'assigned_to' => null,
            'department' => fake()->optional()->randomElement(['Engineering', 'Marketing', 'Sales', 'HR', 'Finance']),
            'issue_date' => null,
            'return_date' => null,
            'condition' => fake()->randomElement(['new', 'good', 'fair', 'poor', 'damaged']),
            'specifications' => null,
            'description' => fake()->optional()->sentence(),
            'primary_image' => null,
            'user_id' => User::factory(),
        ];
    }
}
