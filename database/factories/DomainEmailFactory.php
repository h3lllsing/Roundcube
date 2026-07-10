<?php

namespace Database\Factories;

use App\Models\DomainEmail;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DomainEmailFactory extends Factory
{
    protected $model = DomainEmail::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'email' => fake()->email(),
            'service_provider_id' => ServiceProvider::factory(),
            'storage_mb' => fake()->randomElement([5120, 10240, 15360, 30720, 102400]),
            'cost' => fake()->randomFloat(2, 3, 50),
            'expiry_date' => fake()->dateTimeBetween('-1 month', '+1 year')->format('Y-m-d'),
            'status' => fake()->randomElement(['active', 'expired', 'cancelled']),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
