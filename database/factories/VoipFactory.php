<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Voip;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoipFactory extends Factory
{
    protected $model = Voip::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service_provider_id' => ServiceProvider::factory(),
            'name' => fake()->words(2, true).' Line',
            'extensions' => [fake()->numerify('1##'), fake()->optional()->numerify('2##')],
            'phone_number' => fake()->phoneNumber(),
            'type' => fake()->randomElement(['sip', 'trunk', 'phone']),
            'direction' => fake()->randomElement(['inbound', 'outbound', 'both']),
            'username' => fake()->userName(),
            'password' => fake()->password(),
            'extension_password' => fake()->optional()->password(),
            'dashboard_url' => fake()->url(),
            'server_ip' => fake()->optional()->ipv4(),
            'cost' => fake()->randomFloat(2, 5, 100),
            'start_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'expiry_date' => fake()->dateTimeBetween('-1 month', '+1 year')->format('Y-m-d'),
            'status' => fake()->randomElement(['active', 'expired', 'cancelled']),
            'number_status' => fake()->optional()->randomElement(['active', 'blocked', 'forwarding']),
            'outbound_code' => fake()->optional()->numerify('0###'),
            'team_details' => fake()->optional()->sentence(),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
