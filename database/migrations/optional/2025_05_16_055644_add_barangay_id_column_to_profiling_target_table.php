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
            if (Schema::hasTable('profiling_target')) {
                Schema::table('profiling_target', function (Blueprint $table) {
                    // Make sure the column does not already exist to avoid errors
                    if (Schema::hasColumn('profiling_target', 'facility_id')) {
                        $table->dropColumn('facility_id');
                    }

                    if (!Schema::hasColumn('profiling_target', 'barangay_id')) {
                        $table->unsignedInteger('barangay_id')->nullable();
                        $table->foreign('barangay_id')->references('id')->on('barangay')->onDelete('cascade');
                    }
                });
            } else {
                throw new \Exception("The 'profiling_target' table does not exist.");
            }
        } catch (\Exception $e) {
            Log::error('Migration failed (add barangay_id to profiling_target): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            if (Schema::hasTable('profiling_target')) {
                Schema::table('profiling_target', function (Blueprint $table) {
                    if (Schema::hasColumn('profiling_target', 'facility_id')) {
                        $table->unsignedInteger('facility_id');
                        $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
                    }

                    if (Schema::hasColumn('profiling_target', 'barangay_id')) {
                        $table->dropForeign(['barangay_id']);
                        $table->dropColumn('barangay_id');
                    }
                });
            }
        } catch (\Exception $e) {
            Log::error('Migration rollback failed (drop barangay_id from profiling_target): ' . $e->getMessage());
            throw $e;
        }
    }
};
