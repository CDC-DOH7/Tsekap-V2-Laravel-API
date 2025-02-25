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
        Schema::table('risk_form', function (Blueprint $table) {
				$table->dropColumn([
					 'ar_refer_physician_name',
					 'ar_refer_reason',
					 'ar_refer_facility',
				]); 
		  });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_form', function (Blueprint $table) {
				$table->string('ar_refer_physician_name')->nullable();
				$table->string('ar_refer_reason')->nullable();
				$table->string('ar_refer_facility')->nullable();
		  });
    }
};
