<?php

namespace Database\Factories;

use App\Models\LoginAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoginAuditFactory extends Factory
{
    protected $model = LoginAudit::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'email' => fake()->email(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'event' => fake()->randomElement(['login', 'logout', 'failed']),
        ];
    }
}
