<?php

namespace Database\Seeders;

use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            \HasinHayder\Tyro\Database\Seeders\TyroSeeder::class,
        ]);

        // Give admin user the super-admin role too
        $admin = User::where('email', 'admin@tyro.project')->first();
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if ($admin && $superAdminRole && !$admin->roles->contains($superAdminRole->id)) {
            $admin->roles()->attach($superAdminRole);
        }

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')],
        );

        $this->call(FeatureModuleSeeder::class);
    }
}
