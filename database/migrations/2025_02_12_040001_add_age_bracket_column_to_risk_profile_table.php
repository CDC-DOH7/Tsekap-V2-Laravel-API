<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if any record in risk_profile has a non-null age_bracket_id
        $exists = DB::table('risk_profile')->whereNotNull('age_bracket_id')->exists();

        if ($exists) {
            throw new \Exception("Migration aborted: 'risk_profile' table already contains 'age_bracket_id' values.");
        }

        Schema::table('risk_profile', function (Blueprint $table) {
            $table->integer('age_bracket_id')->nullable()->after('age'); // Adjust as needed
            $table->foreign('age_bracket_id')->references('id')->on('new_age_brackets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_profile', function (Blueprint $table) {
            $table->dropForeign(['age_bracket_id']);
            $table->dropColumn('age_bracket_id');
        });
    }
};
