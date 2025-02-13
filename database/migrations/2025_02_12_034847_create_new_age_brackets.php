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
            ['range' => '20-29 years old', 'description' => 'Young Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '30-39 years old', 'description' => 'Young Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '40-49 years old', 'description' => 'Middle-aged Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '50-59 years old', 'description' => 'Middle-aged Adult', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['range' => '60+ years old', 'description' => 'Senior Citizen', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
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
