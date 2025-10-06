<?php

namespace Database\Seeders;

use App\Models\AppVersion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AppVersion::create([
            'platform' => 'android',
            'latest_version' => '1.2.0',
            'download_url' => 'https://drive.usercontent.google.com/download?id=1CBHfIETwMTPcZevFcEvOG65MVFsJ6gfe&export=download&authuser=0',
            'is_force_update' => false,
            'release_notes' => 'Initial release.',
        ]);
    }
}
