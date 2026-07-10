<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetAssignmentFactory extends Factory
{
    protected $model = AssetAssignment::class;

    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'assigned_to' => User::factory(),
            'department' => fake()->optional()->randomElement(['Engineering', 'Marketing', 'Sales']),
            'assigned_by' => User::factory(),
            'assigned_at' => fake()->dateTimeThisYear(),
            'expected_return_at' => fake()->optional(0.3)->dateTimeThisYear(),
            'returned_at' => fake()->optional(0.5)->dateTimeThisYear(),
            'condition_on_return' => null,
            'assignment_reason' => fake()->optional()->randomElement(['New Employee', 'Replacement', 'Temporary', 'Loan', 'Other']),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
