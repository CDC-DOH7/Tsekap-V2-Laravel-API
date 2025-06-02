<?php

namespace Database\Seeders;

use App\Models\TsekapV2\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $countries = [
            ['country_name' => 'Philippines', 'country_code' => 'PH']

        ];

        foreach ($countries as $country) {
            Country::firstOrCreate(
                ['country_name' => $country['country_name'], 'country_code' => $country['country_code']],
                [
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]
            );
        }
    }
}
