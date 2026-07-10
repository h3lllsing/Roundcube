<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\User;
use App\Models\VaultEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class VaultEntryFactory extends Factory
{
    protected $model = VaultEntry::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service_name' => fake()->company().' '.fake()->randomElement(['Admin', 'API', 'Dashboard', 'SSH']),
            'service_url' => fake()->url(),
            'username' => fake()->userName(),
            'encrypted_password' => encrypt(fake()->password(12, 20)),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function withModule(): static
    {
        return $this->state(fn () => ['module_id' => Module::factory()]);
    }
}
