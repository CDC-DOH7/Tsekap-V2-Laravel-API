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
            Schema::create('patient_injury_form_preadmission_data', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('general_data_id')->index();

                // Pre-admission Data
                $table->unsignedInteger('poi_province_id')->index();
                $table->unsignedInteger('poi_municipal_id')->index();
                $table->unsignedInteger('poi_barangay_id')->index();
                $table->string('poi_purok_sitio')->nullable();
                $table->date('poi_date_of_injury');
                $table->time('poi_time_of_injury');
                $table->date('date_of_consultation');
                $table->time('time_of_consultation');

                // Injury Intent
                $table->string('injury_intent_type', 50)->nullable();
                $table->string('first_aid_given_yes_no', 10);
                $table->text('first_aid_given_by_whom')->nullable();
                $table->text('first_aid_given_what')->nullable();

                // ======= NOI = NATURE OF INJURY =======
                $table->string('noi_multiple_injuries_yes_no', 10)->nullable();

                $table->string('noi_abrasions_yes_no', 10)->nullable();
                $table->text('noi_abrasions_details')->nullable();
                $table->string('noi_abrasions_body_parts', 255)->nullable();

                $table->string('noi_avulsion_yes_no', 10)->nullable();
                $table->text('noi_avulsion_details')->nullable();
                $table->string('noi_avulsion_body_parts', 255)->nullable();

                $table->string('noi_burn_yes_no', 10)->nullable();
                $table->text('noi_burn_details')->nullable();
                $table->string('noi_burn_body_parts', 255)->nullable();
                $table->unsignedInteger('noi_burn_degree')->nullable();

                $table->string('noi_concussion_yes_no', 10)->nullable();
                $table->text('noi_concussion_details')->nullable();
                $table->string('noi_concussion_body_parts', 255)->nullable();

                $table->string('noi_contusion_yes_no', 10)->nullable();
                $table->text('noi_contusion_details')->nullable();
                $table->string('noi_contusion_body_parts', 255)->nullable();

                $table->string('noi_fracture_yes_no', 10)->nullable();
                $table->string('noi_fracture_type', 50)->nullable();
                $table->text('noi_fracture_details')->nullable();
                $table->string('noi_fracture_body_parts', 255)->nullable();

                $table->string('noi_open_wound_yes_no', 10)->nullable();
                $table->text('noi_open_wound_details')->nullable();
                $table->string('noi_open_wound_body_parts', 255)->nullable();

                $table->string('noi_traumatic_amputation_yes_no', 10)->nullable();
                $table->text('noi_traumatic_amputation_details')->nullable();
                $table->string('noi_traumatic_amputation_body_parts', 255)->nullable();

                $table->string('noi_others_yes_no', 10)->nullable();
                $table->string('noi_others_specify', 50)->nullable();
                $table->text('noi_others_details')->nullable();
                $table->string('noi_others_body_parts', 255)->nullable();
                // ========== END NOI = NATURE OF INJURY ==========

                // ======= ECI = EXTERNAL CAUSE/S OF INJURY =======
                $table->string('eci_bites_stings_yes_no', 10)->nullable();
                $table->text('eci_bites_stings_details')->nullable();

                $table->string('eci_burns_yes_no', 10)->nullable();
                $table->text('eci_burns_details')->nullable();
                $table->text('eci_burns_specify_others')->nullable();

                $table->string('eci_chemical_substance_yes_no', 10)->nullable();
                $table->text('eci_chemical_substance_details')->nullable();

                $table->string('eci_contact_with_sharps_yes_no', 10)->nullable();
                $table->text('eci_contact_with_sharps_details')->nullable();

                $table->string('eci_drowning_yes_no', 10)->nullable();
                $table->text('eci_drowning_details')->nullable();
                $table->text('eci_drowning_specify_others')->nullable();

                $table->string('eci_exposure_to_force_of_nature_yes_no', 10)->nullable();
                $table->text('eci_exposure_to_force_of_nature_details')->nullable();

                $table->string('eci_fall_yes_no', 10)->nullable();
                $table->text('eci_fall_details')->nullable();

                $table->string('eci_firecracker_yes_no', 10)->nullable();
                $table->text('eci_firecracker_details')->nullable();

                $table->string('eci_sa_or_alleged_rape_yes_no', 10)->nullable();
                $table->text('eci_sa_or_alleged_rape_details')->nullable();

                $table->string('eci_gunshot_yes_no', 10)->nullable();
                $table->text('eci_gunshot_details')->nullable();

                $table->string('eci_hanging_strangulation_yes_no', 10)->nullable();
                $table->text('eci_hanging_strangulation_details')->nullable();

                $table->string('eci_mauling_assaults_yes_no', 10)->nullable();
                $table->text('eci_mauling_assaults_details')->nullable();

                $table->string('eci_vehicular_accident_yes_no', 10)->nullable();
                $table->text('eci_vehicular_accident_details')->nullable();
                // ========== END ECI = EXTERNAL CAUSE/S OF INJURY ==========

                // ======= COI = CAUSES OF INJURY =======
                $table->string('coi_vehicular_accident_location', 10)->nullable();
                $table->string('coi_vehicular_accident_type', 50)->nullable();

                $table->string('coi_patients_vehicle', 50)->nullable();
                $table->text('coi_patients_vehicle_specify_others')->nullable();

                $table->string('coi_other_vehicle_or_object_involved', 50)->nullable();
                $table->text('coi_other_vehicle_or_object_involved_specify_others')->nullable();

                $table->string('coi_act_of_pat_at_time_of_incident', 50)->nullable();
                $table->text('coi_act_of_pat_at_time_of_incident_specify_others')->nullable();
                $table->string('coi_oth_risk_factors_at_the_time_of_the_incident', 50)->nullable();
                $table->text('coi_oth_risk_factors_at_the_time_of_the_incident_specify_others')->nullable();

                $table->string('coi_safety', 70)->nullable();
                $table->text('coi_safety_specify_others')->nullable();
                $table->string('coi_position_of_patient', 50)->nullable();
                $table->text('coi_position_of_patient_specify_others')->nullable();
                $table->string('coi_place_of_occurrence', 50)->nullable();
                $table->text('coi_place_of_occurrence_specify_workplace')->nullable();
                $table->text('coi_place_of_occurrence_specify_others')->nullable();
                // ========== END ECI = EXTERNAL CAUSE/S OF INJURY ==========

                // Hospital or Facility Data
                $table->string('hfd_er_opd_bhs_rhu_yes_no', 10)->nullable();
                $table->string('hfd_transferred_from_other_facility_yes_no', 10)->nullable();
                $table->string('hfd_referred_by_other_other_facility_yes_no', 10)->nullable();
                $table->text('hfd_originating_facility')->nullable();
                $table->string('hfd_status_on_arrival', 50)->nullable();
                $table->string('hfd_status_on_arrival_conscious', 50)->nullable();
                $table->string('hfd_transport_mode', 50)->nullable();
                $table->text('hfd_transport_mode_specify_others')->nullable();
                $table->text('hfd_initial_impression')->nullable();
                $table->string('hfd_icd10_code_nature_of_injury', 100)->nullable();
                $table->string('hfd_icd10_code_external_cause_of_injury', 100)->nullable();
                $table->string('hfd_disposition', 100)->nullable();
                $table->string('hfd_disposition_specify', 100)->nullable();
                $table->string('hfd_outcome', 50)->nullable();

                $table->string('hfd_in_patient_admitted_yes_no', 10)->nullable();
                $table->text('hfd_in_patient_complete_final_diagnosis')->nullable();
                $table->string('hfd_in_patient_disposition', 100)->nullable();
                $table->string('hfd_in_patient_disposition_specify', 100)->nullable();
                $table->string('hfd_in_patient_outcome', 50)->nullable();
                $table->string('hfd_in_patient_icd10_code_nature_of_injury', 100)->nullable();
                $table->string('hfd_in_patient_icd10_code_external_cause_of_injury', 100)->nullable();

                $table->foreign('general_data_id')->references('id')->on('patient_injury_form_general_data')->onDelete('cascade');
                $table->foreign('poi_province_id')->references('id')->on('province')->onDelete('restrict');
                $table->foreign('poi_municipal_id')->references('id')->on('muncity')->onDelete('restrict');
                $table->foreign('poi_barangay_id')->references('id')->on('barangay')->onDelete('restrict');

                // timestamps
                $table->timestamps();
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
            Schema::table('patient_injury_form_preadmission_data', function (Blueprint $table) {
                $table->dropForeign(['general_data_id']);
                $table->dropForeign(['poi_province_id']);
                $table->dropForeign(['poi_municipal_id']);
                $table->dropForeign(['poi_barangay_id']);
            });

            Schema::dropIfExists('patient_injury_form_preadmission_data');
        } catch (\Exception $e) {
            Log::error("Migration failed (down): " . $e->getMessage());
            throw $e;
        }
    }
};
