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
            Schema::create('pch_risk_assessment_tool_profile', function (Blueprint $table) {
                // profile metadata
                $table->increments('id');
                $table->unsignedInteger('profile_id')->nullable();
                $table->unsignedInteger('facility_id_updated');
                $table->unsignedInteger('encoded_by')->index();
                $table->tinyInteger('offline_entry')->default(0);

                // profile and personal information
                $table->string('prefix', 15)->nullable();
                $table->string('lname', 255);
                $table->string('fname', 255);
                $table->string('mname', 255)->nullable();
                $table->string('suffix', 15)->nullable();
                $table->string('sex', 10);
                $table->date('dob');
                $table->unsignedInteger('age');
                $table->unsignedInteger('age_bracket_id');
                $table->text('birth_place')->nullable();
                $table->string('civil_status', 20);
                $table->string('educational_attainment', 50);
                $table->string('employment_status', 50);
                $table->string('occupation', 255)->nullable();
                $table->string('monthly_income', 50)->nullable();
                $table->string('religion', 50)->nullable();
                $table->string('other_religion', 255)->nullable();
                $table->string('indigenous', 50)->nullable();
                $table->string('blood_type', 5)->nullable();
                $table->string('mother_fname', 255);
                $table->string('mother_mname', 255)->nullable();
                $table->string('mother_lname', 255);
                $table->date('mother_dob');

                // location fields with foreign keys
                $table->unsignedInteger('country_id');
                $table->unsignedInteger('region_id');
                $table->unsignedInteger('province_id');
                $table->unsignedInteger('muncity_id');
                $table->unsignedInteger('barangay_id');

                $table->text('number_or_street_name')->nullable();
                $table->unsignedInteger('zip_code');
                $table->string('email_address', 100)->nullable();
                $table->string('mobile_number', 25)->nullable();
                $table->string('landline_number', 25)->nullable();

                $table->string('family_member', 50)->nullable();
                $table->string('dswd_nhts_member', 15)->nullable();
                $table->string('four_ps_member', 15)->nullable();
                $table->string('facility_household_number', 50)->nullable();
                $table->string('family_serial_number', 50)->nullable();
                $table->string('philhealth_member', 25)->nullable();
                $table->string('philhealth_membership_type', 255)->nullable();
                $table->string('philhealth_number', 50)->nullable();
                $table->string('philhealth_category', 25)->nullable();
                $table->string('pcb_eligible', 25)->nullable();

                // system metadata
                $table->timestamps();

                // foreign key constraints
                $table->foreign('profile_id')->references('id')->on('profile');
                $table->foreign('facility_id_updated')->references('id')->on('facilities');
                $table->foreign('encoded_by')->references('id')->on('users');

                $table->foreign('country_id')->references('id')->on('country')->onDelete('cascade');
                $table->foreign('region_id')->references('id')->on('region')->onDelete('cascade');
                $table->foreign('province_id')->references('id')->on('province')->onDelete('cascade');
                $table->foreign('muncity_id')->references('id')->on('muncity')->onDelete('cascade');
                $table->foreign('barangay_id')->references('id')->on('barangay')->onDelete('cascade');
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
            Schema::table('pch_risk_assessment_tool_profile', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
                $table->dropForeign(['region_id']);
                $table->dropForeign(['province_id']);
                $table->dropForeign(['muncity_id']);
                $table->dropForeign(['barangay_id']);
            });

            Schema::dropIfExists('pch_risk_assessment_tool_profile');
        } catch (\Exception $e) {
            Log::error('Migration failed (down): ' . $e->getMessage());
            throw $e;
        }
    }
};
