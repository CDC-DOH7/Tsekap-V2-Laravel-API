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
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('platform'); // e.g., "android" or "ios"
            $table->string('latest_version'); // e.g., "2.6.0"
            $table->string('download_url')->nullable(); // APK download URL
            $table->boolean('is_force_update')->default(false); // true = must update
            $table->text('release_notes')->nullable(); // optional: changelog or release notes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
