<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the table already exists and contains data
        if (Schema::hasTable('citizenships') && DB::table('citizenships')->exists()) {
            throw new \Exception("Migration aborted: 'citizenships' table already exists and contains data.");
        }

        Schema::create('citizenships', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizenships');
    }
};
