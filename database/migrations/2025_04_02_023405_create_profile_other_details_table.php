<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::create('profile_other_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('profile_id');
                $table->string('purok_name', 100)->nullable();
                $table->string('sitio_name', 100)->nullable();
                $table->string('street_name', 100)->nullable();
                $table->timestamps();

                $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
            });
        } catch (Exception $e) {
            report($e);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('profile_other_details');
        } catch (Exception $e) {
            report($e);
        }
    }
};
