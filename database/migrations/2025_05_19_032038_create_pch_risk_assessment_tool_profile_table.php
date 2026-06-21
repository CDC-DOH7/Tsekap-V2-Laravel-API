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
                $table->increments('id'); // id
                $table->unsignedInteger('profile_id')->nullable(); // profile_id
                $table->unsignedInteger('facility_id_updated'); // facility_id_updated
                $table->unsignedInteger('encoded_by')->index(); // encoded_by
                $table->tinyInteger('offline_entry')->default(0); // offline_entry

                // profile and personal information
                $table->string('prefix', 15)->nullable(); // prefix
                $table->string('lname', 255); // lname
                $table->string('fname', 255); // fname
                $table->string('mname', 255)->nullable(); // mname
                $table->string('suffix', 15)->nullable(); // suffix
                $table->string('sex', 10); // sex
                $table->date('dob'); // dob
                $table->unsignedInteger('age'); // age
                $table->unsignedInteger('age_bracket_id'); // age_bracket_id
                $table->text('birth_place')->nullable(); // birth_place
                $table->string('civil_status', 20); // civil_status
                $table->string('educational_attainment', 50); // educational_attainment
                $table->string('employment_status', 50); // employment_status
                $table->string('occupation', 255)->nullable(); // occupation
                $table->string('monthly_income', 50)->nullable(); // monthly_income
                $table->string('religion', 50)->nullable(); // religion
                $table->string('other_religion', 255)->nullable(); // other_religion
                $table->string('indigenous', 50)->nullable(); // indigenous
                $table->string('blood_type', 5)->nullable(); // blood_type
                $table->string('mother_fname', 255); // mother_fname
                $table->string('mother_mname', 255)->nullable(); // mother_mname
                $table->string('mother_lname', 255); // mother_lname
                $table->date('mother_dob'); // mother_dob

                // location fields with foreign keys
                $table->unsignedInteger('country_id'); // country_id
                $table->unsignedInteger('region_id'); // region_id
                $table->unsignedInteger('province_id'); // province_id
                $table->unsignedInteger('muncity_id'); // muncity_id
                $table->unsignedInteger('barangay_id'); // barangay_id

                $table->text('number_or_street_name')->nullable(); // number_or_street_name
                $table->unsignedInteger('zip_code'); // zip_code
                $table->string('email_address', 100)->nullable(); // email_address 
                $table->string('mobile_number', 25)->nullable(); // mobile_number
                $table->string('landline_number', 25)->nullable(); // landline_number

                $table->string('family_member', 50)->nullable(); // family_member
                $table->string('dswd_nhts_member', 15)->nullable(); // dswd_nhts_member
                $table->string('four_ps_member', 15)->nullable(); // four_ps_member
                $table->string('facility_household_number', 50)->nullable(); // facility_household_number
                $table->string('family_serial_number', 50)->nullable(); // family_serial_number
                $table->string('philhealth_member', 25)->nullable(); // philhealth_member
                $table->string('philhealth_membership_type', 255)->nullable(); // philhealth_membership_type
                $table->string('philhealth_number', 50)->nullable(); // philhealth_number
                $table->string('philhealth_category', 25)->nullable(); // philhealth_category
                $table->string('pcb_eligible', 25)->nullable(); // pcb_eligible

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
