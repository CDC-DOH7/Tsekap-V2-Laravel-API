<?php

namespace Database\Seeders;

use App\Models\TsekapV2\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // list other regions here
        $regions = [
            ['region_name' => 'Central Visayas', 'region_code' => 'Region VII', 'country_id' => 1]
        ];

        foreach ($regions as $region) {
            Region::firstOrCreate(
                ['country_id' => $region['country_id'], 'region_name' => $region['region_name'], 'region_code' => $region['region_code']],
                [
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]
            );
        }
    }
}
