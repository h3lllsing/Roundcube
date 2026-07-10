<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\Module;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DomainFactory extends Factory
{
    protected $model = Domain::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'hosting_id' => null,
            'service_provider_id' => ServiceProvider::factory(),
            'name' => fake()->domainName(),
            'registration_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'expiry_date' => fake()->dateTimeBetween('-1 month', '+1 year')->format('Y-m-d'),
            'auto_renew' => fake()->boolean(),
            'cost' => fake()->randomFloat(2, 8, 50),
            'status' => fake()->randomElement(['active', 'expired', 'pending_transfer', 'cancelled']),
            'dns_servers' => fake()->boolean() ? [fake()->domainWord().'.com', fake()->domainWord().'.net'] : null,
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function withModule(): static
    {
        return $this->state(fn () => ['module_id' => Module::factory()]);
    }
}
