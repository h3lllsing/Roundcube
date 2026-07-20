<?php

namespace Database\Factories;

use App\Enums\DomainStatus;
use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;

class DomainFactory extends Factory
{
    protected $model = Domain::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->domainName(),
            'status' => DomainStatus::Active,
            'notes' => fake()->sentence(),
        ];
    }
}
