<?php

namespace Database\Seeders;

use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(FeatureModuleSeeder::class);
        $this->call(RolePermissionSeeder::class);

        if (! app()->environment('testing', 'production')) {
            $this->call([
                TyroSeeder::class,
            ]);

            User::updateOrCreate(
                ['email' => 'test@example.com'],
                ['name' => 'Test User', 'password' => bcrypt(env('DEMO_ENTITY_PASSWORD', 'password'))],
            );
        }
    }
}
