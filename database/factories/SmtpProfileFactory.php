<?php

namespace Database\Factories;

use App\Models\SmtpProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmtpProfileFactory extends Factory
{
    protected $model = SmtpProfile::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' SMTP',
            'sender_name' => fake()->name(),
            'sender_email' => fake()->companyEmail(),
            'reply_to_email' => fake()->companyEmail(),
            'smtp_host' => fake()->randomElement(['smtp.gmail.com', 'smtp.mailtrap.io', 'smtp.sendgrid.net']),
            'smtp_port' => fake()->randomElement([465, 587, 25]),
            'smtp_encryption' => fake()->randomElement(['ssl', 'tls']),
            'smtp_username' => fake()->email(),
            'smtp_password' => fake()->password(),
            'is_default' => false,
            'is_active' => true,
            'priority' => fake()->numberBetween(0, 10),
            'last_tested_at' => null,
            'last_test_status' => null,
            'last_test_error' => null,
        ];
    }

    public function default(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
