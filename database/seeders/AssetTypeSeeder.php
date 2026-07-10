<?php

namespace Database\Seeders;

use App\Models\AssetType;
use Illuminate\Database\Seeder;

class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $laptop = \App\Models\AssetCategory::where('slug', 'laptop')->first();
        $headphone = \App\Models\AssetCategory::where('slug', 'headphone')->first();
        $mouse = \App\Models\AssetCategory::where('slug', 'mouse')->first();
        $network = \App\Models\AssetCategory::where('slug', 'network-device')->first();

        $types = [];

        if ($laptop) {
            $types = array_merge($types, [
                ['category_id' => $laptop->id, 'brand' => 'Dell', 'name' => 'Latitude 5540', 'model_number' => 'Latitude 5540'],
                ['category_id' => $laptop->id, 'brand' => 'Dell', 'name' => 'Latitude 7440', 'model_number' => 'Latitude 7440'],
                ['category_id' => $laptop->id, 'brand' => 'HP', 'name' => 'EliteBook 840 G10', 'model_number' => '840 G10'],
                ['category_id' => $laptop->id, 'brand' => 'Lenovo', 'name' => 'ThinkPad X1 Carbon Gen 11', 'model_number' => 'X1C Gen 11'],
                ['category_id' => $laptop->id, 'brand' => 'Apple', 'name' => 'MacBook Pro 14"', 'model_number' => 'MPP14'],
            ]);
        }

        if ($headphone) {
            $types = array_merge($types, [
                ['category_id' => $headphone->id, 'brand' => 'Sony', 'name' => 'WH-1000XM5', 'model_number' => 'WH1000XM5'],
                ['category_id' => $headphone->id, 'brand' => 'Jabra', 'name' => 'Evolve2 65', 'model_number' => 'EV65'],
                ['category_id' => $headphone->id, 'brand' => 'Logitech', 'name' => 'Zone 900', 'model_number' => 'Z900'],
            ]);
        }

        if ($mouse) {
            $types = array_merge($types, [
                ['category_id' => $mouse->id, 'brand' => 'Logitech', 'name' => 'MX Master 3S', 'model_number' => 'MX3S'],
                ['category_id' => $mouse->id, 'brand' => 'Microsoft', 'name' => 'Surface Mouse', 'model_number' => 'SFC-00001'],
                ['category_id' => $mouse->id, 'brand' => 'Razer', 'name' => 'DeathAdder V3', 'model_number' => 'DAV3'],
            ]);
        }

        if ($network) {
            $types = array_merge($types, [
                ['category_id' => $network->id, 'brand' => 'Cisco', 'name' => 'Catalyst 9300-24T', 'model_number' => 'C9300-24T'],
                ['category_id' => $network->id, 'brand' => 'MikroTik', 'name' => 'RB4011iGS+RM', 'model_number' => 'RB4011'],
                ['category_id' => $network->id, 'brand' => 'Ubiquiti', 'name' => 'EdgeRouter 12', 'model_number' => 'ER-12'],
                ['category_id' => $network->id, 'brand' => 'Fortinet', 'name' => 'FortiGate 60F', 'model_number' => 'FG-60F'],
                ['category_id' => $network->id, 'brand' => 'Ubiquiti', 'name' => 'UniFi 6 Pro', 'model_number' => 'U6-Pro'],
                ['category_id' => $network->id, 'brand' => 'Cisco', 'name' => 'IP Phone 8845', 'model_number' => 'CP-8845'],
                ['category_id' => $network->id, 'brand' => 'Hikvision', 'name' => 'DS-2CD2386G2-I', 'model_number' => '2CD2386G2'],
                ['category_id' => $network->id, 'brand' => 'Hikvision', 'name' => 'DS-7608NI-I2/8P', 'model_number' => 'DS-7608NI'],
            ]);
        }

        foreach ($types as $data) {
            AssetType::updateOrCreate(
                ['category_id' => $data['category_id'], 'brand' => $data['brand'], 'name' => $data['name']],
                $data
            );
        }
    }
}
