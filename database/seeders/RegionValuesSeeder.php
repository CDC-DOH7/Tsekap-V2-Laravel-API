<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionValuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            // Bohol
            1 => '1', // Bohol 
            2 => '1', // Cebu 
        ];

        foreach ($provinces as $provinceId => $regionId) {
            DB::table('province')
                ->where('id', $provinceId)
                ->update(['region_id' => (int)$regionId]);
        }
    }
}
