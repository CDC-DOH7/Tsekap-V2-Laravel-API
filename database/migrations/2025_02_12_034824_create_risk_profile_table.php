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
        Schema::create('risk_profile', function (Blueprint $table) {
            $table->increments('id'); // Primary key with auto-increment
            $table->unsignedInteger('profile_id')->nullable();
            $table->string('lname', 255);
            $table->string('fname', 255);
            $table->string('mname', 255)->nullable();
            $table->string('suffix', 255)->nullable();
            $table->string('sex', 10);
            $table->date('dob');
            $table->integer('age'); // Age
            $table->unsignedInteger('age_bracket_id')->nullable()->index();
            $table->string('civil_status', 25); // Civil status
            $table->string('religion', 50); // Religion
            $table->string('other_religion', 255)->nullable();;
            $table->string('contact', 20);
            $table->integer('province_id'); // Province ID
            $table->integer('municipal_id'); // Municipal ID
            $table->integer('barangay_id'); // Barangay ID
            $table->string('street', 255)->nullable(); // Street, nullable
            $table->string('purok', 255)->nullable(); // Purok, nullable
            $table->string('sitio', 255)->nullable(); // Sitio, nullable
            $table->string('phic_id', 255)->nullable();
            $table->string('pwd_id', 255)->nullable();
            $table->string('citizenship', 50);
            $table->string('other_citizenship', 255)->nullable();
            $table->string('indigenous_person', 50);
            $table->string('employment_status', 25);
            $table->string('facility_id_updated', 255);
            $table->tinyInteger('offline_entry')->nullable()->default(0);
            $table->unsignedInteger('encoded_by')->index();

            // Metadata
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_profile');
    }
};
