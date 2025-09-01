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
            Schema::create('patient_injury_form_general_data', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('profile_id')->nullable();

                // Disease Reporting Unit (DRU Data)
                $table->unsignedInteger('facility_id_updated')->index();
                $table->string('name_of_reporting_facility', 255);
                $table->string('address_of_reporting_facility', 255)->nullable();
                $table->string('type_of_dru', 50);
                $table->string('type_of_patient', 50);
                $table->unsignedInteger('encoded_by')->nullable();
                $table->tinyInteger('offline_entry')->default(0);
                $table->string('hospital_case_no', 100)->nullable();

                // General Data
                $table->string('lname', 255);
                $table->string('fname', 255);
                $table->string('mname', 255)->nullable();
                $table->string('sex', 10);
                $table->date('dob');
                $table->unsignedInteger('age'); // in years this may be applicable
                $table->unsignedInteger('age_in_months')->nullable();
                $table->unsignedInteger('age_in_days')->nullable();
                $table->integer('age_bracket_id')->index();

                $table->unsignedInteger('temp_province_id')->index();
                $table->unsignedInteger('temp_municipal_id')->index();
                $table->unsignedInteger('temp_barangay_id')->index();

                $table->unsignedInteger('perm_province_id')->index();
                $table->unsignedInteger('perm_municipal_id')->index();
                $table->unsignedInteger('perm_barangay_id')->index();
                $table->string('phic_id', 50)->nullable();

                // system metadata
                $table->timestamps();

                // Foreign key constraints
                $table->foreign('facility_id_updated')->references('id')->on('facilities')->onDelete('restrict');
                $table->foreign('age_bracket_id')->references('id')->on('new_age_brackets')->onDelete('restrict');

                // temp
                $table->foreign('temp_province_id')->references('id')->on('province')->onDelete('restrict');
                $table->foreign('temp_municipal_id')->references('id')->on('muncity')->onDelete('restrict');
                $table->foreign('temp_barangay_id')->references('id')->on('barangay')->onDelete('restrict');

                // perm
                $table->foreign('perm_province_id')->references('id')->on('province')->onDelete('restrict');
                $table->foreign('perm_municipal_id')->references('id')->on('muncity')->onDelete('restrict');
                $table->foreign('perm_barangay_id')->references('id')->on('barangay')->onDelete('restrict');

                $table->foreign('profile_id')->references('id')->on('profile')->onDelete('set null');
                $table->foreign('encoded_by')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Exception $e) {
            Log::error('Migration failed (up): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('patient_injury_form_general_data', function (Blueprint $table) {
                $table->dropForeign(index: ['facility_id_updated']);
                $table->dropForeign(['age_bracket_id']);

                $table->dropForeign(['perm_province_id']);
                $table->dropForeign(['perm_municipal_id']);
                $table->dropForeign(['perm_barangay_id']);

                $table->dropForeign(['temp_province_id']);
                $table->dropForeign(['temp_municipal_id']);
                $table->dropForeign(['temp_barangay_id']);

                $table->dropForeign(['profile_id']);
                $table->dropForeign(['encoded_by']);
            });

            Schema::dropIfExists('patient_injury_form_general_data');
        } catch (\Exception $e) {
            Log::error('Migration failed (down): ' . $e->getMessage());
            throw $e;
        }
    }
};
