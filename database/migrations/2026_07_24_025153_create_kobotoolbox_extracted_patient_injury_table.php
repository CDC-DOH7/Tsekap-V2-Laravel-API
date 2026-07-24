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
            Schema::table('kobotoolbox_extracted_patient_injury', function (Blueprint $table) {
                $table->increments('id');

                // Kobotool Data
                $table->text('start')->nullable(); // start
                $table->text('end')->nullable(); // end
                $table->text('name_of_reporting_facility')->nullable(); // name_of_reporting_facility
                $table->text('type_of_dru')->nullable(); // type_of_dru 
                $table->text('address_of_dru_province_huc')->nullable(); // address_of_dru_province_huc
                $table->text('type_of_patient')->nullable(); // type_of_patient
                $table->text('hospital_case_no')->nullable(); // hospital_case_no
                $table->text('last_name')->nullable(); // last_name
                $table->text('first_name')->nullable(); // first_name
                $table->text('middle_name')->nullable(); // middle_name
                $table->text('sex')->nullable(); // sex
                $table->text('date_of_birth')->nullable(); // date_of_birth
                $table->text('age')->nullable(); // age
                $table->text('age_in_days_months_years')->nullable(); // age_in_days_months_years
                $table->text('permanent_province')->nullable(); // permanent_province
                $table->text('permanent_muncity')->nullable(); // permanent_muncity
                $table->text('permanent_barangay')->nullable(); // permanent_barangay
                $table->text('temporary_province')->nullable(); // temporary_province
                $table->text('temporary_muncity')->nullable(); // temporary_muncity
                $table->text('temporary_barangay')->nullable(); // temporary_barangay
                $table->text('philhealth_number')->nullable(); // philhealth_number
                $table->text('place_of_injury_province')->nullable(); // place_of_injury_province
                $table->text('place_of_injury_muncity')->nullable(); // place_of_injury_muncity
                $table->text('place_of_injury_barangay')->nullable(); // place_of_injury_barangay
                $table->text('place_of_injury_sitio_purok_street')->nullable(); // place_of_injury_sitio_purok_street
                $table->text('date_and_time_of_injury')->nullable(); // date_and_time_of_injury
                $table->text('date_and_time_of_consultation')->nullable(); // date_and_time_of_consultation
                $table->text('injury_intent')->nullable(); // injury_intent
                $table->text('first_aid_given')->nullable(); // first_aid_given
                $table->text('what_first_aid_was_given')->nullable(); // what_first_aid_was_given
                $table->text('who_gave_the_first_aid')->nullable(); // who_gave_the_first_aid
                $table->text('multiple_injury_ies')->nullable(); // multiple_injury_ies
                $table->text('nature_of_injuries_select_all_applicable')->nullable(); // nature_of_injuries_select_all_applicable
                $table->text('nature_of_injuries_select_all_applicable_abrasion')->nullable(); // nature_of_injuries_select_all_applicable_abrasion
                $table->text('nature_of_injuries_select_all_applicable_avulsion')->nullable(); // nature_of_injuries_select_all_applicable_avulsion
                $table->text('nature_of_injuries_select_all_applicable_burn')->nullable(); // nature_of_injuries_select_all_applicable_burn
                $table->text('nature_of_injuries_select_all_applicable_concussion')->nullable(); // nature_of_injuries_select_all_applicable_concussion
                $table->text('nature_of_injuries_select_all_applicable_contusion')->nullable(); // nature_of_injuries_select_all_applicable_contusion
                $table->text('nature_of_injuries_select_all_applicable_fracture')->nullable(); // nature_of_injuries_select_all_applicable_fracture
                $table->text('nature_of_injuries_select_all_applicable_open_wound')->nullable(); // nature_of_injuries_select_all_applicable_open_wound
                $table->text('nature_of_injuries_select_all_applicable_traumatic_amputation')->nullable(); // nature_of_injuries_select_all_applicable_traumatic_amputation
                $table->text('nature_of_injuries_select_all_applicable_others_please_specify_injury_and_the_body_parts_affected')->nullable(); // nature_of_injuries_select_all_applicable_others_please_specify_injury_and_the_body_parts_affected
                $table->text('abrasion_details')->nullable(); // abrasion_details
                $table->text('avulsion_details')->nullable(); // avulsion_details
                $table->text('burn_details')->nullable(); // burn_details
                $table->text('burn_site')->nullable(); // burn_site
                $table->text('concussion_details')->nullable(); // concussion_details
                $table->text('contusion_details')->nullable(); // contusion_details
                $table->text('fracture_details')->nullable(); // fracture_details
                $table->text('fracture_type_details')->nullable(); // fracture_type_details
                $table->text('open_wound_details')->nullable(); // open_wound_details
                $table->text('traumatic_amputation_details')->nullable(); // traumatic_amputation_details
                $table->text('nature_of_injuries_others_details')->nullable(); // nature_of_injuries_others_details
                $table->text('external_causes_of_injury_ies')->nullable(); // external_causes_of_injury_ies
                $table->text('external_causes_of_injury_ies_bites_stings')->nullable(); // external_causes_of_injury_ies_bites_stings
                $table->text('external_causes_of_injury_ies_burns')->nullable(); // external_causes_of_injury_ies_burns
                $table->text('external_causes_of_injury_ies_chemical_substance')->nullable(); // external_causes_of_injury_ies_chemical_substance
                $table->text('external_causes_of_injury_ies_contact_with_sharp_objects')->nullable(); // external_causes_of_injury_ies_contact_with_sharp_objects
                $table->text('external_causes_of_injury_ies_drowning')->nullable(); // external_causes_of_injury_ies_drowning
                $table->text('external_causes_of_injury_ies_exposure_to_forces_of_nature')->nullable(); // external_causes_of_injury_ies_exposure_to_forces_of_nature
                $table->text('external_causes_of_injury_ies_fall')->nullable(); // external_causes_of_injury_ies_fall
                $table->text('external_causes_of_injury_ies_firecracker')->nullable(); // external_causes_of_injury_ies_firecracker
                $table->text('external_causes_of_injury_ies_sexual_assault_sexual_abuse_rape_alleged')->nullable(); // external_causes_of_injury_ies_sexual_assault_sexual_abuse_rape_alleged
                $table->text('external_causes_of_injury_ies_gunshot')->nullable(); // external_causes_of_injury_ies_gunshot
                $table->text('external_causes_of_injury_ies_hanging_strangulation')->nullable(); // external_causes_of_injury_ies_hanging_strangulation
                $table->text('external_causes_of_injury_ies_mauling_assault')->nullable(); // external_causes_of_injury_ies_mauling_assault
                $table->text('external_causes_of_injury_ies_transport_vehicular_accident')->nullable(); // external_causes_of_injury_ies_transport_vehicular_accident
                $table->text('external_causes_of_injury_ies_others')->nullable(); // external_causes_of_injury_ies_others
                $table->text('bite_sting_details')->nullable(); // bite_sting_details
                $table->text('burns_details')->nullable(); // burns_details
                $table->text('chemical_substance_details')->nullable(); // chemical_substance_details
                $table->text('contact_with_sharp_objects_details')->nullable(); // contact_with_sharp_objects_details
                $table->text('drowning_details')->nullable(); // drowning_details
                $table->text('exposure_to_forces_of_nature_details')->nullable(); // exposure_to_forces_of_nature_details
                $table->text('fall_details')->nullable(); // fall_details 
                $table->text('firecracker_details')->nullable(); // firecracker_details
                $table->text('gunshot_details')->nullable(); // gunshot_details
                $table->text('hanging_strangulation_details')->nullable(); // hanging_strangulation_details
                $table->text('mauling_assault_details')->nullable(); // mauling_assault_details
                $table->text('transport_vehicular_accident_details')->nullable(); // transport_vehicular_accident_details
                $table->text('external_causes_of_injury_ies_others_details')->nullable(); // external_causes_of_injury_ies_others_details
                $table->text('for_transport_vehicular_accidents_only')->nullable(); // for_transport_vehicular_accidents_only
                $table->text('vehicular_accident_type')->nullable(); // vehicular_accident_type
                $table->text('vehicles_involved_patients_vehicle')->nullable(); // vehicles_involved_patients_vehicle
                $table->text('vehicles_involved_patients_vehicle_others_details')->nullable(); // vehicles_involved_patients_vehicle_others_details
                $table->text('vehicles_involved_other_vehicle_object_involved')->nullable(); // vehicles_involved_other_vehicle_object_involved
                $table->text('vehicles_involved_other_vehicle_object_involved_others_details')->nullable(); // vehicles_involved_other_vehicle_object_involved_others_details
                $table->text('position_of_patient')->nullable(); // position_of_patient
                $table->text('position_of_patient_others_details')->nullable(); // position_of_patient_others_details
                $table->text('place_of_occurrence')->nullable(); // place_of_occurrence
                $table->text('workplace_specify')->nullable(); // workplace_specify
                $table->text('place_of_occurence_others_details')->nullable(); // place_of_occurence_others_details
                $table->text('activity_of_the_patient_at_the_time_of_incident')->nullable(); // activity_of_the_patient_at_the_time_of_incident
                $table->text('activity_of_the_patient_at_the_time_of_incident_others_details')->nullable(); // activity_of_the_patient_at_the_time_of_incident_others_details
                $table->text('other_risk_factors_at_the_time_of_the_incident')->nullable(); // other_risk_factors_at_the_time_of_the_incident
                $table->text('other_risk_factors_at_the_time_of_the_incident_alcohol_liquor')->nullable(); // other_risk_factors_at_the_time_of_the_incident_alcohol_liquor
                $table->text('other_risk_factors_at_the_time_of_the_incident_using_mobile_phone')->nullable(); // other_risk_factors_at_the_time_of_the_incident_using_mobile_phone
                $table->text('other_risk_factors_at_the_time_of_the_incident_sleepy')->nullable(); // other_risk_factors_at_the_time_of_the_incident_sleepy
                $table->text('other_risk_factors_at_the_time_of_the_incident_smoking')->nullable(); // other_risk_factors_at_the_time_of_the_incident_smoking
                $table->text('other_risk_factors_at_the_time_of_the_incident_others')->nullable(); // other_risk_factors_at_the_time_of_the_incident_others
                $table->text('other_risk_factors_at_the_time_of_the_incident_other_details')->nullable(); // other_risk_factors_at_the_time_of_the_incident_other_details
                $table->text('safety_select_all_that_applies')->nullable(); // safety_select_all_that_applies
                $table->text('safety_select_all_that_applies_none')->nullable(); // safety_select_all_that_applies_none
                $table->text('safety_select_all_that_applies_childseat')->nullable(); // safety_select_all_that_applies_childseat
                $table->text('safety_select_all_that_applies_airbag')->nullable(); // safety_select_all_that_applies_airbag
                $table->text('safety_select_all_that_applies_lifevest_lifejacket_flotation_device')->nullable(); // safety_select_all_that_applies_lifevest_lifejacket_flotation_device
                $table->text('safety_select_all_that_applies_helmet')->nullable(); // safety_select_all_that_applies_helmet
                $table->text('safety_select_all_that_applies_seatbelt')->nullable(); // safety_select_all_that_applies_seatbelt
                $table->text('safety_select_all_that_applies_unknown')->nullable(); // safety_select_all_that_applies_unknown
                $table->text('safety_select_all_that_applies_others')->nullable(); // safety_select_all_that_applies_others
                $table->text('safety_select_all_that_applies_others_details')->nullable(); // safety_select_all_that_applies_others_details
                $table->text('type_of_patient_hospital_facility_data')->nullable(); // type_of_patient_hospital_facility_data
                $table->text('transferred_from_another_hospital_facility')->nullable(); // transferred_from_another_hospital_facility
                $table->text('referred_by_another_hospital_facility_for_laboratory_and_or_other_medical_procedures')->nullable(); // referred_by_another_hospital_facility_for_laboratory_and_or_other_medical_procedures
                $table->text('name_of_originating_hospital_physician')->nullable(); // name_of_originating_hospital_physician
                $table->text('status_upon_reaching_the_facility')->nullable(); // status_upon_reaching_the_facility
                $table->text('if_alive')->nullable(); // if_alive
                $table->text('mode_of_transport_to_the_hospital_facility')->nullable(); // mode_of_transport_to_the_hospital_facility
                $table->text('mode_of_transport_to_the_hospital_others_details')->nullable(); // mode_of_transport_to_the_hospital_others_details
                $table->text('initial_impression')->nullable(); // initial_impression
                $table->text('icd_10_codes_nature_injury')->nullable(); // icd_10_codes_nature_injury
                $table->text('icd_10_codes_external_cause_of_injury')->nullable(); // icd_10_codes_external_cause_of_injury
                $table->text('disposition')->nullable(); // disposition
                $table->text('specify_facility_transferred_to')->nullable(); // specify_facility_transferred_to
                $table->text('outcome')->nullable(); // outcome
                $table->text('initial_admitting_final_diagnosis')->nullable(); // initial_admitting_final_diagnosis
                $table->text('disposition')->nullable(); // disposition
                $table->text('facility_transfered_to')->nullable(); // facility_transfered_to
                $table->text('disposition_others_details')->nullable(); // disposition_others_details
                $table->text('outcome')->nullable(); // outcome
                $table->text('icd_10_codes_nature_of_injury')->nullable(); // icd_10_codes_nature_of_injury
                $table->text('icd_10_external_case_of_injury')->nullable(); // icd_10_external_case_of_injury
                $table->text('name_of_encoder')->nullable(); // name_of_encoder
                $table->text('designation_of_encoder')->nullable(); // designation_of_encoder
                $table->text('contact_number_of_encoder')->nullable(); // contact_number_of_encoder
                $table->text('address_of_dru')->nullable(); // address_of_dru
                $table->text('_id')->nullable(); // _id
                $table->text('uuid')->nullable(); // uuid
                $table->text('submission_time')->nullable(); // submission_time
                $table->text('validation_status')->nullable(); // validation_status
                $table->text('notes')->nullable(); // notes
                $table->text('status')->nullable(); // status
                $table->text('submitted_by')->nullable(); // submitted_by
                $table->text('version')->nullable(); // version
                $table->text('tags')->nullable(); // tags
                $table->text('meta_root_uuid')->nullable(); // meta_root_uuid
                $table->text('index')->nullable(); // index

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
