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
            Schema::create('pch_risk_assessment_tool_form', function (Blueprint $table) {
                $table->increments('id'); // id, [PRIMARY_KEY]
                $table->unsignedInteger('pch_profile_id')->index(); // pch_profile_id, [FOREIGN_KEY]
                $table->string('nature_of_visit'); // nature_of_visit
                $table->string('nature_of_visit_duration', 50); // nature_of_visit_duration
                $table->string('type_of_consultation'); // type_of_consultation

                // vital signs fields
                $table->string('vit_bp_1st_reading', 10); // vit_bp_1st_reading
                $table->string('vit_bp_2nd_reading', 10); // vit_bp_2nd_reading
                $table->string('vit_bp_3rd_reading', 10); // vit_bp_3rd_reading
                $table->string('vit_bp_average_2nd_to_3rd_reading', 10); // vit_bp_average_2nd_to_3rd_reading

                $table->string('vit_oxygen_saturation'); // vit_oxygen_saturation
                $table->string('vit_heart_rate_or_pulse_rate'); // vit_heart_rate_or_pulse_rate
                $table->boolean('vit_is_normal_rate'); // vit_is_normal_rate
                $table->boolean('vit_is_regular_rhythm'); // vit_is_regular_rhythm
                $table->double('vit_respiratory_rate'); // vit_respiratory_rate
                $table->double('vit_temperature'); // vit_temperature
                $table->double('vit_weight'); // vit_weight
                $table->double('vit_height'); // vit_height
                $table->double('vit_waist_circumference'); // vit_waist_circumference 
                $table->double('vit_bmi'); // vit_bmi
                $table->text('vit_chief_complaint'); // vit_chief_complaint
                $table->text('vit_history_of_present_illness_and_remarks')->nullable(); // vit_history_of_present_illness_and_remarks

                // comorbidities fields
                $table->boolean('comorb_hist_heart_attack_stroke_or_kidney_problems')->nullable(); // comorb_hist_heart_attack_stroke_or_kidney_problems
                $table->boolean('comorb_hist_heart_attack_stroke_1st_degree_relatives')->nullable(); // comorb_hist_heart_attack_stroke_1st_degree_relatives
                $table->boolean('comorb_hypertension')->nullable(); // comorb_hypertension
                $table->boolean('comorb_hypertension_if_yes_taking_medications')->nullable(); // comorb_hypertension_if_yes_taking_medications
                $table->boolean('comorb_diabetes_mellitus')->nullable(); // comorb_diabetes_mellitus
                $table->boolean('comorb_diabetes_mellitus_if_yes_taking_medications')->nullable(); // comorb_diabetes_mellitus_if_yes_taking_medications
                $table->boolean('comorb_high_cholesterol')->nullable(); // comorb_high_cholesterol
                $table->boolean('comorb_high_cholesterol_if_yes_taking_medications')->nullable(); // comorb_high_cholesterol_if_yes_taking_medications
                $table->boolean('comorb_tuberculosis')->nullable(); // comorb_tuberculosis

                // lifestyle fields
                $table->boolean('lifestyle_current_smoker')->nullable(); // lifestyle_current_smoker
                $table->boolean('lifestyle_current_smoker_if_yes_tobacco_products')->nullable(); // lifestyle_current_smoker_if_yes_tobacco_products
                $table->boolean('lifestyle_current_smoker_if_yes_vaporized_products')->nullable(); // lifestyle_current_smoker_if_yes_vaporized_products
                $table->boolean('lifestyle_current_smoker_if_yes_both')->nullable(); // lifestyle_current_smoker_if_yes_both
                $table->boolean('lifestyle_binge_drinking_past_year')->nullable(); // lifestyle_binge_drinking_past_year
                $table->boolean('lifestyle_moderate_physical_activity_throughout_the_week')->nullable(); // lifestyle_moderate_physical_activity_throughout_the_week
                $table->boolean('lifestyle_intake_of_fruits_and_veg_below_five_portions')->nullable(); // lifestyle_intake_of_fruits_and_veg_below_five_portions
                $table->string('lifestyle_non_laboratory_cvd_risk_percentage_color_code', 10)->nullable(); // lifestyle_non_laboratory_cvd_risk_percentage_color_code

                // management fields
                $table->boolean('mngt_counseling_on_healthy_diet')->nullable(); // mngt_counseling_on_healthy_diet
                $table->boolean('mngt_counseling_on_physical_activity')->nullable(); // mngt_counseling_on_physical_activity
                $table->boolean('mngt_counseling_on_referred_for_bti')->nullable(); // mngt_counseling_on_referred_for_bti
                $table->boolean('mngt_harmful_use_of_alcohol')->nullable(); // mngt_harmful_use_of_alcohol
                $table->boolean('mngt_referred_to_pcf_for_risk_screening')->nullable(); // mngt_referred_to_pcf_for_risk_screening

                // management and lifestyle metadata
                $table->date('date_next_risk_assessment')->nullable(); // date_next_risk_assessment
                $table->string('assessed_by', 100)->nullable(); // assessed_by
                $table->string('verified_by', 100)->nullable(); // verified_by

                // immunization record fields
                $table->boolean('imm_record_child_bcg')->nullable(); // imm_record_child_bcg
                $table->boolean('imm_record_child_opv')->nullable(); // imm_record_child_opv
                $table->boolean('imm_record_child_polio_1')->nullable(); // imm_record_child_polio_1
                $table->boolean('imm_record_child_polio_2')->nullable(); // imm_record_child_polio_2
                $table->boolean('imm_record_child_polio_3')->nullable(); // imm_record_child_polio_3
                $table->boolean('imm_record_child_hep_b1')->nullable(); // imm_record_child_hep_b1
                $table->boolean('imm_record_child_hep_b2')->nullable(); // imm_record_child_hep_b2
                $table->boolean('imm_record_child_hep_b3')->nullable(); // imm_record_child_hep_b3
                $table->boolean('imm_record_child_dpt1')->nullable(); // imm_record_child_dpt1
                $table->boolean('imm_record_child_dpt2')->nullable(); // imm_record_child_dpt2
                $table->boolean('imm_record_child_dpt3')->nullable(); // imm_record_child_dpt3
                $table->boolean('imm_record_child_hib1')->nullable(); // imm_record_child_hib1
                $table->boolean('imm_record_child_hib2')->nullable(); // imm_record_child_hib2
                $table->boolean('imm_record_child_hib3')->nullable(); // imm_record_child_hib3
                $table->boolean('imm_record_child_covid19')->nullable(); // imm_record_child_covid19

                $table->boolean('imm_record_child_measles_mcv1_mr')->nullable(); // imm_record_child_measles_mcv1_mr
                $table->boolean('imm_record_child_measles_mcv1_mmr')->nullable(); // imm_record_child_measles_mcv1_mmr
                $table->boolean('imm_record_child_measles_mcv2_mr')->nullable(); // imm_record_child_measles_mcv2_mr
                $table->boolean('imm_record_child_measles_mcv2_mmr')->nullable(); // imm_record_child_measles_mcv2_mmr
                $table->boolean('imm_record_child_booster')->nullable(); // imm_record_child_booster
                $table->boolean('imm_record_child_others')->nullable(); // imm_record_child_others
                $table->text('imm_record_child_others_please_specify')->nullable(); // imm_record_child_others_please_specify
                $table->boolean('imm_record_schoolage_mr')->nullable(); // imm_record_schoolage_mr
                $table->boolean('imm_record_schoolage_td')->nullable(); // imm_record_schoolage_td
                $table->boolean('imm_record_schoolage_hpv')->nullable(); // imm_record_schoolage_hpv
                $table->boolean('imm_record_schoolage_others')->nullable(); // imm_record_schoolage_others
                $table->text('imm_record_schoolage_others_please_specify')->nullable(); // imm_record_schoolage_others_please_specify
                $table->boolean('imm_record_pregnant_none')->nullable(); // imm_record_pregnant_none
                $table->boolean('imm_record_pregnant_tetanus_toxoid')->nullable(); // imm_record_pregnant_tetanus_toxoid
                $table->boolean('imm_record_pregnant_covid19')->nullable(); // imm_record_pregnant_covid19
                $table->boolean('imm_record_pregnant_flu')->nullable(); // imm_record_pregnant_flu
                $table->boolean('imm_record_pregnant_others')->nullable(); // imm_record_pregnant_others
                $table->text('imm_record_pregnant_others_please_specify')->nullable(); // imm_record_pregnant_others_please_specify
                $table->boolean('imm_record_adult_elderly_none')->nullable(); // imm_record_adult_elderly_none
                $table->boolean('imm_record_adult_elderly_flu')->nullable(); // imm_record_adult_elderly_flu
                $table->boolean('imm_record_adult_elderly_pneumococcal')->nullable(); // imm_record_adult_elderly_pneumococcal
                $table->boolean('imm_record_adult_elderly_covid19')->nullable(); // imm_record_adult_elderly_covid19
                $table->boolean('imm_record_adult_elderly_hpv')->nullable(); // imm_record_adult_elderly_hpv
                $table->boolean('imm_record_adult_elderly_others')->nullable(); // imm_record_adult_elderly_others
                $table->text('imm_record_adult_elderly_others_specify')->nullable(); // imm_record_adult_elderly_others_specify

                // menstrual history fields
                $table->boolean('menst_hist_menarche')->nullable(); // menst_hist_menarche
                $table->double('menst_hist_age_menarche')->nullable(); // menst_hist_age_menarche
                $table->boolean('menst_hist_menopause')->nullable(); // menst_hist_menopause
                $table->double('menst_hist_age_menopause')->nullable(); // menst_hist_age_menopause
                $table->unsignedInteger('menst_hist_no_of_pads_used_per_day')->nullable(); // menst_hist_no_of_pads_used_per_day
                $table->double('menst_hist_interval_cycle_of_menstruation_in_days')->nullable(); // menst_hist_interval_cycle_of_menstruation_in_days
                $table->text('menst_hist_birth_control_method_used')->nullable(); // menst_hist_birth_control_method_used
                $table->double('menst_hist_onset_of_sexual_intercourse_age')->nullable(); // menst_hist_onset_of_sexual_intercourse_age

                // pregnancy history fields
                $table->unsignedInteger('preg_hist_gravidity')->nullable(); // preg_hist_gravidity
                $table->unsignedInteger('preg_hist_parity')->nullable(); // preg_hist_parity
                $table->unsignedInteger('preg_hist_no_of_full_term_pregnancy')->nullable(); // preg_hist_no_of_full_term_pregnancy
                $table->unsignedInteger('preg_hist_no_of_premature_pregnancy')->nullable(); // preg_hist_no_of_premature_pregnancy
                $table->unsignedInteger('preg_hist_no_of_abortion')->nullable(); // preg_hist_no_of_abortion
                $table->unsignedInteger('preg_hist_no_of_living_children')->nullable(); // preg_hist_no_of_living_children
                $table->boolean('preg_hist_pre_eclampsia')->nullable(); // preg_hist_pre_eclampsia
                $table->boolean('preg_hist_with_access_to_family_planning')->nullable(); // preg_hist_with_access_to_family_planning

                // family history fields
                $table->boolean('fam_hist_asthma')->nullable(); // fam_hist_asthma
                $table->boolean('fam_hist_copd')->nullable(); // fam_hist_copd
                $table->boolean('fam_hist_hypertension')->nullable(); // fam_hist_hypertension
                $table->boolean('fam_hist_tuberculosis')->nullable(); // fam_hist_tuberculosis
                $table->boolean('fam_hist_cancer')->nullable(); // fam_hist_cancer
                $table->boolean('fam_hist_diabetes')->nullable(); // fam_hist_diabetes
                $table->boolean('fam_hist_kidney_disease')->nullable(); // fam_hist_kidney_disease
                $table->boolean('fam_hist_peripheral_vascular_diseases')->nullable(); // fam_hist_peripheral_vascular_diseases
                $table->boolean('fam_hist_mental_disorders')->nullable(); // fam_hist_mental_disorders
                $table->boolean('fam_hist_others')->nullable(); // fam_hist_others
                $table->text('fam_hist_others_please_specify')->nullable(); // fam_hist_others_please_specify

                // social history fields
                $table->boolean('soc_hist_is_patient_illicit_drug_user')->nullable(); // soc_hist_is_patient_illicit_drug_user
                $table->string('soc_hist_if_yes_indicate_type_of_illegal_drug_used', 255)->nullable(); // soc_hist_if_yes_indicate_type_of_illegal_drug_used
                $table->boolean('soc_hist_is_patient_sexually_active')->nullable(); // soc_hist_is_patient_sexually_active
                $table->string('soc_hist_sexual_activity_no_of_partner', 10)->nullable(); // soc_hist_sexual_activity_no_of_partner
                $table->boolean('soc_hist_sexual_activity_with_protection')->nullable(); // soc_hist_sexual_activity_with_protection

                // risk assessment pt. II
                $table->boolean('risk_assessment_established_angina_pectoris')->nullable(); // risk_assessment_established_angina_pectoris
                $table->boolean('risk_assessment_with_left_ventricular_hypertrophy')->nullable(); // risk_assessment_with_left_ventricular_hypertrophy
                $table->boolean('risk_assessment_wo_est_cvd_with_8mmol_of_cholesterol')->nullable(); // risk_assessment_wo_est_cvd_with_8mmol_of_cholesterol
                $table->boolean('risk_assessment_wo_est_cvd_who_have_persistent_raised_bp')->nullable(); // risk_assessment_wo_est_cvd_who_have_persistent_raised_bp
                $table->boolean('risk_assessment_with_type1_or_type2_diabetes')->nullable(); // risk_assessment_with_type1_or_type2_diabetes
                $table->boolean('risk_assessment_with_known_renal_failure_or_impairment')->nullable(); // risk_assessment_with_known_renal_failure_or_impairment

                $table->string('bp_2nd_encounter_1st_reading', 10)->nullable(); // bp_2nd_encounter_1st_reading
                $table->string('bp_2nd_encounter_2nd_reading', 10)->nullable(); // bp_2nd_encounter_2nd_reading
                $table->string('bp_2nd_encounter_3rd_reading', 10)->nullable(); // bp_2nd_encounter_3rd_reading
                $table->string('bp_2nd_encounter_average_2nd_to_3rd_reading', 10)->nullable(); // bp_2nd_encounter_average_2nd_to_3rd_reading
                $table->boolean('individual_have_all_classic_symptoms_marked')->nullable(); // individual_have_all_classic_symptoms_marked
                $table->double('urine_ketones_result')->nullable(); // urine_ketones_result
                $table->date('urine_ketones_result_date_taken')->nullable(); // urine_ketones_result_date_taken
                $table->double('total_cholesterol_result')->nullable(); // total_cholesterol_result
                $table->date('total_cholesterol_result_date_taken')->nullable(); // total_cholesterol_result_date_taken
                $table->double('random_plasma_glucose_result')->nullable(); // random_plasma_glucose_result
                $table->date('random_plasma_glucose_result_date_taken')->nullable(); // random_plasma_glucose_result_date_taken
                $table->double('fasting_plasma_glucose_result')->nullable(); // fasting_plasma_glucose_result
                $table->date('fasting_plasma_glucose_result_date_taken')->nullable(); // fasting_plasma_glucose_result_date_taken
                $table->double('confirmatory_fpg_result')->nullable(); // confirmatory_fpg_result
                $table->date('confirmatory_fpg_result_date_taken')->nullable(); // confirmatory_fpg_result_date_taken
                $table->boolean('basic_labs_for_confirmed_hypertensives_12l_ecg')->nullable(); // basic_labs_for_confirmed_hypertensives_12l_ecg
                $table->boolean('basic_labs_for_confirmed_hypertensives_blood_test')->nullable(); // basic_labs_for_confirmed_hypertensives_blood_test
                $table->boolean('basic_labs_for_confirmed_hypertensives_dipstick')->nullable(); // basic_labs_for_confirmed_hypertensives_dipstick

                $table->string('laboratory_cvd_risk_percentage_color_code', 10)->nullable(); // laboratory_cvd_risk_percentage_color_code
                $table->boolean('mngt_2_counseling_on_healthy_diet')->nullable(); // mngt_2_counseling_on_healthy_diet
                $table->boolean('mngt_2_counseling_on_physical_activity')->nullable(); // mngt_2_counseling_on_physical_activity
                $table->boolean('mngt_2_counseling_on_tobacco_cessation')->nullable(); // mngt_2_counseling_on_tobacco_cessation
                $table->boolean('mngt_2_counseling_on_harmful_use_of_alcohol')->nullable(); // mngt_2_counseling_on_harmful_use_of_alcohol
                $table->boolean('medications_anti_hypertension')->nullable(); // medications_anti_hypertension
                $table->boolean('medications_yes_anti_hypertension_out_of_pkt')->nullable(); // medications_yes_anti_hypertension_out_of_pkt
                $table->boolean('medications_yes_anti_hypertension_both_pbf_and_oop')->nullable(); // medications_yes_anti_hypertension_both_pbf_and_oop
                $table->boolean('medications_yes_anti_hypertension_provided')->nullable(); // medications_yes_anti_hypertension_provided
                $table->boolean('medications_oral_hypoglycemic_agents_or_insulin')->nullable(); // medications_oral_hypoglycemic_agents_or_insulin
                $table->boolean('medications_yes_oral_hypoglycemic_agents_or_insulin_provided')->nullable(); // medications_yes_oral_hypoglycemic_agents_or_insulin_provided
                $table->boolean('medications_yes_oral_hypoglycemic_agents_or_insulin_out_of_pkt')->nullable(); // medications_yes_oral_hypoglycemic_agents_or_insulin_out_of_pkt
                $table->boolean('medications_oral_hypoglycemic_agents_or_insulin_both_pbf_and_oop')->nullable(); // medications_oral_hypoglycemic_agents_or_insulin_both_pbf_and_oop
                $table->date('risk_assessment_ii_date_of_follow_up')->nullable(); // risk_assessment_ii_date_of_follow_up
                $table->string('physicians_name_risk_assessment_pt_ii', 255)->nullable(); // physicians_name_risk_assessment_pt_ii

                // physical exam fields
                $table->boolean('pe_skin_extremities_essentially_normal')->nullable(); // pe_skin_extremities_essentially_normal
                $table->boolean('pe_skin_extremities_clubbing')->nullable(); // pe_skin_extremities_clubbing
                $table->boolean('pe_skin_extremities_cold_clammy')->nullable(); // pe_skin_extremities_cold_clammy
                $table->boolean('pe_skin_extremities_pale_nailbeds')->nullable(); // pe_skin_extremities_pale_nailbeds
                $table->boolean('pe_skin_extremities_cyanosis')->nullable(); // pe_skin_extremities_cyanosis
                $table->boolean('pe_skin_extremities_poor_skin_turgor')->nullable(); // pe_skin_extremities_poor_skin_turgor
                $table->boolean('pe_skin_extremities_edema')->nullable(); // pe_skin_extremities_edema
                $table->boolean('pe_skin_extremities_itching')->nullable(); // pe_skin_extremities_itching
                $table->boolean('pe_skin_extremities_enythema')->nullable(); // pe_skin_extremities_enythema
                $table->boolean('pe_skin_extremities_lesions')->nullable(); // pe_skin_extremities_lesions
                $table->boolean('pe_heent_essentially_normal')->nullable(); // pe_heent_essentially_normal
                $table->boolean('pe_heent_abnormal_pupillary_reaction')->nullable(); // pe_heent_abnormal_pupillary_reaction
                $table->boolean('pe_heent_cervical_lymphadenopathy')->nullable(); // pe_heent_cervical_lymphadenopathy
                $table->boolean('pe_heent_icteric_sclera')->nullable(); // pe_heent_icteric_sclera
                $table->boolean('pe_heent_pale_conjunctivae')->nullable(); // pe_heent_pale_conjunctivae
                $table->boolean('pe_heent_sunken_eyeballs')->nullable(); // pe_heent_sunken_eyeballs
                $table->boolean('pe_chest_essentially_normal')->nullable(); // pe_chest_essentially_normal
                $table->boolean('pe_chest_asymmetric_chest_expansion')->nullable(); // pe_chest_asymmetric_chest_expansion
                $table->boolean('pe_chest_wheezes')->nullable(); // pe_chest_wheezes
                $table->boolean('pe_chest_crackles')->nullable(); // pe_chest_crackles
                $table->boolean('pe_chest_enlarge_axillary_lymph_nodes')->nullable(); // pe_chest_enlarge_axillary_lymph_nodes
                $table->boolean('pe_chest_decreased_breath_sounds')->nullable(); // pe_chest_decreased_breath_sounds
                $table->boolean('pe_chest_lumps_over_breast')->nullable(); // pe_chest_lumps_over_breast
                $table->boolean('pe_heart_essentially_normal')->nullable(); // pe_heart_essentially_normal
                $table->boolean('pe_heart_displaced_apex_beat')->nullable(); // pe_heart_displaced_apex_beat
                $table->boolean('pe_heart_heart_murmur')->nullable(); // pe_heart_heart_murmur
                $table->boolean('pe_heart_irregular_rhythm')->nullable(); // pe_heart_irregular_rhythm
                $table->boolean('pe_heart_heaves')->nullable(); // pe_heart_heaves
                $table->boolean('pe_abdomen_essentially_normal')->nullable(); // pe_abdomen_essentially_normal
                $table->boolean('pe_abdomen_abdominal_rigidity')->nullable(); // pe_abdomen_abdominal_rigidity
                $table->boolean('pe_abdomen_palpable_mass')->nullable(); // pe_abdomen_palpable_mass
                $table->boolean('pe_abdomen_tenderness')->nullable(); // pe_abdomen_tenderness
                $table->boolean('pe_abdomen_hyperactive_bowel_sounds')->nullable(); // pe_abdomen_hyperactive_bowel_sounds
                $table->boolean('pe_alert_type_allergy')->nullable(); // pe_alert_type_allergy
                $table->boolean('pe_alert_type_disability')->nullable(); // pe_alert_type_disability
                $table->boolean('pe_alert_type_drug')->nullable(); // pe_alert_type_drug
                $table->boolean('pe_alert_type_handicap')->nullable(); // pe_alert_type_handicap
                $table->boolean('pe_alert_type_impairment')->nullable(); // pe_alert_type_impairment
                $table->boolean('pe_alert_type_others')->nullable(); // pe_alert_type_others
                $table->text('pe_alert_type_description')->nullable(); // pe_alert_type_description

                // animal bite fields
                $table->boolean('animal_bite_loc_abdomen')->nullable(); // animal_bite_loc_abdomen
                $table->boolean('animal_bite_loc_chest')->nullable(); // animal_bite_loc_chest
                $table->boolean('animal_bite_loc_forearm')->nullable(); // animal_bite_loc_forearm
                $table->boolean('animal_bite_loc_back')->nullable(); // animal_bite_loc_back
                $table->boolean('animal_bite_loc_eye')->nullable(); // animal_bite_loc_eye
                $table->boolean('animal_bite_loc_hand')->nullable(); // animal_bite_loc_hand
                $table->boolean('animal_bite_loc_buttocks')->nullable(); // animal_bite_loc_buttocks
                $table->boolean('animal_bite_loc_foot')->nullable(); // animal_bite_loc_foot
                $table->boolean('animal_bite_loc_head')->nullable(); // animal_bite_loc_head
                $table->boolean('animal_bite_loc_neck')->nullable(); // animal_bite_loc_neck
                $table->boolean('animal_bite_loc_thigh')->nullable(); // animal_bite_loc_thigh
                $table->boolean('animal_bite_loc_pelvic')->nullable(); // animal_bite_loc_pelvic
                $table->boolean('animal_bite_loc_knee')->nullable(); // animal_bite_loc_knee
                $table->boolean('animal_bite_loc_legs')->nullable(); // animal_bite_loc_legs
                $table->boolean('animal_bite_loc_mouth')->nullable(); // animal_bite_loc_mouth
                $table->boolean('animal_bite_loc_nose')->nullable(); // animal_bite_loc_nose
                $table->boolean('animal_bite_loc_ears')->nullable(); // animal_bite_loc_ears
                $table->boolean('animal_bite_loc_others')->nullable(); // animal_bite_loc_others
                $table->text('animal_bite_loc_others_specify')->nullable(); // animal_bite_loc_others_specify
                $table->boolean('animal_type_dog')->nullable(); // animal_type_dog
                $table->boolean('animal_type_pig')->nullable(); // animal_type_pig
                $table->boolean('animal_type_rat')->nullable(); // animal_type_rat
                $table->boolean('animal_type_snake')->nullable(); // animal_type_snake
                $table->boolean('animal_type_cat')->nullable(); // animal_type_cat
                $table->boolean('animal_type_others')->nullable(); // animal_type_others
                $table->text('animal_type_others_specify')->nullable(); // animal_type_others_specify
                $table->text('animal_bite_description_of_bite_event')->nullable(); // animal_bite_description_of_bite_event
                $table->boolean('animal_bite_type_of_exposure_transdermal_bite')->nullable(); // animal_bite_type_of_exposure_transdermal_bite
                $table->boolean('animal_bite_type_of_exposure_punctured_wounds')->nullable(); // animal_bite_type_of_exposure_punctured_wounds
                $table->boolean('animal_bite_type_of_exposure_lacerations')->nullable(); // animal_bite_type_of_exposure_lacerations
                $table->boolean('animal_bite_type_of_exposure_avulsions')->nullable(); // animal_bite_type_of_exposure_avulsions
                $table->boolean('animal_bite_type_of_exposure_scratches_abrasions_w_sponti_bleed')->nullable(); // animal_bite_type_of_exposure_scratches_abrasions_w_sponti_bleed
                $table->boolean('animal_bite_wash_bite')->nullable(); // animal_bite_wash_bite
                $table->date('animal_bite_date_of_exposure')->nullable(); // animal_bite_date_of_exposure
                $table->string('animal_bite_name_of_accompanying_adult', 100)->nullable(); // animal_bite_name_of_accompanying_adult
                $table->string('animal_bite_contact_number', 25)->nullable(); // animal_bite_contact_number
                $table->string('animal_bite_relationship_to_patient', 50)->nullable(); // animal_bite_relationship_to_patient

                // geriatric assessment fields
                $table->boolean('geriatric_memory_1')->nullable(); // geriatric_memory_1
                $table->boolean('geriatric_depression')->nullable(); // geriatric_depression
                $table->string('geriatric_depression_refer_to_physician_specify', 100)->nullable(); // geriatric_depression_refer_to_physician_specify
                $table->boolean('geriatric_medication')->nullable(); // geriatric_medication
                $table->string('geriatric_medication_refer_to_physician_specify', 100)->nullable(); // geriatric_medication_refer_to_physician_specify
                $table->boolean('geriatric_urinary_incontinence')->nullable(); // geriatric_urinary_incontinence
                $table->string('geriatric_urinary_incontinence_symptoms_specify', 100)->nullable(); // geriatric_urinary_incontinence_symptoms_specify
                $table->string('geriatric_urinary_incontinence_counsel_specify', 100)->nullable(); // geriatric_urinary_incontinence_counsel_specify
                $table->string('geriatric_urinary_incontinence_refer_to_physician_specify', 100)->nullable(); // geriatric_urinary_incontinence_refer_to_physician_specify
                $table->boolean('geriatric_physical_function_capacity')->nullable(); // geriatric_physical_function_capacity
                $table->string('geriatric_physical_function_capacity_refer_to_physician_specify', 100)->nullable(); // geriatric_physical_function_capacity_refer_to_physician
                $table->boolean('geriatric_memory_2')->nullable(); // geriatric_memory_2
                $table->string('geriatric_memory_2_refer_to_physician_specify', 100)->nullable(); // geriatric_memory_2_refer_to_physician_specify
                $table->boolean('geriatric_fall')->nullable(); // geriatric_fall
                $table->string('geriatric_fall_refer_to_physician_specify', 100)->nullable(); // geriatric_fall_refer_to_physician_specify
                $table->unsignedInteger('geriatric_risk_for_falls_seconds')->nullable(); // geriatric_risk_for_falls_seconds
                $table->text('geriatric_risk_for_falls_seconds_indication')->nullable(); // geriatric_risk_for_falls_seconds_indication
                $table->unsignedInteger('geriatric_risk_for_falls_inches')->nullable(); // geriatric_risk_for_falls_inches
                $table->text('geriatric_risk_for_falls_inches_indication')->nullable(); // geriatric_risk_for_falls_inches_indication
                $table->unsignedInteger('geriatric_nutrition_cm')->nullable(); // geriatric_nutrition_cm
                $table->text('geriatric_nutrition_cm_indication')->nullable(); // geriatric_nutrition_cm_indication
                $table->text('geriatric_hearing_test_r_ear_indication')->nullable(); // geriatric_hearing_test_r_ear_indication
                $table->text('geriatric_hearing_test_l_ear_indication')->nullable(); // geriatric_hearing_test_l_ear_indication
                $table->string('geriatric_hearing_test_refer_to_physician_specify', 100)->nullable(); // geriatric_hearing_test_refer_to_physician_specify
                $table->text('geriatric_vision_test_unaided_r_eye')->nullable(); // geriatric_vision_test_unaided_r_eye
                $table->text('geriatric_vision_test_unaided_l_eye')->nullable(); // geriatric_vision_test_unaided_l_eye
                $table->text('geriatric_vision_test_aided_r_eye')->nullable(); // geriatric_vision_test_aided_r_eye
                $table->text('geriatric_vision_test_aided_l_eye')->nullable(); // geriatric_vision_test_aided_l_eye
                $table->string('geriatric_vision_refer_to_opthalmologist_specify', 100)->nullable(); // geriatric_vision_refer_to_opthalmologist_specify
                $table->text('geriatric_summary_of_findings_counsel')->nullable(); // geriatric_summary_of_findings_counsel
                $table->date('geriatric_summary_of_findings_date_of_return_visit')->nullable(); // geriatric_summary_of_findings_date_of_return_visit
                $table->text('geriatric_summary_of_findings_refer_to_a_physician')->nullable(); // geriatric_summary_of_findings_refer_to_a_physician
                $table->date('geriatric_summary_of_findings_date_of_referral')->nullable(); // geriatric_summary_of_findings_date_of_referral
                $table->text('geriatric_summary_of_findings_reason_for_referral')->nullable(); // geriatric_summary_of_findings_reason_for_referral
                $table->text('geriatric_name_and_designation_of_the_provider')->nullable(); // geriatric_name_and_designation_of_the_provider
                $table->text('geriatric_name_and_address_of_the_facility')->nullable(); // geriatric_name_and_address_of_the_facility
                $table->text('geriatric_facility_contact_details')->nullable(); // geriatric_facility_contact_details
                $table->text('geriatric_signature_of_the_provider')->nullable(); // geriatric_signature_of_the_provider
                $table->text('geriatric_referred_to')->nullable(); // geriatric_referred_to

                // laboratory fields 
                $table->boolean('lab_req_blood_chemistry')->nullable(); // lab_req_blood_chemistry
                $table->boolean('lab_req_fecalysis')->nullable(); // lab_req_fecalysis
                $table->boolean('lab_req_mtb_genexpert')->nullable(); // lab_req_mtb_genexpert
                $table->boolean('lab_req_urinalysis')->nullable(); // lab_req_urinalysis
                $table->boolean('lab_req_clinical_chemistry')->nullable(); // lab_req_clinical_chemistry
                $table->boolean('lab_req_hematology')->nullable(); // lab_req_hematology
                $table->boolean('lab_req_serology')->nullable(); // lab_req_serology
                $table->boolean('lab_req_complete_blood_count')->nullable(); // lab_req_complete_blood_count
                $table->boolean('lab_req_immunology')->nullable(); // lab_req_immunology
                $table->boolean('lab_req_sputum_microscopy')->nullable(); // lab_req_sputum_microscopy

                // imaging fields
                $table->boolean('imaging_ecg')->nullable(); // imaging_ecg 
                $table->boolean('imaging_xray')->nullable(); // imaging_xray
                $table->boolean('imaging_mri')->nullable(); // imaging_mri
                $table->boolean('imaging_ct_scan')->nullable(); // imaging_ct_scan
                $table->boolean('imaging_ultrasound')->nullable(); // imaging_ultrasound
                $table->boolean('imaging_2d_echo')->nullable(); // imaging_2d_echo
                $table->boolean('imaging_via')->nullable(); // imaging_via
                $table->boolean('imaging_pap_smear')->nullable(); // imaging_pap_smear
                $table->boolean('imaging_mammogram')->nullable(); // imaging_mammogram
                $table->boolean('imaging_with_contrast_yes')->nullable(); // imaging_with_contrast_yes
                $table->boolean('imaging_with_contrast_no')->nullable(); // imaging_with_contrast_no
                $table->text('diagnosis')->nullable(); // diagnosis
                $table->text('treatment_plan')->nullable(); // treatment_plan
                $table->date('follow_up_date')->nullable(); // follow_up_date
                $table->text('prescription')->nullable(); // prescription
                $table->boolean('refer_to_higher_facility')->nullable(); // refer_to_higher_facility
                $table->text('remarks')->nullable(); // remarks

                // system metadata
                $table->timestamps();

                // foreign key constraints
                $table->foreign('pch_profile_id')->references('id')->on('pch_risk_assessment_tool_profile')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            Log::error('Migration failed (create pch_risk_assessment_tool_form): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('pch_risk_assessment_tool_form');
        } catch (\Exception $e) {
            Log::error('Migration failed (drop pch_risk_assessment_tool_form): ' . $e->getMessage());
            throw $e;
        }
    }
};
