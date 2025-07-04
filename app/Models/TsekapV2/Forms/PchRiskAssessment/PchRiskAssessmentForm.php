<?php

namespace App\Models\TsekapV2\Forms\PchRiskAssessment;

use Illuminate\Database\Eloquent\Model;
use App\Models\TsekapV2\Forms\PchRiskAssessment\PchRiskProfile;

class PchRiskAssessmentForm extends Model
{
    protected $connection = 'mysql';
    protected $table = 'pch_risk_assessment_tool_form';
    protected $guarded = array();
    protected $fillable = [
        'id',
        'pch_profile_id',
        'nature_of_visit',
        'nature_of_visit_duration',
        'type_of_consultation',

        'vit_bp_systolic',
        'vit_bp_diastolic',
        'vit_oxygen_saturation',
        'vit_heart_rate_or_pulse_rate',
        'vit_is_normal_rate',
        'vit_is_regular_rhythm',
        'vit_respiratory_rate',
        'vit_temperature',
        'vit_weight',
        'vit_height',
        'vit_bmi',
        'vit_chief_complaint',
        'vit_history_of_present_illness_and_remarks',

        'pe_skin_extremities_description',
        'pe_heent_description',
        'pe_chest_description',
        'pe_heart',
        'pe_abdomen',
        'pe_alert_type',
        'pe_description',

        'ab_anatomical_location',
        'ab_anatomical_location_others_specify',
        'ab_animal_type',
        'ab_animal_type_others_specify',
        'ab_description_of_event',
        'ab_type_of_exposure',
        'ab_wash_bite',
        'ab_date_of_exposure',

        'comorbidities',
        'comorbidities_others',

        'ph_immunization_record_child',
        'ph_immunization_record_child_others_specify',
        'ph_immunization_record_pregnant',
        'ph_immunization_record_pregnant_others_specify',
        'ph_immunization_record_adult_and_elderly',
        'ph_immunization_record_adult_and_elderly_others_specify',

        'wr_menarche',
        'wr_menarche_age',
        'wr_menopause',
        'wr_menopause_age',
        'wr_no_of_pads_used_per_day',
        'wr_interval_cycle_of_menstruation_in_days',
        'wr_birth_control_method_used',
        'wr_onset_of_sexual_intercourse_age',
        'wr_pregnancy_history_gravidity',
        'wr_pregnancy_history_parity',
        'wr_pre_eclampsia',
        'wr_with_access_to_family_planning_counseling',
        'wr_pregnancy_history_num_of_full_term_pregnancy',
        'wr_pregnancy_history_num_of_premature_pregnancy',
        'wr_number_of_abortion',
        'wr_number_of_living_children',

        'comorbidities',
        'comorbidities_others',

        'fmh_first_degree_relatives_with',
        'fmh_first_degree_relatives_with_specify_others',

        'sh_smoking',
        'sh_use_of_vape',
        'sh_use_of_vape_age_started',

        'soch_illicit_drug_use',
        'soch_illicit_drug_use_specify_illicit_drug_used',
        'soch_sexual_activity_is_sexually_active',
        'soch_sexual_activity_number_of_partners',
        'soch_sexual_activity_with_protection',
        'soch_sexual_activity_testing_done',

        'excessive_alcohol_intake',
        'dietary_fiber_intake_3_servings_of_vegetable_daily',
        'dietary_fiber_intake_2_to_3_servings_of_fruits_daily ',
        'high_fat_or_high_salt_food_intake',
        'physical_activity',

        'pahas_or_tia_q1',
        'pahas_or_tia_q2',
        'pahas_or_tia_q3',
        'pahas_or_tia_q4',
        'pahas_or_tia_q5',
        'pahas_or_tia_q6',
        'pahas_or_tia_q7',
        'pahas_or_tia_q8',

        'presence_or_absence_of_diabetes',
        'symptoms_polyphagia',
        'symptoms_polydipsia',
        'symptoms_polyuria',
        'symptoms_presence_of_urine_ketones_newly_diagnosed',
        'raised_blood_glucose',
        'urine_ketones',
        'fbs_rbs',
        'fbs_rbs_date_taken',
        'urine_ketones_date_taken',
        'raised_blood_lipid',
        'management',
        'total_cholesterol',
        'total_cholesterol_date_taken',
        'lifestyle_modification',
        'presence_of_urine_protein',
        'medications',
        'urine_protein',
        'urine_protein_date_taken',
        'date_follow_up',

        'do_laboratory_request',
        'do_imaging',
        'do_imaging_with_contrast',
        'do_diagnosis',
        'do_treatment_plan',
        'do_follow_up_date',
        'do_prescription',
        'do_remarks',

        'created_at',
        'updated_at',
    ];

    public function pch_profile_Id()
    {
        return $this->belongsTo(PchRiskProfile::class, "id");
    }
}
