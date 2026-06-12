<?php

namespace Database\Factories;

use App\Models\Voip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoipFactory extends Factory
{
    protected $model = Voip::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true) . ' Line',
            'provider' => fake()->randomElement(['Twilio', 'Vonage', 'RingCentral', 'Zoom Phone', '8x8', 'Nextiva']),
            'phone_number' => fake()->phoneNumber(),
            'type' => fake()->randomElement(['sip', 'trunk', 'phone']),
            'username' => fake()->userName(),
            'cost' => fake()->randomFloat(2, 5, 100),
            'start_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'expiry_date' => fake()->dateTimeBetween('-1 month', '+1 year')->format('Y-m-d'),
            'status' => fake()->randomElement(['active', 'expired', 'cancelled']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
