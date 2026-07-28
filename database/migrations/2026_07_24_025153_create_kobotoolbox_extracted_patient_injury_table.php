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
            Schema::create('kobotoolbox_extracted_patient_injury', function (Blueprint $table) {
                $table->increments('id');

                // Kobotool Data
                $table->datetime('start')->nullable(); // start, DATETIME - NOT NULL
                $table->datetime('end')->nullable(); // end, DATETIME - NOT NULL 
                $table->text('name_of_reporting_facility'); // name_of_reporting_facility, STRING/VARCHAR - NOT NULL
                $table->text('type_of_dru')->nullable(); // type_of_dru, STRING/VARCHAR - NULLABLE
                $table->text('address_of_dru_province_huc')->nullable(); // address_of_dru_province_huc, STRING/VARCHAR - NULLABLE
                $table->text('type_of_patient')->nullable(); // type_of_patient, STRING/VARCHAR - NULLABLE
                $table->text('hospital_case_no')->nullable(); // hospital_case_no, STRING/VARCHAR - NULLABLE
                $table->text('last_name')->nullable(); // last_name, STRING/VARCHAR - NULLABLE
                $table->text('first_name')->nullable(); // first_name, STRING/VARCHAR - NULLABLE
                $table->text('middle_name')->nullable(); // middle_name, STRING/VARCHAR - NULLABLE
                $table->text('sex')->nullable(); // sex, STRING/VARCHAR - NULLABLE
                $table->date('date_of_birth')->nullable(); // date_of_birth, DATE - NULLABLE
                $table->unsignedInteger('age')->nullable(); // age, UNSIGNED INTEGER - NULLABLE
                $table->text('age_in_days_months_years')->nullable(); // age_in_days_months_years, STRING/VARCHAR - NULLABLE
                $table->text('permanent_province')->nullable(); // permanent_province, STRING/VARCHAR - NULLABLE
                $table->text('permanent_muncity')->nullable(); // permanent_muncity, STRING/VARCHAR - NULLABLE
                $table->text('permanent_barangay')->nullable(); // permanent_barangay, STRING/VARCHAR - NULLABLE
                $table->text('temporary_province')->nullable(); // temporary_province, STRING/VARCHAR - NULLABLE
                $table->text('temporary_muncity')->nullable(); // temporary_muncity, STRING/VARCHAR - NULLABLE
                $table->text('temporary_barangay')->nullable(); // temporary_barangay, STRING/VARCHAR - NULLABLE
                $table->text('philhealth_number')->nullable(); // philhealth_number, STRING/VARCHAR - NULLABLE
                $table->text('place_of_injury_province')->nullable(); // place_of_injury_province, STRING/VARCHAR - NULLABLE
                $table->text('place_of_injury_muncity')->nullable(); // place_of_injury_muncity, STRING/VARCHAR - NULLABLE
                $table->text('place_of_injury_barangay')->nullable(); // place_of_injury_barangay, STRING/VARCHAR - NULLABLE
                $table->text('place_of_injury_sitio_purok_street')->nullable(); // place_of_injury_sitio_purok_street, STRING/VARCHAR - NULLABLE
                $table->text('date_and_time_of_injury')->nullable(); // date_and_time_of_injury, DATETIME - NULLABLE
                $table->text('date_and_time_of_consultation')->nullable(); // date_and_time_of_consultation, DATETIME - NULLABLE
                $table->text('injury_intent')->nullable(); // injury_intent, STRING/VARCHAR - NULLABLE
                $table->text('first_aid_given')->nullable(); // first_aid_given, STRING/VARCHAR - NULLABLE
                $table->text('what_first_aid_was_given')->nullable(); // what_first_aid_was_given, STRING/VARCHAR - NULLABLE
                $table->text('who_gave_the_first_aid')->nullable(); // who_gave_the_first_aid, STRING/VARCHAR - NULLABLE
                $table->text('multiple_injury_ies')->nullable(); // multiple_injury_ies, STRING/VARCHAR - NULLABLE
                $table->text('noi_all')->nullable();
                $table->text('noi_abrasion')->nullable();
                $table->text('noi_avulsion')->nullable();
                $table->text('noi_burn')->nullable();
                $table->text('noi_concussion')->nullable();
                $table->text('noi_contusion')->nullable();
                $table->text('noi_fracture')->nullable();
                $table->text('noi_open_wound')->nullable();
                $table->text('noi_trauma_amp')->nullable();
                $table->text('noi_others')->nullable();
                $table->text('abrasion_details')->nullable(); // abrasion_details, STRING/VARCHAR - NULLABLE
                $table->text('avulsion_details')->nullable(); // avulsion_details, STRING/VARCHAR - NULLABLE
                $table->text('burn_details')->nullable(); // burn_details, STRING/VARCHAR - NULLABLE
                $table->text('burn_site')->nullable(); // burn_site, STRING/VARCHAR - NULLABLE
                $table->text('concussion_details')->nullable(); // concussion_details, STRING/VARCHAR - NULLABLE
                $table->text('contusion_details')->nullable(); // contusion_details, STRING/VARCHAR - NULLABLE
                $table->text('fracture_details')->nullable(); // fracture_details, STRING/VARCHAR - NULLABLE
                $table->text('fracture_type_details')->nullable(); // fracture_type_details, STRING/VARCHAR - NULLABLE
                $table->text('open_wound_details')->nullable(); // open_wound_details, STRING/VARCHAR - NULLABLE
                $table->text('traumatic_amputation_details')->nullable(); // traumatic_amputation_details, STRING/VARCHAR - NULLABLE
                $table->text('noi_other_details')->nullable(); // noi_other_details
                $table->text('ext_causes')->nullable(); // ext_causes
                $table->text('ext_bites_stings')->nullable();
                $table->text('ext_burns')->nullable();
                $table->text('ext_chemical_subs')->nullable(); // ext_chemical_subs
                $table->text('ext_sharp_objects')->nullable(); // ext_sharp_objects
                $table->text('ext_drowning')->nullable();
                $table->text('ext_expo_nature')->nullable(); // ext_expo_nature
                $table->text('ext_fall')->nullable();
                $table->text('ext_firecracker')->nullable();
                $table->text('ext_sexual_assault_abuse_rape_alleged')->nullable(); // ext_sexual_assault_abuse_rape_alleged
                $table->text('ext_gunshot')->nullable(); // ext_gunshot
                $table->text('ext_hanging_strangulation')->nullable(); // ext_hanging_strangulation
                $table->text('ext_mauling_assault')->nullable(); // ext_mauling_assault
                $table->text('ext_transport_vehicular_accident')->nullable(); // ext_transport_vehicular_accident
                $table->text('ext_others')->nullable();
                $table->text('bite_sting_details')->nullable(); // bite_sting_details
                $table->text('burns_details')->nullable(); // burns_details
                $table->text('chemical_subs_details')->nullable(); // chemical_subs_details
                $table->text('contact_with_sharp_objects_details')->nullable(); // contact_with_sharp_objects_details
                $table->text('drowning_details')->nullable(); // drowning_details
                $table->text('expo_nature_details')->nullable(); // expo_nature_details
                $table->text('fall_details')->nullable(); // fall_details
                $table->text('firecracker_details')->nullable(); // firecracker_details
                $table->text('gunshot_details')->nullable(); // gunshot_details
                $table->text('hanging_strangulation_details')->nullable(); // hanging_strangulation_details
                $table->text('mauling_assault_details')->nullable(); // mauling_assault_details
                $table->text('transport_vehicular_accident_details')->nullable(); // transport_vehicular_accident_details
                $table->text('ext_coi_other_details')->nullable(); // ext_coi_other_details
                $table->text('for_transport_vehicular_accidents_only')->nullable(); // for_transport_vehicular_accidents_only
                $table->text('vehicular_accident_type')->nullable(); // vehicular_accident_type
                $table->text('vehicles_involved_patients_vehicle')->nullable(); // vehicles_involved_patients_vehicle
                $table->text('vehicles_involved_patients_vehicle_others_details')->nullable(); // vehicles_involved_patients_vehicle_others_details
                $table->text('vehicles_involved_other_vehicle_object_involved')->nullable(); // vehicles_involved_other_vehicle_object_involved
                $table->text('vehicles_involved_other_vehicle_object_involved_others_details')->nullable(); // vehicles_involved_other_vehicle_object_involved_others_details
                $table->text('position_of_patient')->nullable(); // position_of_patient
                $table->text('position_of_patient_others_details')->nullable(); // position_of_patient_others_details
                $table->text('place_of_occurrence')->nullable(); // place_of_occurrence, STRING/VARCHAR - NULLABLE
                $table->text('workplace_specify')->nullable(); // workplace_specify, STRING/VARCHAR - NULLABLE
                $table->text('place_of_occurence_others_details')->nullable(); // place_of_occurence_others_details
                $table->text('activity')->nullable(); // activity
                $table->text('activity_details')->nullable(); // activity_details
                $table->text('other_risk_all')->nullable(); // other_risk_all
                $table->text('other_risk_alcohol_liquor')->nullable(); // other_risk_alcohol_liquor
                $table->text('other_risk_mobile_phone')->nullable(); // other_risk_mobile_phone
                $table->text('other_risk_sleepy')->nullable();
                $table->text('other_risk_smoking')->nullable();
                $table->text('other_risk_others')->nullable();
                $table->text('other_risk_other_details')->nullable(); // other_risk_other_details
                $table->text('safety_all')->nullable();
                $table->text('safety_none')->nullable();
                $table->text('safety_childseat')->nullable();
                $table->text('safety_airbag')->nullable();
                $table->text('safety_lifevest')->nullable(); // lifevest_lifejacket_flotation_device
                $table->text('safety_helmet')->nullable();
                $table->text('safety_seatbelt')->nullable();
                $table->text('safety_unknown')->nullable();
                $table->text('safety_others')->nullable();
                $table->text('safety_others_details')->nullable(); // safety_others_details
                $table->text('type_of_patient_hospital_facility_data')->nullable(); // type_of_patient_hospital_facility_data
                $table->text('transferred_hosp_op')->nullable(); // transferred_hosp_op
                $table->text('referred_hosp_op')->nullable(); // referred_hosp_op
                $table->text('orig_physician_op')->nullable(); // orig_physician_op
                $table->text('status_reach_op')->nullable(); // status_reach_op
                $table->text('if_alive_op')->nullable(); // if_alive_op
                $table->text('mot_to_the_hospital_facility_op')->nullable(); // mot_to_the_hospital_facility_op
                $table->text('mot_to_the_hospital_others_details_op')->nullable(); // mot_to_the_hospital_others_details_op
                $table->text('initial_impression_op')->nullable(); // initial_impression_op
                $table->text('icd_10_nature_op')->nullable(); // icd_10_nature_op
                $table->text('icd_10_ext_op')->nullable(); // icd_10_ext_op
                $table->text('disposition_op')->nullable(); // disposition_op, STRING/VARCHAR - NULLABLE
                $table->text('specify_facility_transferred_to_op')->nullable(); // specify_facility_transferred_to_op
                $table->text('outcome_op')->nullable(); // outcome_op, STRING/VARCHAR - NULLABLE
                $table->text('initial_admitting_final_diagnosis_ip')->nullable(); // initial_admitting_final_diagnosis_ip
                $table->text('disposition_ip')->nullable(); // disposition_ip, STRING/VARCHAR - NULLABLE
                $table->text('facility_transfered_to_ip')->nullable(); // facility_transfered_to_ip, STRING/VARCHAR - NULLABLE
                $table->text('disposition_others_details_ip')->nullable(); // disposition_others_details_ip
                $table->text('outcome_ip')->nullable(); // outcome_ip, STRING/VARCHAR - NULLABLE
                $table->text('icd_10_nature_ip')->nullable(); // icd_10_nature_ip
                $table->text('icd_10_ext_ip')->nullable(); // icd_10_ext_ip
                $table->text('name_of_encoder')->nullable(); // name_of_encoder
                $table->text('designation_of_encoder')->nullable(); // designation_of_encoder
                $table->text('contact_number_of_encoder')->nullable(); // contact_number_of_encoder
                $table->text('address_of_dru')->nullable(); // address_of_dru, STRING/VARCHAR - NULLABLE
                $table->text('_id')->nullable(); // _id, BIGINT - NULLABLE
                $table->text('uuid')->nullable(); // uuid, STRING/VARCHAR - NULLABLE
                $table->text('submission_time')->nullable(); // submission_time, DATETIME - NULLABLE
                $table->text('validation_status')->nullable(); // validation_status, STRING/VARCHAR - NULLABLE
                $table->text('notes')->nullable(); // notes, STRING/VARCHAR - NULLABLE
                $table->text('status')->nullable(); // status, STRING/VARCHAR - NULLABLE
                $table->text('submitted_by')->nullable(); // submitted_by, STRING/VARCHAR - NULLABLE
                $table->text('version')->nullable(); // version, STRING/VARCHAR - NULLABLE
                $table->text('tags')->nullable(); // tags, STRING/VARCHAR - NULLABLE
                $table->text('meta_root_uuid')->nullable(); // meta_root_uuid, STRING/VARCHAR - NULLABLE
                $table->text('index')->nullable(); // index, BIGINT - NULLABLE

                // system metadata
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
            Schema::dropIfExists('kobotoolbox_extracted_patient_injury');
        } catch (\Exception $e) {
            Log::error('Migration failed (down): ' . $e->getMessage());
            throw $e;
        }
    }
};
