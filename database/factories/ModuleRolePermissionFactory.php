<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Role;

class ModuleRolePermissionFactory extends Factory
{
    protected $model = ModuleRolePermission::class;

    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'role_id' => Role::create(['name' => fake()->unique()->word(), 'slug' => fake()->unique()->slug()])->id,
            'can_create' => fake()->boolean(),
            'can_read' => true,
            'can_update' => fake()->boolean(),
            'can_delete' => fake()->boolean(),
            'can_export' => fake()->boolean(),
        ];
    }
}
