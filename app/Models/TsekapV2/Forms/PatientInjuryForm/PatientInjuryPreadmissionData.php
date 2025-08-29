<?php

namespace App\Models\TsekapV2\Forms\PatientInjuryForm;

use Illuminate\Database\Eloquent\Model;
use App\Models\TsekapV2\Province;
use App\Models\TsekapV2\Muncity;
use App\Models\TsekapV2\Barangay;

class PatientInjuryPreadmissionData extends Model
{
    protected $connection = 'mysql';
    protected $table = 'patient_injury_form_preadmission_data';
    protected $guarded = array();

    protected $fillable = [
        'id',
        'general_data_id',

        'poi_province_id',
        'poi_municipal_id',
        'poi_barangay_id',
        'poi_purok_sitio',
        'poi_date_of_injury',
        'poi_time_of_injury',
        'date_of_consultation',
        'time_of_consultation',

        'injury_intent_type',
        'first_aid_given_yes_no',
        'first_aid_given_by_whom',
        'first_aid_given_what',

        'noi_multiple_injuries_yes_no',

        'noi_abrasions_yes_no',
        'noi_abrasions_details',
        'noi_abrasions_body_parts',

        'noi_avulsion_yes_no',
        'noi_avulsion_details',
        'noi_avulsion_body_parts',

        'noi_burn_yes_no',
        'noi_burn_details',
        'noi_burn_body_parts',
        'noi_burn_degree',

        'noi_concussion_yes_no',
        'noi_concussion_details',
        'noi_concussion_body_parts',

        'noi_contusion_yes_no',
        'noi_contusion_details',
        'noi_contusion_body_parts',

        'noi_fracture_yes_no',
        'noi_fracture_type',
        'noi_fracture_details',
        'noi_fracture_body_parts',

        'noi_open_wound_yes_no',
        'noi_open_wound_details',
        'noi_open_wound_body_parts',

        'noi_traumatic_amputation_yes_no',
        'noi_traumatic_amputation_details',
        'noi_traumatic_amputation_body_parts',

        'noi_others_yes_no',
        'noi_others_specify',
        'noi_others_details',
        'noi_others_body_parts',

        'eci_bites_stings_yes_no',
        'eci_bites_stings_details',

        'eci_burns_yes_no',
        'eci_burns_details',
        'eci_burns_specify_others',

        'eci_chemical_substance_yes_no',
        'eci_chemical_substance_details',

        'eci_contact_with_sharps_yes_no',
        'eci_contact_with_sharps_details',

        'eci_drowning_yes_no',
        'eci_drowning_details',
        'eci_drowning_specify_others',

        'eci_exposure_to_force_of_nature_yes_no',
        'eci_exposure_to_force_of_nature_details',

        'eci_fall_yes_no',
        'eci_fall_details',

        'eci_firecracker_yes_no',
        'eci_firecracker_details',

        'eci_sa_or_alleged_rape_yes_no',
        'eci_sa_or_alleged_rape_details',

        'eci_gunshot_yes_no',
        'eci_gunshot_details',

        'eci_hanging_strangulation_yes_no',
        'eci_hanging_strangulation_details',

        'eci_mauling_assaults_yes_no',
        'eci_mauling_assaults_details',

        'eci_vehicular_accident_yes_no',
        'eci_vehicular_accident_details',
        'coi_vehicular_accident_location',
        'coi_vehicular_accident_type',
        'coi_patients_vehicle',
        'coi_patients_vehicle_specify_others',

        'coi_other_vehicle_or_object_involved',
        'coi_other_vehicle_or_object_involved_specify_others',

        'coi_act_of_pat_at_time_of_incident',
        'coi_act_of_pat_at_time_of_incident_specify_others',
        'coi_oth_risk_factors_at_the_time_of_the_incident',
        'coi_oth_risk_factors_at_the_time_of_the_incident_specify_others',

        'coi_safety',
        'coi_safety_specify_others',
        'coi_position_of_patient',
        'coi_position_of_patient_specify_others',
        'coi_place_of_occurrence',
        'coi_place_of_occurrence_specify_workplace',
        'coi_place_of_occurrence_specify_others',

        'hfd_er_opd_bhs_rhu_yes_no',
        'hfd_transferred_from_other_facility_yes_no',
        'hfd_referred_by_other_other_facility_yes_no',
        'hfd_originating_facility',
        'hfd_status_on_arrival',
        'hfd_transport_mode',
        'hfd_transport_mode_specify_others',
        'hfd_initial_impression',
        'hfd_icd10_code_nature_of_injury',
        'hfd_icd10_code_external_cause_of_injury',
        'hfd_disposition',
        'hfd_outcome',

        'hfd_in_patient_admitted_yes_no',
        'hfd_in_patient_complete_final_diagnosis',
        'hfd_in_patient_disposition',
        'hfd_in_patient_outcome',
        'hfd_in_patient_icd10_code_nature_of_injury',
        'hfd_in_patient_icd10_code_external_cause_of_injury',

        'created_at',
        'updated_at'
    ];

    public function province()
    {
        return $this->belongsTo(Province::class, 'poi_province_id');
    }

    public function muncity()
    {
        return $this->belongsTo(Muncity::class, 'poi_municipal_id');
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'poi_barangay_id');
    }

    public function generalData()
    {
        return $this->belongsTo(PatientInjuryGeneralData::class, 'general_data_id', 'id');
    }
}
