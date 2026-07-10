<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\User;
use App\Models\UserModulePermission;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserModulePermissionFactory extends Factory
{
    protected $model = UserModulePermission::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'module_id' => Module::factory(),
            'can_create' => null,
            'can_read' => null,
            'can_update' => null,
            'can_export' => null,
            'can_reveal' => null,
            'can_import' => null,
        ];
    }

    public function allow(string $permission): static
    {
        return $this->state(fn () => [$permission => true]);
    }

    public function deny(string $permission): static
    {
        return $this->state(fn () => [$permission => false]);
    }
}
