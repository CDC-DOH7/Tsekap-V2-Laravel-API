<?php

namespace Database\Seeders;

use App\Models\TsekapV2\Religion;
use Illuminate\Database\Seeder;

class ReligionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // list other religions here
        $religions = [
            'Roman Catholic',
            'Islam',
            'Iglesia ni Cristo',
            'Seventh-Day Adventist',
            'Iglesia Filipina Independiente/Aglipay',
            'Bible Baptist Church',
            'United Church of Christ in The Philippines',
            "Jehovah's Witnesses",
            'Church of Christ',
            'Latter-Day Saints',
            'Assemblies of God',
            'Kingdom of Jesus Christ',
            'Evangelical',
            'Baptists',
            'Methodists',
            'Hinduism',
            'Buddhism',
            'Judaism',
            "Baha'i",
            'Jainism',
            'Eastern Orthodox',
            'Anglican',
            'Presbyterian',
            'Pentecostal',
            'Lutheran',
            'Coptic Orthodox',
            'Ethiopian Orthodox',
            'Sikhism',
            'Shinto',
            'Taoism',
            'Zoroastrianism',
            'Rastafari',
            'Vodou',
            'Traditional African Religions',
            'Native American Religions',
            'Others'
        ];

        foreach ($religions as $religion) {
            Religion::firstOrCreate(
                ['name' => $religion],
                [
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]
            );
        }
    }
}
