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
        Schema::table('new_age_brackets', function (Blueprint $table) {
            $table->integer('age_id')->autoIncrement()->after('id');
        });

        // Copy data from `id` to `age_id`
        DB::statement('UPDATE new_age_brackets SET age_id = id');

        Schema::table('new_age_brackets', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_age_brackets', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->after('age_id');
        });

        // Copy data back to `id`
        DB::statement('UPDATE new_age_brackets SET id = age_id');

        Schema::table('new_age_brackets', function (Blueprint $table) {
            $table->dropColumn('age_id');
        });
    }
};
