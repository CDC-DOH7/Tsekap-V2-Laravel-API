<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('new_age_brackets', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('range');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('new_age_brackets')->insert([
            ['range' => '0-6 days', 'description' => 'Infant', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '7-28 days', 'description' => 'Infant', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '29 days-11 months', 'description' => 'Infant', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '1-4 years old', 'description' => 'Preschool Age Children', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '5-9 years old', 'description' => 'School Age Children', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '10-14 years old', 'description' => 'Adolescent', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '15-19 years old', 'description' => 'Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '20-24 years old', 'description' => 'Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '25-29 years old', 'description' => 'Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '30-34 years old', 'description' => 'Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '35-39 years old', 'description' => 'Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '40-44 years old', 'description' => 'Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '45-49 years old', 'description' => 'Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '50-54 years old', 'description' => 'Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '55-59 years old', 'description' => 'Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '60-64 years old', 'description' => 'Senior Citizen', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '65-69 years old', 'description' => 'Senior Citizen', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '70 years and above', 'description' => 'Senior Citizen', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_age_brackets');
    }
};
