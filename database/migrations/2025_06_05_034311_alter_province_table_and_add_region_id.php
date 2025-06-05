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
            Schema::table('province', function (Blueprint $table) {
                $table->unsignedInteger('region_id')->nullable()->after('id');
                $table->foreign('region_id')->references('id')->on('region')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            Log::error('Migration failed (Province Table): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('province', function (Blueprint $table) {
                $table->dropForeign(['region_id']);
                $table->dropColumn('region_id');
            });
        } catch (\Exception $e) {
            Log::error('Migration failed (Province Table): ' . $e->getMessage());
            throw $e;
        }
    }
};
