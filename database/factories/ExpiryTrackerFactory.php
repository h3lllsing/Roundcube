<?php

namespace Database\Factories;

use App\Models\ExpiryTracker;
use App\Models\Module;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpiryTrackerFactory extends Factory
{
    protected $model = ExpiryTracker::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service_provider_id' => ServiceProvider::factory(),
            'name' => fake()->words(3, true),
            'username' => fake()->userName(),

            'login_url' => fake()->url(),
            'expiry_date' => fake()->dateTimeBetween('-1 month', '+6 months')->format('Y-m-d'),
            'renewal_date' => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d') : null,
            'cost' => fake()->optional()->randomFloat(2, 10, 999),
            'status' => fake()->randomElement(['active', 'expired', 'pending_renewal', 'cancelled']),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function withModule(): static
    {
        return $this->state(fn () => ['module_id' => Module::factory()]);
    }
}
