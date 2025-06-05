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
            Schema::table('muncity', function (Blueprint $table) {
                $table->unsignedInteger('zip_code')->nullable()->after('province_id');
            });
        } catch (\Exception $e) {
            Log::error('Migration failed (Muncity Table): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('muncity', function (Blueprint $table) {
                $table->dropColumn('zip_code');
            });
        } catch (\Exception $e) {
            Log::error('Migration failed (Muncity Table): ' . $e->getMessage());
            throw $e;
        }
    }
};
