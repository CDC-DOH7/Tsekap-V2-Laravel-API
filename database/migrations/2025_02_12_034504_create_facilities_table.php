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
        Schema::create('facilities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('facility_code', 100)->nullable(); 
            $table->string('name');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('abbr');
            $table->string('address');
            $table->unsignedInteger('brgy');
            $table->unsignedInteger('muncity');
            $table->unsignedInteger('province');
            $table->string('contact');
            $table->string('email');
            $table->integer('status');
            $table->string('picture')->nullable();
            $table->string('chief_hospital', 100)->nullable();
            $table->string('level')->nullable();
            $table->string('hospital_type', 45)->nullable();
            $table->integer('tricity_id')->nullable();
            $table->string('referral_used', 45)->nullable();
            $table->timestamps();

            // Adding foreign key constraints
            $table->foreign('muncity')->references('id')->on('muncity')->onDelete('cascade');
            $table->foreign('province')->references('id')->on('province')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
