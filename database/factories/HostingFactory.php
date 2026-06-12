<?php

namespace Database\Factories;

use App\Models\Hosting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HostingFactory extends Factory
{
    protected $model = Hosting::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true) . ' Hosting',
            'provider' => fake()->randomElement(['SiteGround', 'Bluehost', 'HostGator', 'DreamHost', 'A2 Hosting', 'InMotion']),
            'plan' => fake()->randomElement(['Basic', 'Business', 'Deluxe', 'Premium', 'Enterprise']),
            'domain' => fake()->domainName(),
            'start_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'expiry_date' => fake()->dateTimeBetween('-1 month', '+1 year')->format('Y-m-d'),
            'cost' => fake()->randomFloat(2, 5, 200),
            'status' => fake()->randomElement(['active', 'expired', 'cancelled']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
