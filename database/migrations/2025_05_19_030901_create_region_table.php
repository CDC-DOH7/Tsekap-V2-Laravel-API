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
            Schema::create('region', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('country_id')->index();
                $table->string('region_code', 15)->unique();
                $table->string('region_name', 255)->unique();
                $table->timestamps();

                $table->foreign('country_id')
                    ->references('id')
                    ->on('country');
            });
        } catch (\Exception $e) {
            Log::error('Migration failed (create region): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('region', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
            });
            Schema::dropIfExists('region');
        } catch (\Exception $e) {
            Log::error('Migration rollback failed (drop region): ' . $e->getMessage());
            throw $e;
        }
    }
};
