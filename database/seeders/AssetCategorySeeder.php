<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Laptop', 'slug' => 'laptop', 'description' => 'Portable computers including notebooks and ultrabooks', 'sort_order' => 1],
            ['name' => 'Headphone', 'slug' => 'headphone', 'description' => 'Audio headsets, headphones and earbuds', 'sort_order' => 2],
            ['name' => 'Mouse', 'slug' => 'mouse', 'description' => 'Computer mice and pointing devices', 'sort_order' => 3],
            ['name' => 'Network Device', 'slug' => 'network-device', 'description' => 'Routers, switches, firewalls, WiFi APs, IP phones, CCTV, NVR/DVR', 'sort_order' => 4],
        ];

        foreach ($categories as $data) {
            AssetCategory::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
