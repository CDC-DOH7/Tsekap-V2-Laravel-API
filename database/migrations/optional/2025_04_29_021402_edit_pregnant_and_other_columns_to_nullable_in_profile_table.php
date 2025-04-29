<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    // Please do not force execution if this fails to execute!
    // Manually Edit the Database Schema in Profile Table and set Pregnant and Dengvaxia to Nullable.

    public function up(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->date('pregnant')->nullable()->change();
            $table->string('dengvaxia')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->date('pregnant')->nullable(false)->change();
            $table->string('dengvaxia')->nullable(false)->change();
        });
    }
};
