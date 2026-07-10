<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Vps;
use Illuminate\Database\Eloquent\Factories\Factory;

class VpsFactory extends Factory
{
    protected $model = Vps::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true).' VPS',
            'service_provider_id' => ServiceProvider::factory(),
            'plan' => fake()->randomElement(['s-1vcpu-1gb', 's-2vcpu-2gb', 's-4vcpu-8gb', 'c-2vcpu-4gb', 'g6-2vcpu-8gb']),
            'ip_address' => fake()->ipv4(),
            'password' => fake()->password(),
            'os' => fake()->randomElement(['Ubuntu 22.04', 'Ubuntu 24.04', 'Debian 12', 'CentOS 9', 'AlmaLinux 9', 'Rocky Linux 9']),
            'ram_mb' => fake()->randomElement([1024, 2048, 4096, 8192, 16384]),
            'disk_gb' => fake()->randomElement([25, 50, 80, 160, 320]),
            'cpu_cores' => fake()->randomElement([1, 2, 4, 8]),
            'cost' => fake()->randomFloat(2, 5, 200),
            'start_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'expiry_date' => fake()->dateTimeBetween('-1 month', '+1 year')->format('Y-m-d'),
            'status' => fake()->randomElement(['active', 'expired', 'cancelled']),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
