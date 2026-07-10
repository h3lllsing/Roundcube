<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(AssetCategorySeeder::class);
        $this->call(AssetTypeSeeder::class);
        $this->call(FeatureModuleSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(RoleTemplateSeeder::class);

        if (! app()->environment('testing', 'production')) {
            $this->call([
                TyroSeeder::class,
            ]);

            User::updateOrCreate(
                ['email' => 'test@example.com'],
                ['name' => 'Test User', 'password' => bcrypt(env('DEMO_ENTITY_PASSWORD', 'password'))],
            );

            $this->call(DemoDataSeeder::class);
        }
    }
}
