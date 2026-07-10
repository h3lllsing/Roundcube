<?php

namespace Database\Factories;

use App\Models\GMail;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GMailFactory extends Factory
{
    protected $model = GMail::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'module_id' => Module::factory(),
            'status' => fake()->randomElement(['active', 'inactive', 'suspended']),
            'user_name' => fake()->userName(),
            'pseudo' => fake()->userName(),
            'emails_address' => fake()->email(),
            'password' => fake()->password(),
            'security_number' => fake()->randomNumber(8),
            'security_number_person' => fake()->name(),
            'recovery_email' => fake()->email(),
            'department' => fake()->word(),
            'assigned' => fake()->name(),
            'user_remarks' => fake()->sentence(),
            'comments' => fake()->paragraph(),
        ];
    }
}
