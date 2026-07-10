<?php

namespace Database\Factories;

use App\Models\ExpiryTracker;
use App\Models\ExpiryTrackerNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpiryTrackerNotificationFactory extends Factory
{
    protected $model = ExpiryTrackerNotification::class;

    public function definition(): array
    {
        return [
            'expiry_tracker_id' => ExpiryTracker::factory(),
            'sender_email' => fake()->email(),
            'reminder_day' => fake()->numberBetween(-7, 30),
            'recipient_email' => fake()->email(),
            'recipient_type' => fake()->randomElement(['user', 'admin', 'custom']),
            'trigger_source' => fake()->randomElement(['cron', 'manual', 'test']),
            'status' => fake()->randomElement(['sent', 'failed']),
            'sent_at' => fake()->dateTimeThisMonth(),
        ];
    }
}
