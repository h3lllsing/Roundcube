<?php

namespace Database\Factories;

use App\Models\Webhook;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebhookFactory extends Factory
{
    protected $model = Webhook::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Test Webhook',
            'url' => 'https://hooks.example.com/notify',
            'events' => ['expiring_soon'],
            'is_active' => true,
        ];
    }
}
