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
        // Check if the table already has data
        $exists = DB::table('new_age_brackets')->exists();

        if ($exists) {
            throw new \Exception("Migration aborted: 'new_age_brackets' table already contains data.");
        }

        Schema::create('new_age_brackets', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('name');
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->timestamps();
        });

        DB::table('new_age_brackets')->insert([
            ['name' => 'Infant', 'min_age' => null, 'max_age' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Child', 'min_age' => 1, 'max_age' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Adolescent', 'min_age' => 10, 'max_age' => 19, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Adult', 'min_age' => 20, 'max_age' => 59, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Senior Citizen', 'min_age' => 60, 'max_age' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
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
