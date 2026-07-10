<?php

namespace Database\Factories;

use App\Models\Hosting;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HostingFactory extends Factory
{
    protected $model = Hosting::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true).' Hosting',
            'username' => fake()->userName(),
            'password' => fake()->password(8, 16),
            'cpanel_url' => fake()->optional()->url(),
            'service_provider_id' => ServiceProvider::factory(),
            'plan' => fake()->randomElement(['Basic', 'Business', 'Deluxe', 'Premium', 'Enterprise']),
            'domain' => fake()->domainName(),
            'start_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'expiry_date' => fake()->dateTimeBetween('-1 month', '+1 year')->format('Y-m-d'),
            'cost' => fake()->randomFloat(2, 5, 200),
            'status' => fake()->randomElement(['active', 'inactive', 'expired', 'suspended', 'pending_transfer', 'cancelled']),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
