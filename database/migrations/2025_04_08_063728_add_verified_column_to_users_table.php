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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('verified')->default(false)->after('email'); // Adjust 'email' to the column after which you want to add 'verified'
            });
        } else {
            throw new \Exception("The 'users' table does not exist.");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('verified');
            });
        } else {
            throw new \Exception("The 'users' table does not exist.");
        }
    }
};
