<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Models\Domain;
use App\Models\EmailAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailAccountFactory extends Factory
{
    protected $model = EmailAccount::class;

    public function definition(): array
    {
        return [
            'domain_id' => Domain::factory(),
            'email' => fake()->unique()->email(),
            'password' => bcrypt('password'),
            'imap_host' => 'imap.' . fake()->domainName(),
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'smtp_host' => 'smtp.' . fake()->domainName(),
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => fake()->email(),
            'status' => AccountStatus::Active,
            'sync_enabled' => true,
        ];
    }
}
