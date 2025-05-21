<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::create('country', function (Blueprint $table) {
                $table->increments('id');
                $table->string('country_code', 5)->unique();
                $table->string('country_name', 255)->unique();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Migration failed (create country table): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('country');
        } catch (\Exception $e) {
            Log::error('Migration rollback failed (drop country table): ' . $e->getMessage());
            throw $e;
        }
    }
};
