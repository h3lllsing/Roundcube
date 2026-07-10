<?php

namespace Database\Factories;

use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OtherServiceFactory extends Factory
{
    protected $model = OtherService::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service_provider_id' => ServiceProvider::factory(),
            'name' => fake()->words(2, true).' Service',
            'service_type' => fake()->randomElement(['saas', 'api', 'monitoring', 'analytics', 'cdn', 'ssl', 'other']),
            'username' => fake()->userName(),
            'password' => fake()->password(),
            'login_url' => fake()->url(),
            'website' => fake()->url(),
            'cost' => fake()->randomFloat(2, 5, 300),
            'start_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'expiry_date' => fake()->dateTimeBetween('-1 month', '+1 year')->format('Y-m-d'),
            'status' => fake()->randomElement(['active', 'expired', 'cancelled']),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
