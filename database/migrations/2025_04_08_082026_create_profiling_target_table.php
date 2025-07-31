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
        // Barangay
        if (!Schema::hasTable('profiling_target_per_barangay')) {
            Schema::create('profiling_target_per_barangay', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('muncity_id'); // Foreign key for muncity table
                $table->unsignedInteger('barangay_id'); // Foreign key for barangay table
                $table->unsignedInteger('male_target')->nullable();
                $table->unsignedInteger('female_target')->nullable();
                $table->unsignedInteger('total_target')->nullable(); // Total target for the barangay
                $table->timestamps();

                $table->foreign('muncity_id')->references('id')->on('muncity')->onDelete('cascade');
                $table->foreign('barangay_id')->references('id')->on('barangay')->onDelete('cascade');
            });
        } else {
            throw new \Exception("The 'profiling_target_per_barangay' table does not exist.");
        }

        if (!Schema::hasTable('profiling_total_per_barangay')) {
            Schema::create('profiling_total_per_barangay', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('muncity_id'); // Foreign key for muncity table
                $table->unsignedInteger('barangay_id'); // Foreign key for barangay table
                $table->unsignedInteger('male_population')->nullable();
                $table->unsignedInteger('female_population')->nullable();
                $table->unsignedInteger('total_population')->nullable(); // Total population for the barangay
                $table->timestamps();

                $table->foreign('muncity_id')->references('id')->on('muncity')->onDelete('cascade');
                $table->foreign('barangay_id')->references('id')->on('barangay')->onDelete('cascade');
            });
        } else {
            throw new \Exception("The 'profiling_total_per_barangay' table does not exist.");
        }

        // Muncity
        if (!Schema::hasTable('profiling_target_per_muncity')) {
            Schema::create('profiling_target_per_muncity', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('province_id'); // Foreign key for province table
                $table->unsignedInteger('muncity_id'); // Foreign key for muncity table
                $table->unsignedInteger('male_target')->nullable();
                $table->unsignedInteger('female_target')->nullable();
                $table->unsignedInteger('total_target')->nullable(); // Total target for the muncity
                $table->timestamps();

                $table->foreign('province_id')->references('id')->on('province')->onDelete('cascade');
                $table->foreign('muncity_id')->references('id')->on('muncity')->onDelete('cascade');
            });
        } else {
            throw new \Exception("The 'profiling_target_per_muncity' table does not exist.");
        }

        if (!Schema::hasTable('profiling_total_per_muncity')) {
            Schema::create('profiling_total_per_muncity', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('province_id'); // Foreign key for province table
                $table->unsignedInteger('muncity_id'); // Foreign key for muncity table
                $table->unsignedInteger('male_population')->nullable();
                $table->unsignedInteger('female_population')->nullable();
                $table->unsignedInteger('total_population')->nullable(); // Total population for the muncity
                $table->timestamps();

                $table->foreign('province_id')->references('id')->on('province')->onDelete('cascade');
                $table->foreign('muncity_id')->references('id')->on('muncity')->onDelete('cascade');
            });
        } else {
            throw new \Exception("The 'profiling_total_per_muncity' table does not exist.");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('profiling_target_per_barangay');
        Schema::dropIfExists('profiling_total_per_barangay');
    }
};
