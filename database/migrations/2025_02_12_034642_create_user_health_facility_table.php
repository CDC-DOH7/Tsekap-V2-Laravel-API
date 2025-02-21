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
        Schema::dropIfExists('user_health_facility');
        
        Schema::create('user_health_facility', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('facility_id');
            $table->string('user_designation', 255)->nullable();
            $table->timestamp('assigned_at')->useCurrent();

            $table->primary(['user_id', 'facility_id']); // Composite primary key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_health_facility');
    }
};
