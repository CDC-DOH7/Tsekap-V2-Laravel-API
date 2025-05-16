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
        if (!Schema::hasTable('profiling_target')) {
            Schema::create('profiling_target', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('barangay_id'); // Foreign key for facilities table
                $table->unsignedInteger('male_population')->nullable();
                $table->unsignedInteger('female_population')->nullable();
                $table->unsignedInteger('male_target')->nullable();
                $table->unsignedInteger('female_target')->nullable();
                $table->timestamps();

                $table->foreign('barangay_id')->references('id')->on('barangay')->onDelete('cascade');
            });
        } else {
            throw new \Exception("The 'profiling_target' table does not exist.");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiling_target');
    }
};
