<?php

namespace App\Http\Controllers\TsekapV2\Forms\PchRiskAssessmentForm;

use Exception;
use App\Models\User;
use App\Models\TsekapV2\UserHealthFacility;
use App\Models\TsekapV2\Facilities;
use App\Models\TsekapV2\Forms\PchRiskAssessment\PchRiskAssessmentForm;
use App\Models\TsekapV2\Forms\PchRiskAssessment\PchRiskProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DataController extends Controller
{
    private function getHealthFacilityForUser(User $user): Facilities | null
    {
        $userHealthFacilityMapping = UserHealthFacility::where('user_id', $user->id)->first();
        if ($userHealthFacilityMapping) {
            return Facilities::select('id', 'name', 'address', 'hospital_type')
                ->where('id', $userHealthFacilityMapping->getAttribute('facility_id'))
                ->first();
        }
        return null;
    }

    //---- !!! ADAPTED FUNCTION !!! ----//
    public function retrievePchRiskProfileWithoutFacility(Request $request): JsonResponse | null
    {
        // Ensure the user is authenticated via Sanctum
        $user = $request->user();

        // Validate the request
        $validator = Validator::make($request->all(), [
            'filter' => 'required|string',
            'keyword' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        $filter = $request->query('filter');
        $keyword = $request->query('keyword');

        // Base query for risk profiles
        $query = PchRiskProfile::select(
            // profile metadata
            'pch_risk_assessment_tool_profile.id',
            'pch_risk_assessment_tool_profile.profile_id',
            'pch_risk_assessment_tool_profile.facility_id_updated',
            'pch_risk_assessment_tool_profile.encoded_by',
            'pch_risk_assessment_tool_profile.offline_entry',

            // profile and personal information
            'pch_risk_assessment_tool_profile.prefix',
            'pch_risk_assessment_tool_profile.lname',
            'pch_risk_assessment_tool_profile.fname',
            'pch_risk_assessment_tool_profile.mname',
            'pch_risk_assessment_tool_profile.suffix',
            'pch_risk_assessment_tool_profile.sex',
            'pch_risk_assessment_tool_profile.dob',

            'pch_risk_assessment_tool_profile.age', // added age field 
            'pch_risk_assessment_tool_profile.age_bracket_id', // added age_bracket_id field

            'pch_risk_assessment_tool_profile.birth_place',
            'pch_risk_assessment_tool_profile.civil_status',
            'pch_risk_assessment_tool_profile.educational_attainment',
            'pch_risk_assessment_tool_profile.employment_status',
            'pch_risk_assessment_tool_profile.occupation',

            'pch_risk_assessment_tool_profile.monthly_income', // added monthly_income field

            'pch_risk_assessment_tool_profile.religion',
            'pch_risk_assessment_tool_profile.other_religion',
            'pch_risk_assessment_tool_profile.indigenous',
            'pch_risk_assessment_tool_profile.blood_type',
            'pch_risk_assessment_tool_profile.mother_fname',
            'pch_risk_assessment_tool_profile.mother_mname',
            'pch_risk_assessment_tool_profile.mother_lname',
            'pch_risk_assessment_tool_profile.mother_dob',

            // location fields with foreign keys and contact information
            'pch_risk_assessment_tool_profile.country_id',
            'pch_risk_assessment_tool_profile.region_id',
            'pch_risk_assessment_tool_profile.province_id',
            'pch_risk_assessment_tool_profile.muncity_id',
            'pch_risk_assessment_tool_profile.barangay_id',
            'pch_risk_assessment_tool_profile.number_or_street_name',
            'pch_risk_assessment_tool_profile.zip_code',
            'pch_risk_assessment_tool_profile.email_address',
            'pch_risk_assessment_tool_profile.mobile_number',
            'pch_risk_assessment_tool_profile.landline_number',

            // other identifiers and membership information
            'pch_risk_assessment_tool_profile.family_member',
            'pch_risk_assessment_tool_profile.dswd_nhts_member',
            'pch_risk_assessment_tool_profile.four_ps_member',
            'pch_risk_assessment_tool_profile.facility_household_number',
            'pch_risk_assessment_tool_profile.family_serial_number',
            'pch_risk_assessment_tool_profile.philhealth_member',
            'pch_risk_assessment_tool_profile.philhealth_membership_type',
            'pch_risk_assessment_tool_profile.philhealth_number',
            'pch_risk_assessment_tool_profile.philhealth_category',
            'pch_risk_assessment_tool_profile.pcb_eligible',
            'pch_risk_assessment_tool_profile.created_at',
            'pch_risk_assessment_tool_profile.updated_at',
            'muncity.description as municipal_name',
            'province.description as province_name'
        )
            ->join('muncity', 'pch_risk_assessment_tool_profile.muncity_id', '=', 'muncity.id')
            ->join('province', 'pch_risk_assessment_tool_profile.province_id', '=', 'province.id');

        // Apply user privilege filters
        if ($user->getAttribute('user_priv') === 3) {
            $query->where('pch_risk_assessment_tool_profile.province_id', $user->getAttribute('province'));
        }

        // Apply keyword filter
        if ($keyword) {
            $query->where(function ($q) use ($filter, $keyword) {
                $columns = [
                    'id' => 'pch_risk_assessment_tool_profile.id', // included for filtering of ID also
                    'facility_id_updated' => 'pch_risk_assessment_tool_profile.facility_id_updated',
                    'fname' => 'pch_risk_assessment_tool_profile.fname',
                    'lname' => 'pch_risk_assessment_tool_profile.lname',
                    'dob' => 'pch_risk_assessment_tool_profile.dob'
                ];

                if ($filter === 'dob') {
                    // Parse keyword as date
                    $parsedDate = date('Y-m-d', strtotime($keyword));
                    $q->where($columns['dob'], $parsedDate);
                } elseif (isset($columns[$filter])) {
                    $q->where($columns[$filter], 'like', "%$keyword%");
                } else {
                    $q->where('pch_risk_assessment_tool_profile.fname', 'like', "%$keyword%")
                        ->orWhere('pch_risk_assessment_tool_profile.lname', 'like', "%$keyword%")
                        ->orWhere('pch_risk_assessment_tool_profile.dob', 'like', "%$keyword%")
                        ->orWhere('pch_risk_assessment_tool_profile.id', '=', $keyword) // included for filtering of ID also
                        ->orWhere('pch_risk_assessment_tool_profile.facility_id_updated', '=', $keyword);
                }
            });
        }

        // Paginate and return results
        $results = $query->orderBy('created_at', 'desc')->simplePaginate(30);
        return response()->json($results, 200);
    }

    // retrieval by facility using GET parameters
    public function retrievePchRiskProfileByFacility(Request $request): JsonResponse
    {
        // Ensure the user is authenticated via Sanctum
        $user = $request->user();

        // Validate the request
        $validator = Validator::make($request->all(), [
            'filter' => 'required|string',
            'keyword' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        $filter = $request->query('filter');
        $keyword = $request->query('keyword');

        // Retrieve the facility for the user
        $facility = $this->getHealthFacilityForUser($user);

        if (($user->getAttribute('user_priv') === 6 || $user->getAttribute('verified') !== 1) && !$facility) {
            return response()->json(['error' => 'Facility not found for user'], 404);
        }

        // Base query for risk profiles
        $query = PchRiskProfile::select(
            'pch_risk_assessment_tool_profile.id',
            'pch_risk_assessment_tool_profile.profile_id',
            'pch_risk_assessment_tool_profile.facility_id_updated',
            'pch_risk_assessment_tool_profile.encoded_by',
            'pch_risk_assessment_tool_profile.offline_entry',

            // profile and personal information
            'pch_risk_assessment_tool_profile.prefix',
            'pch_risk_assessment_tool_profile.lname',
            'pch_risk_assessment_tool_profile.fname',
            'pch_risk_assessment_tool_profile.mname',
            'pch_risk_assessment_tool_profile.suffix',
            'pch_risk_assessment_tool_profile.sex',
            'pch_risk_assessment_tool_profile.dob',

            'pch_risk_assessment_tool_profile.age', // added age field 
            'pch_risk_assessment_tool_profile.age_bracket_id', // added age_bracket_id field

            'pch_risk_assessment_tool_profile.birth_place',
            'pch_risk_assessment_tool_profile.civil_status',
            'pch_risk_assessment_tool_profile.educational_attainment',
            'pch_risk_assessment_tool_profile.employment_status',
            'pch_risk_assessment_tool_profile.occupation',

            'pch_risk_assessment_tool_profile.monthly_income', // added monthly_income field

            'pch_risk_assessment_tool_profile.religion',
            'pch_risk_assessment_tool_profile.other_religion',
            'pch_risk_assessment_tool_profile.indigenous',
            'pch_risk_assessment_tool_profile.blood_type',
            'pch_risk_assessment_tool_profile.mother_fname',
            'pch_risk_assessment_tool_profile.mother_mname',
            'pch_risk_assessment_tool_profile.mother_lname',
            'pch_risk_assessment_tool_profile.mother_dob',

            // location fields with foreign keys and contact information
            'pch_risk_assessment_tool_profile.country_id',
            'pch_risk_assessment_tool_profile.region_id',
            'pch_risk_assessment_tool_profile.province_id',
            'pch_risk_assessment_tool_profile.muncity_id',
            'pch_risk_assessment_tool_profile.barangay_id',
            'pch_risk_assessment_tool_profile.number_or_street_name',
            'pch_risk_assessment_tool_profile.zip_code',
            'pch_risk_assessment_tool_profile.email_address',
            'pch_risk_assessment_tool_profile.mobile_number',
            'pch_risk_assessment_tool_profile.landline_number',

            // other identifiers and membership information
            'pch_risk_assessment_tool_profile.family_member',
            'pch_risk_assessment_tool_profile.dswd_nhts_member',
            'pch_risk_assessment_tool_profile.four_ps_member',
            'pch_risk_assessment_tool_profile.facility_household_number',
            'pch_risk_assessment_tool_profile.family_serial_number',
            'pch_risk_assessment_tool_profile.philhealth_member',
            'pch_risk_assessment_tool_profile.philhealth_membership_type',
            'pch_risk_assessment_tool_profile.philhealth_number',
            'pch_risk_assessment_tool_profile.philhealth_category',
            'pch_risk_assessment_tool_profile.pcb_eligible',
            'pch_risk_assessment_tool_profile.created_at',
            'pch_risk_assessment_tool_profile.updated_at',
            'muncity.description as municipal_name',
            'province.description as province_name'
        )
            ->join('muncity', 'pch_risk_assessment_tool_profile.muncity_id', '=', 'muncity.id')
            ->join('province', 'pch_risk_assessment_tool_profile.province_id', '=', 'province.id');

        // Apply user privilege filters
        if ($user->getAttribute('user_priv') === 3) {
            $query->where('pch_risk_assessment_tool_profile.province_id', $user->getAttribute('province'));
        } elseif ($user->getAttribute('user_priv') === 6 && $facility) {
            $query->where('pch_risk_assessment_tool_profile.facility_id_updated', $facility->id);
        }

        // Apply keyword filter
        if ($keyword) {
            $query->where(function ($q) use ($filter, $keyword) {
                $columns = [
                    'facility_id_updated' => 'pch_risk_assessment_tool_profile.facility_id_updated',
                    'fname' => 'pch_risk_assessment_tool_profile.fname',
                    'lname' => 'pch_risk_assessment_tool_profile.lname',
                    'dob' => 'pch_risk_assessment_tool_profile.dob'
                ];

                if ($filter === 'dob') {
                    // Parse keyword as date
                    $parsedDate = date('Y-m-d', strtotime($keyword));
                    $q->where($columns['dob'], $parsedDate);
                } elseif (isset($columns[$filter])) {
                    $q->where($columns[$filter], 'like', "%$keyword%");
                } else {
                    $q->where('pch_risk_assessment_tool_profile.fname', 'like', "%$keyword%")
                        ->orWhere('pch_risk_assessment_tool_profile.lname', 'like', "%$keyword%")
                        ->orWhere('pch_risk_assessment_tool_profile.dob', 'like', "%$keyword%")
                        ->orWhere('pch_risk_assessment_tool_profile.facility_id_updated', '=', $keyword);
                }
            });
        }

        // Paginate and return results
        $results = $query->orderBy('created_at', 'desc')->simplePaginate(30);
        return response()->json($results, 200);
    }

    public function retrievePchRiskAssessmentForm(Request $request): JsonResponse
    {
        // Validate the request using the Validator facade
        $validator = Validator::make($request->all(), [
            'pch_profile_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        $id = $request->query('pch_profile_id');

        // Building the query
        $query = PchRiskAssessmentForm::select(
            'pch_risk_assessment_tool_form.id',
            'pch_risk_assessment_tool_form.pch_profile_id',
            'pch_risk_assessment_tool_form.nature_of_visit',
            'pch_risk_assessment_tool_form.nature_of_visit_duration',
            'pch_risk_assessment_tool_form.type_of_consultation',

            'pch_risk_assessment_tool_form.vit_bp_1st_reading',
            'pch_risk_assessment_tool_form.vit_bp_2nd_reading',
            'pch_risk_assessment_tool_form.vit_bp_3rd_reading',
            'pch_risk_assessment_tool_form.vit_bp_average_2nd_to_3rd_reading',

            'pch_risk_assessment_tool_form.vit_oxygen_saturation',
            'pch_risk_assessment_tool_form.vit_heart_rate_or_pulse_rate',
            'pch_risk_assessment_tool_form.vit_is_normal_rate',
            'pch_risk_assessment_tool_form.vit_is_regular_rhythm',
            'pch_risk_assessment_tool_form.vit_respiratory_rate',
            'pch_risk_assessment_tool_form.vit_temperature',
            'pch_risk_assessment_tool_form.vit_weight',
            'pch_risk_assessment_tool_form.vit_height',
            'pch_risk_assessment_tool_form.vit_waist_circumference',
            'pch_risk_assessment_tool_form.vit_bmi',
            'pch_risk_assessment_tool_form.vit_chief_complaint',
            'pch_risk_assessment_tool_form.vit_history_of_present_illness_and_remarks',

            'pch_risk_assessment_tool_form.comorb_hist_heart_attack_stroke_or_kidney_problems',
            'pch_risk_assessment_tool_form.comorb_hist_heart_attack_stroke_1st_degree_relatives',
            'pch_risk_assessment_tool_form.comorb_hypertension',
            'pch_risk_assessment_tool_form.comorb_hypertension_if_yes_taking_medications',
            'pch_risk_assessment_tool_form.comorb_diabetes_mellitus',
            'pch_risk_assessment_tool_form.comorb_diabetes_mellitus_if_yes_taking_medications',
            'pch_risk_assessment_tool_form.comorb_high_cholesterol',
            'pch_risk_assessment_tool_form.comorb_high_cholesterol_if_yes_taking_medications',
            'pch_risk_assessment_tool_form.comorb_tuberculosis',

            'pch_risk_assessment_tool_form.lifestyle_current_smoker',
            'pch_risk_assessment_tool_form.lifestyle_current_smoker_if_yes_tobacco_products',
            'pch_risk_assessment_tool_form.lifestyle_current_smoker_if_yes_vaporized_products',
            'pch_risk_assessment_tool_form.lifestyle_current_smoker_if_yes_both',
            'pch_risk_assessment_tool_form.lifestyle_binge_drinking_past_year',
            'pch_risk_assessment_tool_form.lifestyle_moderate_physical_activity_throughout_the_week',
            'pch_risk_assessment_tool_form.lifestyle_intake_of_fruits_and_veg_below_five_portions',
            'pch_risk_assessment_tool_form.lifestyle_non_laboratory_cvd_risk_percentage_color_code',

            'pch_risk_assessment_tool_form.mngt_counseling_on_healthy_diet',
            'pch_risk_assessment_tool_form.mngt_counseling_on_physical_activity',
            'pch_risk_assessment_tool_form.mngt_counseling_on_referred_for_bti',
            'pch_risk_assessment_tool_form.mngt_harmful_use_of_alcohol',
            'pch_risk_assessment_tool_form.mngt_referred_to_pcf_for_risk_screening',
            'pch_risk_assessment_tool_form.date_next_risk_assessment',

            'pch_risk_assessment_tool_form.assessed_by',
            'pch_risk_assessment_tool_form.verified_by',

            'pch_risk_assessment_tool_form.imm_record_child_none',
            'pch_risk_assessment_tool_form.imm_record_child_bcg',
            'pch_risk_assessment_tool_form.imm_record_child_opv',
            'pch_risk_assessment_tool_form.imm_record_child_polio_1',
            'pch_risk_assessment_tool_form.imm_record_child_polio_2',
            'pch_risk_assessment_tool_form.imm_record_child_polio_3',
            'pch_risk_assessment_tool_form.imm_record_child_hep_b1',
            'pch_risk_assessment_tool_form.imm_record_child_hep_b2',
            'pch_risk_assessment_tool_form.imm_record_child_hep_b3',
            'pch_risk_assessment_tool_form.imm_record_child_dpt1',
            'pch_risk_assessment_tool_form.imm_record_child_dpt2',
            'pch_risk_assessment_tool_form.imm_record_child_dpt3',
            'pch_risk_assessment_tool_form.imm_record_child_hib1',
            'pch_risk_assessment_tool_form.imm_record_child_hib2',
            'pch_risk_assessment_tool_form.imm_record_child_hib3',
            'pch_risk_assessment_tool_form.imm_record_child_covid19',

            'pch_risk_assessment_tool_form.imm_record_child_measles_mcv1_mr',
            'pch_risk_assessment_tool_form.imm_record_child_measles_mcv1_mmr',
            'pch_risk_assessment_tool_form.imm_record_child_measles_mcv2_mr',
            'pch_risk_assessment_tool_form.imm_record_child_measles_mcv2_mmr',
            'pch_risk_assessment_tool_form.imm_record_child_booster',
            'pch_risk_assessment_tool_form.imm_record_child_others',
            'pch_risk_assessment_tool_form.imm_record_child_others_please_specify',
            'pch_risk_assessment_tool_form.imm_record_schoolage_none',
            'pch_risk_assessment_tool_form.imm_record_schoolage_mr',
            'pch_risk_assessment_tool_form.imm_record_schoolage_td',
            'pch_risk_assessment_tool_form.imm_record_schoolage_hpv',
            'pch_risk_assessment_tool_form.imm_record_schoolage_others',
            'pch_risk_assessment_tool_form.imm_record_schoolage_others_please_specify',
            'pch_risk_assessment_tool_form.imm_record_pregnant_none',
            'pch_risk_assessment_tool_form.imm_record_pregnant_tetanus_toxoid',
            'pch_risk_assessment_tool_form.imm_record_pregnant_covid19',
            'pch_risk_assessment_tool_form.imm_record_pregnant_flu',
            'pch_risk_assessment_tool_form.imm_record_pregnant_others',
            'pch_risk_assessment_tool_form.imm_record_pregnant_others_please_specify',
            'pch_risk_assessment_tool_form.imm_record_adult_elderly_none',
            'pch_risk_assessment_tool_form.imm_record_adult_elderly_flu',
            'pch_risk_assessment_tool_form.imm_record_adult_elderly_pneumococcal',
            'pch_risk_assessment_tool_form.imm_record_adult_elderly_covid19',
            'pch_risk_assessment_tool_form.imm_record_adult_elderly_hpv',
            'pch_risk_assessment_tool_form.imm_record_adult_elderly_others',
            'pch_risk_assessment_tool_form.imm_record_adult_elderly_others_specify',

            'pch_risk_assessment_tool_form.menst_hist_menarche',
            'pch_risk_assessment_tool_form.menst_hist_age_menarche',
            'pch_risk_assessment_tool_form.menst_hist_menopause',
            'pch_risk_assessment_tool_form.menst_hist_age_menopause',
            'pch_risk_assessment_tool_form.menst_hist_no_of_pads_used_per_day',
            'pch_risk_assessment_tool_form.menst_hist_interval_cycle_of_menstruation_in_days',
            'pch_risk_assessment_tool_form.menst_hist_birth_control_method_used',
            'pch_risk_assessment_tool_form.menst_hist_onset_of_sexual_intercourse_age',

            'pch_risk_assessment_tool_form.preg_hist_gravidity',
            'pch_risk_assessment_tool_form.preg_hist_parity',
            'pch_risk_assessment_tool_form.preg_hist_no_of_full_term_pregnancy',
            'pch_risk_assessment_tool_form.preg_hist_no_of_premature_pregnancy',
            'pch_risk_assessment_tool_form.preg_hist_no_of_abortion',
            'pch_risk_assessment_tool_form.preg_hist_no_of_living_children',
            'pch_risk_assessment_tool_form.preg_hist_pre_eclampsia',
            'pch_risk_assessment_tool_form.preg_hist_with_access_to_family_planning',

            'pch_risk_assessment_tool_form.fam_hist_asthma',
            'pch_risk_assessment_tool_form.fam_hist_copd',
            'pch_risk_assessment_tool_form.fam_hist_hypertension',
            'pch_risk_assessment_tool_form.fam_hist_tuberculosis',
            'pch_risk_assessment_tool_form.fam_hist_cancer',
            'pch_risk_assessment_tool_form.fam_hist_diabetes',
            'pch_risk_assessment_tool_form.fam_hist_kidney_disease',
            'pch_risk_assessment_tool_form.fam_hist_peripheral_vascular_diseases',
            'pch_risk_assessment_tool_form.fam_hist_mental_disorders',
            'pch_risk_assessment_tool_form.fam_hist_others',
            'pch_risk_assessment_tool_form.fam_hist_others_please_specify',

            'pch_risk_assessment_tool_form.soc_hist_is_patient_illicit_drug_user',
            'pch_risk_assessment_tool_form.soc_hist_if_yes_indicate_type_of_illegal_drug_used',
            'pch_risk_assessment_tool_form.soc_hist_is_patient_sexually_active',
            'pch_risk_assessment_tool_form.soc_hist_sexual_activity_no_of_partner',
            'pch_risk_assessment_tool_form.soc_hist_sexual_activity_with_protection',

            'pch_risk_assessment_tool_form.risk_assessment_established_angina_pectoris',
            'pch_risk_assessment_tool_form.risk_assessment_with_left_ventricular_hypertrophy',
            'pch_risk_assessment_tool_form.risk_assessment_wo_est_cvd_with_8mmol_of_cholesterol',
            'pch_risk_assessment_tool_form.risk_assessment_wo_est_cvd_who_have_persistent_raised_bp',
            'pch_risk_assessment_tool_form.risk_assessment_with_type1_or_type2_diabetes',
            'pch_risk_assessment_tool_form.risk_assessment_with_known_renal_failure_or_impairment',

            'pch_risk_assessment_tool_form.bp_2nd_encounter_1st_reading',
            'pch_risk_assessment_tool_form.bp_2nd_encounter_2nd_reading',
            'pch_risk_assessment_tool_form.bp_2nd_encounter_3rd_reading',
            'pch_risk_assessment_tool_form.bp_2nd_encounter_average_2nd_to_3rd_reading',
            'pch_risk_assessment_tool_form.individual_have_all_classic_symptoms_marked',
            'pch_risk_assessment_tool_form.urine_ketones_result',
            'pch_risk_assessment_tool_form.urine_ketones_result_date_taken',
            'pch_risk_assessment_tool_form.total_cholesterol_result',
            'pch_risk_assessment_tool_form.total_cholesterol_result_date_taken',
            'pch_risk_assessment_tool_form.random_plasma_glucose_result',
            'pch_risk_assessment_tool_form.random_plasma_glucose_result_date_taken',
            'pch_risk_assessment_tool_form.fasting_plasma_glucose_result',
            'pch_risk_assessment_tool_form.fasting_plasma_glucose_result_date_taken',
            'pch_risk_assessment_tool_form.confirmatory_fpg_result',
            'pch_risk_assessment_tool_form.confirmatory_fpg_result_date_taken',
            'pch_risk_assessment_tool_form.basic_labs_for_confirmed_hypertensives_12l_ecg',
            'pch_risk_assessment_tool_form.basic_labs_for_confirmed_hypertensives_blood_test',
            'pch_risk_assessment_tool_form.basic_labs_for_confirmed_hypertensives_dipstick',

            'pch_risk_assessment_tool_form.laboratory_cvd_risk_percentage_color_code',
            'pch_risk_assessment_tool_form.mngt_2_counseling_on_healthy_diet',
            'pch_risk_assessment_tool_form.mngt_2_counseling_on_physical_activity',
            'pch_risk_assessment_tool_form.mngt_2_counseling_on_tobacco_cessation',
            'pch_risk_assessment_tool_form.mngt_2_counseling_on_harmful_use_of_alcohol',
            'pch_risk_assessment_tool_form.medications_anti_hypertension',
            'pch_risk_assessment_tool_form.medications_yes_anti_hypertension_out_of_pkt',
            'pch_risk_assessment_tool_form.medications_yes_anti_hypertension_both_pbf_and_oop',
            'pch_risk_assessment_tool_form.medications_yes_anti_hypertension_provided',
            'pch_risk_assessment_tool_form.medications_oral_hypoglycemic_agents_or_insulin',
            'pch_risk_assessment_tool_form.medications_yes_oral_hypoglycemic_agents_or_insulin_provided',
            'pch_risk_assessment_tool_form.medications_yes_oral_hypoglycemic_agents_or_insulin_out_of_pkt',
            'pch_risk_assessment_tool_form.medications_oral_hypoglycemic_agents_or_insulin_both_pbf_and_oop',
            'pch_risk_assessment_tool_form.risk_assessment_ii_date_of_follow_up',
            'pch_risk_assessment_tool_form.physicians_name_risk_assessment_pt_ii',

            'pch_risk_assessment_tool_form.pe_skin_extremities_essentially_normal',
            'pch_risk_assessment_tool_form.pe_skin_extremities_clubbing',
            'pch_risk_assessment_tool_form.pe_skin_extremities_cold_clammy',
            'pch_risk_assessment_tool_form.pe_skin_extremities_pale_nailbeds',
            'pch_risk_assessment_tool_form.pe_skin_extremities_cyanosis',
            'pch_risk_assessment_tool_form.pe_skin_extremities_poor_skin_turgor',
            'pch_risk_assessment_tool_form.pe_skin_extremities_edema',
            'pch_risk_assessment_tool_form.pe_skin_extremities_itching',
            'pch_risk_assessment_tool_form.pe_skin_extremities_erythema',
            'pch_risk_assessment_tool_form.pe_skin_extremities_lesions',
            'pch_risk_assessment_tool_form.pe_heent_essentially_normal',
            'pch_risk_assessment_tool_form.pe_heent_abnormal_pupillary_reaction',
            'pch_risk_assessment_tool_form.pe_heent_cervical_lymphadenopathy',
            'pch_risk_assessment_tool_form.pe_heent_icteric_sclera',
            'pch_risk_assessment_tool_form.pe_heent_pale_conjunctivae',
            'pch_risk_assessment_tool_form.pe_heent_sunken_eyeballs',
            'pch_risk_assessment_tool_form.pe_chest_essentially_normal',
            'pch_risk_assessment_tool_form.pe_chest_asymmetric_chest_expansion',
            'pch_risk_assessment_tool_form.pe_chest_wheezes',
            'pch_risk_assessment_tool_form.pe_chest_crackles',
            'pch_risk_assessment_tool_form.pe_chest_enlarge_axillary_lymph_nodes',
            'pch_risk_assessment_tool_form.pe_chest_decreased_breath_sounds',
            'pch_risk_assessment_tool_form.pe_chest_lumps_over_breast',
            'pch_risk_assessment_tool_form.pe_heart_essentially_normal',
            'pch_risk_assessment_tool_form.pe_heart_displaced_apex_beat',
            'pch_risk_assessment_tool_form.pe_heart_heart_murmur',
            'pch_risk_assessment_tool_form.pe_heart_irregular_rhythm',
            'pch_risk_assessment_tool_form.pe_heart_heaves',
            'pch_risk_assessment_tool_form.pe_abdomen_essentially_normal',
            'pch_risk_assessment_tool_form.pe_abdomen_abdominal_rigidity',
            'pch_risk_assessment_tool_form.pe_abdomen_palpable_mass',
            'pch_risk_assessment_tool_form.pe_abdomen_tenderness',
            'pch_risk_assessment_tool_form.pe_abdomen_hyperactive_bowel_sounds',
            'pch_risk_assessment_tool_form.pe_alert_type_allergy',
            'pch_risk_assessment_tool_form.pe_alert_type_disability',
            'pch_risk_assessment_tool_form.pe_alert_type_drug',
            'pch_risk_assessment_tool_form.pe_alert_type_handicap',
            'pch_risk_assessment_tool_form.pe_alert_type_impairment',
            'pch_risk_assessment_tool_form.pe_alert_type_others',
            'pch_risk_assessment_tool_form.pe_alert_type_description',

            'pch_risk_assessment_tool_form.animal_bite_loc_abdomen',
            'pch_risk_assessment_tool_form.animal_bite_loc_chest',
            'pch_risk_assessment_tool_form.animal_bite_loc_forearm',
            'pch_risk_assessment_tool_form.animal_bite_loc_back',
            'pch_risk_assessment_tool_form.animal_bite_loc_eye',
            'pch_risk_assessment_tool_form.animal_bite_loc_hand',
            'pch_risk_assessment_tool_form.animal_bite_loc_buttocks',
            'pch_risk_assessment_tool_form.animal_bite_loc_foot',
            'pch_risk_assessment_tool_form.animal_bite_loc_head',
            'pch_risk_assessment_tool_form.animal_bite_loc_neck',
            'pch_risk_assessment_tool_form.animal_bite_loc_thigh',
            'pch_risk_assessment_tool_form.animal_bite_loc_pelvic',
            'pch_risk_assessment_tool_form.animal_bite_loc_knee',
            'pch_risk_assessment_tool_form.animal_bite_loc_legs',
            'pch_risk_assessment_tool_form.animal_bite_loc_mouth',
            'pch_risk_assessment_tool_form.animal_bite_loc_nose',
            'pch_risk_assessment_tool_form.animal_bite_loc_ears',
            'pch_risk_assessment_tool_form.animal_bite_loc_others',
            'pch_risk_assessment_tool_form.animal_bite_loc_others_specify',
            'pch_risk_assessment_tool_form.animal_type_dog',
            'pch_risk_assessment_tool_form.animal_type_pig',
            'pch_risk_assessment_tool_form.animal_type_rat',
            'pch_risk_assessment_tool_form.animal_type_snake',
            'pch_risk_assessment_tool_form.animal_type_cat',
            'pch_risk_assessment_tool_form.animal_type_others',
            'pch_risk_assessment_tool_form.animal_type_others_specify',
            'pch_risk_assessment_tool_form.animal_bite_description_of_bite_event',
            'pch_risk_assessment_tool_form.animal_bite_type_of_exposure_transdermal_bite',
            'pch_risk_assessment_tool_form.animal_bite_type_of_exposure_punctured_wounds',
            'pch_risk_assessment_tool_form.animal_bite_type_of_exposure_lacerations',
            'pch_risk_assessment_tool_form.animal_bite_type_of_exposure_avulsions',
            'pch_risk_assessment_tool_form.animal_bite_type_of_exposure_scratches_abrasions_w_sponti_bleed',
            'pch_risk_assessment_tool_form.animal_bite_wash_bite',
            'pch_risk_assessment_tool_form.animal_bite_date_of_exposure',
            'pch_risk_assessment_tool_form.animal_bite_name_of_accompanying_adult',
            'pch_risk_assessment_tool_form.animal_bite_contact_number',
            'pch_risk_assessment_tool_form.animal_bite_relationship_to_patient',

            'pch_risk_assessment_tool_form.geriatric_memory_1',
            'pch_risk_assessment_tool_form.geriatric_depression',
            'pch_risk_assessment_tool_form.geriatric_medication',
            'pch_risk_assessment_tool_form.geriatric_urinary_incontinence',
            // 'pch_risk_assessment_tool_form.geriatric_urinary_incontinence_symptoms_specify',
            // 'pch_risk_assessment_tool_form.geriatric_urinary_incontinence_counsel_specify',
            'pch_risk_assessment_tool_form.geriatric_physical_function_capacity',
            'pch_risk_assessment_tool_form.geriatric_memory_2',
            'pch_risk_assessment_tool_form.geriatric_fall',
            'pch_risk_assessment_tool_form.geriatric_risk_for_falls_seconds',
            'pch_risk_assessment_tool_form.geriatric_risk_for_falls_seconds_indication',
            'pch_risk_assessment_tool_form.geriatric_risk_for_falls_inches',
            'pch_risk_assessment_tool_form.geriatric_risk_for_falls_inches_indication',
            'pch_risk_assessment_tool_form.geriatric_nutrition_cm',
            'pch_risk_assessment_tool_form.geriatric_nutrition_cm_indication',
            'pch_risk_assessment_tool_form.geriatric_hearing_test_r_ear_indication',
            'pch_risk_assessment_tool_form.geriatric_hearing_test_l_ear_indication',
            'pch_risk_assessment_tool_form.geriatric_vision_test_unaided_r_eye',
            'pch_risk_assessment_tool_form.geriatric_vision_test_unaided_l_eye',
            'pch_risk_assessment_tool_form.geriatric_vision_test_aided_r_eye',
            'pch_risk_assessment_tool_form.geriatric_vision_test_aided_l_eye',
            'pch_risk_assessment_tool_form.geriatric_summary_of_findings_counsel',
            'pch_risk_assessment_tool_form.geriatric_summary_of_findings_date_of_return_visit',
            'pch_risk_assessment_tool_form.geriatric_summary_of_findings_refer_to_a_physician',
            'pch_risk_assessment_tool_form.geriatric_summary_of_findings_date_of_referral',
            'pch_risk_assessment_tool_form.geriatric_summary_of_findings_reason_for_referral',
            'pch_risk_assessment_tool_form.geriatric_name_and_designation_of_the_provider',
            'pch_risk_assessment_tool_form.geriatric_name_and_address_of_the_facility',
            'pch_risk_assessment_tool_form.geriatric_facility_contact_details',
            'pch_risk_assessment_tool_form.geriatric_signature_of_the_provider',
            'pch_risk_assessment_tool_form.geriatric_referred_to',

            'pch_risk_assessment_tool_form.lab_req_blood_chemistry',
            'pch_risk_assessment_tool_form.lab_req_fecalysis',
            'pch_risk_assessment_tool_form.lab_req_mtb_genexpert',
            'pch_risk_assessment_tool_form.lab_req_urinalysis',
            'pch_risk_assessment_tool_form.lab_req_clinical_chemistry',
            'pch_risk_assessment_tool_form.lab_req_hematology',
            'pch_risk_assessment_tool_form.lab_req_serology',
            'pch_risk_assessment_tool_form.lab_req_complete_blood_count',
            'pch_risk_assessment_tool_form.lab_req_immunology',
            'pch_risk_assessment_tool_form.lab_req_sputum_microscopy',

            'pch_risk_assessment_tool_form.imaging_ecg',
            'pch_risk_assessment_tool_form.imaging_xray',
            'pch_risk_assessment_tool_form.imaging_mri',
            'pch_risk_assessment_tool_form.imaging_ct_scan',
            'pch_risk_assessment_tool_form.imaging_ultrasound',
            'pch_risk_assessment_tool_form.imaging_2d_echo',
            'pch_risk_assessment_tool_form.imaging_via',
            'pch_risk_assessment_tool_form.imaging_pap_smear',
            'pch_risk_assessment_tool_form.imaging_mammogram',
            'pch_risk_assessment_tool_form.imaging_with_contrast',
            'pch_risk_assessment_tool_form.diagnosis',
            'pch_risk_assessment_tool_form.treatment_plan',
            'pch_risk_assessment_tool_form.follow_up_date',
            'pch_risk_assessment_tool_form.prescription',
            'pch_risk_assessment_tool_form.refer_to_higher_facility',
            'pch_risk_assessment_tool_form.remarks',
            'pch_risk_assessment_tool_form.created_at',
            'pch_risk_assessment_tool_form.updated_at',
        );

        if ($id) {
            $query->where('pch_profile_id', "=", $id);
        }
        return response()->json($query->simplePaginate(30), 200);
    }

    private function calculateAge(string $dob, ?string $asOfDate = null): ?int
    {
        try {
            $dob = new \DateTime($dob);
            $asOf = $asOfDate ? new \DateTime($asOfDate) : new \DateTime();
            $age = $dob->diff($asOf)->y;
            return $age;
        } catch (Exception $e) {
            Log::error('Error calculating age: ' . $e->getMessage());
            return null;
        }
    }

    public function addPchRiskProfile(Request $request): JsonResponse
    {
        $fields = $request->input('fields');

        // Calculate age from dob if dob is present
        if (!empty($fields['dob'])) {
            $fields['age'] = $this->calculateAge($fields['dob']);
        }

        // Define validation rules
        $rules = [
            // profile metadata
            'fields' => 'required|array',
            'fields.profile_id' => 'nullable|integer',
            'fields.facility_id_updated' => 'required|integer',
            'fields.encoded_by' => 'required|integer',
            'fields.offline_entry' => 'required|boolean',

            // profile and personal information
            'fields.prefix' => 'sometimes|nullable|string|max:15',
            'fields.lname' => 'required|string|max:255',
            'fields.fname' => 'required|string|max:255',
            'fields.mname' => 'sometimes|nullable|string|max:255',
            'fields.suffix' => 'sometimes|nullable|string|max:15',
            'fields.sex' => 'required|string|max:10',
            'fields.dob' => 'required|date',
            'fields.age' => 'sometimes|numeric|min:0|max:120', // added age field
            'fields.birth_place' => 'required|string',
            'fields.civil_status' => 'required|string|max:20',
            'fields.educational_attainment' => 'required|string|max:50',
            'fields.employment_status' => 'required|string|max:50',
            'fields.occupation' => 'sometimes|nullable|string|max:255',
            'fields.monthly_income' => 'sometimes|nullable|string|max:50', // added monthly income field
            'fields.religion' =>  'sometimes|nullable|string|max:50',
            'fields.other_religion' => 'sometimes|nullable|string|max:255',
            'fields.indigenous' => 'sometimes|nullable|string|max:50',
            'fields.blood_type' => 'sometimes|nullable|string|max:5',
            'fields.mother_fname' => 'required|string|max:255',
            'fields.mother_mname' => 'sometimes|nullable|string|max:255',
            'fields.mother_lname' => 'required|string|max:255',
            'fields.mother_dob' => 'required|date',

            // location fields with foreign keys and contact information
            'fields.country_id' => 'required|integer',
            'fields.region_id' => 'required|integer',
            'fields.province_id' => 'required|integer',
            'fields.muncity_id' => 'required|integer',
            'fields.barangay_id' => 'required|integer',

            'fields.number_or_street_name' => 'sometimes|nullable|string',
            'fields.zip_code' => 'required|integer',
            'fields.email_address' => 'sometimes|nullable|email',
            'fields.mobile_number' => 'sometimes|nullable|string|max:25',
            'fields.landline_number' => 'sometimes|nullable|string|max:25',

            // other identifiers and membership information
            'fields.family_member' => 'sometimes|nullable|string|max:50',
            'fields.dswd_nhts_member' => 'sometimes|nullable|boolean',
            'fields.four_ps_member' => 'sometimes|nullable|boolean',
            'fields.facility_household_number' => 'sometimes|nullable|string|max:50',
            'fields.family_serial_number' => 'sometimes|nullable|string|max:50',
            'fields.philhealth_member' => 'sometimes|nullable|string|max:25',
            'fields.philhealth_membership_type' => 'sometimes|nullable|string|max:50',
            'fields.philhealth_number' => 'sometimes|nullable|string|max:50',
            'fields.philhealth_category' => 'sometimes|nullable|string|max:25',
            'fields.pcb_eligible' => 'sometimes|nullable|string|max:25',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // ---- Redacted ----
        // Check for malformed parameters
        if ($fields['offline_entry'] === false && empty($fields['profile_id'])) {
            return response()->json(['error' => 'Malformed parameter. Please recheck request.'], 403);
        }

        // Check for duplicates
        $existingRiskProfile = PchRiskProfile::where('fname', $fields['fname'])
            ->where('lname', $fields['lname'])
            ->where('dob', $fields['dob'])
            ->where('facility_id_updated', $fields['facility_id_updated'])
            ->whereDate('created_at', '!=', now()->toDateString()); // Exclude records created today

        if (!empty($fields['mname'])) {
            $existingRiskProfile->where('mname', $fields['mname']);
        } else {
            $existingRiskProfile->whereNull('mname');
        }

        if (!empty($fields['profile_id'])) {
            $existingRiskProfile->where('profile_id', $fields['profile_id']);
        }

        if (!empty($fields['prefix'])) {
            $existingRiskProfile->where('prefix', $fields['prefix']);
        } else {
            $existingRiskProfile->whereNull('prefix');
        }

        if (!empty($fields['suffix'])) {
            $existingRiskProfile->where('suffix', $fields['suffix']);
        } else {
            $existingRiskProfile->whereNull('suffix');
        }

        if ($existingRiskProfile->exists()) {
            return response()->json(['error' => 'Duplicate in entered data. Please recheck.'], 409);
        }

        // Determine the age_bracket_id based on the age
        $ageBrackets = [
            [0, 0.0164, 1],
            [0.0165, 0.0767, 2],
            [0.0768, 0.9167, 3],
            [1, 4, 4],
            [5, 9, 5],
            [10, 14, 6],
            [15, 19, 7],
            [20, 24, 8],
            [25, 29, 9],
            [30, 34, 10],
            [35, 39, 11],
            [40, 44, 12],
            [45, 49, 13],
            [50, 54, 14],
            [55, 59, 15],
            [60, 64, 16],
            [65, 69, 17],
            [70, PHP_INT_MAX, 18]
        ];

        foreach ($ageBrackets as [$min, $max, $id]) {
            if ($fields['age'] >= $min && $fields['age'] <= $max) {
                $fields['age_bracket_id'] = $id;
                break;
            }
        }

        // Save the record
        try {
            $pchRiskProfile = new PchRiskProfile();
            $pchRiskProfile->fill($fields);
            $pchRiskProfile->save();

            return response()->json([
                'message' => 'Entry successfully saved.',
                'id' => $pchRiskProfile->getAttribute('id')
            ], 200);
        } catch (Exception $e) {
            Log::error('An error occurred while adding a pch risk profile: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    public function addPchRiskForm(Request $request): JsonResponse
    {
        $fields = $request->input('fields');

        // Define validation rules
        $rules = [
            'fields' => 'required|array',
            'fields.pch_profile_id' => 'required|integer',
            'fields.nature_of_visit' => 'required|string',
            'fields.nature_of_visit_duration' => 'required|string|max:50',
            'fields.type_of_consultation' => 'required|string',

            // vital signs fields
            'fields.vit_bp_1st_reading' => 'required|string|max:10',
            'fields.vit_bp_2nd_reading' => 'required|string|max:10',
            'fields.vit_bp_3rd_reading' => 'required|string|max:10',
            'fields.vit_bp_average_2nd_to_3rd_reading' => 'required|string|max:10',

            'fields.vit_oxygen_saturation' => 'required|string',
            'fields.vit_heart_rate_or_pulse_rate' => 'required|string',
            'fields.vit_is_normal_rate' => 'required|boolean',
            'fields.vit_is_regular_rhythm' => 'required|boolean',
            'fields.vit_respiratory_rate' => 'required|numeric|min:0',
            'fields.vit_temperature' => 'required|numeric|min:0',
            'fields.vit_weight' => 'required|numeric|min:0',
            'fields.vit_height' => 'required|numeric|min:0',
            'fields.vit_waist_circumference' => 'required|numeric|min:0',
            'fields.vit_bmi' => 'required|numeric|min:0',
            'fields.vit_chief_complaint' => 'required|string',
            'fields.vit_history_of_present_illness_and_remarks' => 'nullable|string',

            // comorbidities fields
            'fields.comorb_hist_heart_attack_stroke_or_kidney_problems' => 'nullable|boolean',
            'fields.comorb_hist_heart_attack_stroke_1st_degree_relatives' => 'nullable|boolean',
            'fields.comorb_hypertension' => 'nullable|boolean',
            'fields.comorb_hypertension_if_yes_taking_medications' => 'nullable|boolean',
            'fields.comorb_diabetes_mellitus' => 'nullable|boolean',
            'fields.comorb_diabetes_mellitus_if_yes_taking_medications' => 'nullable|boolean',
            'fields.comorb_high_cholesterol' => 'nullable|boolean',
            'fields.comorb_high_cholesterol_if_yes_taking_medications' => 'nullable|boolean',
            'fields.comorb_tuberculosis' => 'nullable|boolean',

            // lifestyle fields
            'fields.lifestyle_current_smoker' => 'nullable|boolean',
            'fields.lifestyle_current_smoker_if_yes_tobacco_products' => 'nullable|boolean',
            'fields.lifestyle_current_smoker_if_yes_vaporized_products' => 'nullable|boolean',
            'fields.lifestyle_current_smoker_if_yes_both' => 'nullable|boolean',
            'fields.lifestyle_binge_drinking_past_year' => 'nullable|boolean',
            'fields.lifestyle_moderate_physical_activity_throughout_the_week' => 'nullable|boolean',
            'fields.lifestyle_intake_of_fruits_and_veg_below_five_portions' => 'nullable|boolean',
            'fields.lifestyle_non_laboratory_cvd_risk_percentage_color_code' => 'nullable|string|max:10',

            // management fields
            'fields.mngt_counseling_on_healthy_diet' => 'nullable|boolean',
            'fields.mngt_counseling_on_physical_activity' => 'nullable|boolean',
            'fields.mngt_counseling_on_referred_for_bti' => 'nullable|boolean',
            'fields.mngt_harmful_use_of_alcohol' => 'nullable|boolean',
            'fields.mngt_referred_to_pcf_for_risk_screening' => 'nullable|boolean',
            'fields.date_next_risk_assessment' => 'nullable|date',

            // management and lifestyle
            'fields.assessed_by' => 'nullable|string|max:100',
            'fields.verified_by' => 'nullable|string|max:100',

            // immunization record fields
            'fields.imm_record_child_none' => 'nullable|boolean',
            'fields.imm_record_child_bcg' => 'nullable|boolean',
            'fields.imm_record_child_opv' => 'nullable|boolean',
            'fields.imm_record_child_polio_1' => 'nullable|boolean',
            'fields.imm_record_child_polio_2' => 'nullable|boolean',
            'fields.imm_record_child_polio_3' => 'nullable|boolean',
            'fields.imm_record_child_hep_b1' => 'nullable|boolean',
            'fields.imm_record_child_hep_b2' => 'nullable|boolean',
            'fields.imm_record_child_hep_b3' => 'nullable|boolean',
            'fields.imm_record_child_dpt1' => 'nullable|boolean',
            'fields.imm_record_child_dpt2' => 'nullable|boolean',
            'fields.imm_record_child_dpt3' => 'nullable|boolean',
            'fields.imm_record_child_hib1' => 'nullable|boolean',
            'fields.imm_record_child_hib2' => 'nullable|boolean',
            'fields.imm_record_child_hib3' => 'nullable|boolean',
            'fields.imm_record_child_covid19' => 'nullable|boolean',
            'fields.imm_record_child_measles_mcv1_mr' => 'nullable|boolean',
            'fields.imm_record_child_measles_mcv1_mmr' => 'nullable|boolean',
            'fields.imm_record_child_measles_mcv2_mr' => 'nullable|boolean',
            'fields.imm_record_child_measles_mcv2_mmr' => 'nullable|boolean',
            'fields.imm_record_child_booster' => 'nullable|boolean',
            'fields.imm_record_child_others' => 'nullable|boolean',
            'fields.imm_record_child_others_please_specify' => 'nullable|string',
            'fields.imm_record_schoolage_none' => 'nullable|boolean',
            'fields.imm_record_schoolage_mr' => 'nullable|boolean',
            'fields.imm_record_schoolage_td' => 'nullable|boolean',
            'fields.imm_record_schoolage_hpv' => 'nullable|boolean',
            'fields.imm_record_schoolage_others' => 'nullable|boolean',
            'fields.imm_record_schoolage_others_please_specify' => 'nullable|string',
            'fields.imm_record_pregnant_none' => 'nullable|boolean',
            'fields.imm_record_pregnant_tetanus_toxoid' => 'nullable|boolean',
            'fields.imm_record_pregnant_covid19' => 'nullable|boolean',
            'fields.imm_record_pregnant_flu' => 'nullable|boolean',
            'fields.imm_record_pregnant_others' => 'nullable|boolean',
            'fields.imm_record_pregnant_others_please_specify' => 'nullable|string',
            'fields.imm_record_adult_elderly_none' => 'nullable|boolean',
            'fields.imm_record_adult_elderly_flu' => 'nullable|boolean',
            'fields.imm_record_adult_elderly_pneumococcal' => 'nullable|boolean',
            'fields.imm_record_adult_elderly_covid19' => 'nullable|boolean',
            'fields.imm_record_adult_elderly_hpv' => 'nullable|boolean',
            'fields.imm_record_adult_elderly_others' => 'nullable|boolean',
            'fields.imm_record_adult_elderly_others_specify' => 'nullable|string',

            // menstrual history fields
            'fields.menst_hist_menarche' => 'nullable|boolean',
            'fields.menst_hist_age_menarche' => 'nullable|numeric|min:0',
            'fields.menst_hist_menopause' => 'nullable|boolean',
            'fields.menst_hist_age_menopause' => 'nullable|numeric|min:0',
            'fields.menst_hist_no_of_pads_used_per_day' => 'nullable|integer|min:0',
            'fields.menst_hist_interval_cycle_of_menstruation_in_days' => 'nullable|numeric|min:0',
            'fields.menst_hist_birth_control_method_used' => 'nullable|string',
            'fields.menst_hist_onset_of_sexual_intercourse_age' => 'nullable|numeric|min:0',

            // pregnancy history fields
            'fields.preg_hist_gravidity' => 'nullable|integer|min:0',
            'fields.preg_hist_parity' => 'nullable|integer|min:0',
            'fields.preg_hist_no_of_full_term_pregnancy' => 'nullable|integer|min:0',
            'fields.preg_hist_no_of_premature_pregnancy' => 'nullable|integer|min:0',
            'fields.preg_hist_no_of_abortion' => 'nullable|integer|min:0',
            'fields.preg_hist_no_of_living_children' => 'nullable|integer|min:0',
            'fields.preg_hist_pre_eclampsia' => 'nullable|boolean',
            'fields.preg_hist_with_access_to_family_planning' => 'nullable|boolean',

            // family history fields
            'fields.fam_hist_asthma' => 'nullable|boolean',
            'fields.fam_hist_copd' => 'nullable|boolean',
            'fields.fam_hist_hypertension' => 'nullable|boolean',
            'fields.fam_hist_tuberculosis' => 'nullable|boolean',
            'fields.fam_hist_cancer' => 'nullable|boolean',
            'fields.fam_hist_diabetes' => 'nullable|boolean',
            'fields.fam_hist_kidney_disease' => 'nullable|boolean',
            'fields.fam_hist_peripheral_vascular_diseases' => 'nullable|boolean',
            'fields.fam_hist_mental_disorders' => 'nullable|boolean',
            'fields.fam_hist_others' => 'nullable|boolean',
            'fields.fam_hist_others_please_specify' => 'nullable|string',

            // social history fields
            'fields.soc_hist_is_patient_illicit_drug_user' => 'nullable|boolean',
            'fields.soc_hist_if_yes_indicate_type_of_illegal_drug_used' => 'nullable|string|max:255',
            'fields.soc_hist_is_patient_sexually_active' => 'nullable|boolean',
            'fields.soc_hist_sexual_activity_no_of_partner' => 'nullable|string|max:10',
            'fields.soc_hist_sexual_activity_with_protection' => 'nullable|boolean',

            // risk assessment pt. II fields
            'fields.risk_assessment_established_angina_pectoris' => 'nullable|boolean',
            'fields.risk_assessment_with_left_ventricular_hypertrophy' => 'nullable|boolean',
            'fields.risk_assessment_wo_est_cvd_with_8mmol_of_cholesterol' => 'nullable|boolean',
            'fields.risk_assessment_wo_est_cvd_who_have_persistent_raised_bp' => 'nullable|boolean',
            'fields.risk_assessment_with_type1_or_type2_diabetes' => 'nullable|boolean',
            'fields.risk_assessment_with_known_renal_failure_or_impairment' => 'nullable|boolean',

            'fields.bp_2nd_encounter_1st_reading' => 'nullable|string|max:10',
            'fields.bp_2nd_encounter_2nd_reading' => 'nullable|string|max:10',
            'fields.bp_2nd_encounter_3rd_reading' => 'nullable|string|max:10',
            'fields.bp_2nd_encounter_average_2nd_to_3rd_reading' => 'nullable|string|max:10',
            'fields.individual_have_all_classic_symptoms_marked' => 'nullable|boolean',
            'fields.urine_ketones_result' => 'nullable|numeric|min:0',
            'fields.urine_ketones_result_date_taken' => 'nullable|date',
            'fields.total_cholesterol_result' => 'nullable|numeric|min:0',
            'fields.total_cholesterol_result_date_taken' => 'nullable|date',
            'fields.random_plasma_glucose_result' => 'nullable|numeric|min:0',
            'fields.random_plasma_glucose_result_date_taken' => 'nullable|date',
            'fields.fasting_plasma_glucose_result' => 'nullable|numeric|min:0',
            'fields.fasting_plasma_glucose_result_date_taken' => 'nullable|date',
            'fields.confirmatory_fpg_result' => 'nullable|numeric|min:0',
            'fields.confirmatory_fpg_result_date_taken' => 'nullable|date',
            'fields.basic_labs_for_confirmed_hypertensives_12l_ecg' => 'nullable|boolean',
            'fields.basic_labs_for_confirmed_hypertensives_blood_test' => 'nullable|boolean',
            'fields.basic_labs_for_confirmed_hypertensives_dipstick' => 'nullable|boolean',

            'fields.laboratory_cvd_risk_percentage_color_code' => 'nullable|string|max:10',
            'fields.mngt_2_counseling_on_healthy_diet' => 'nullable|boolean',
            'fields.mngt_2_counseling_on_physical_activity' => 'nullable|boolean',
            'fields.mngt_2_counseling_on_tobacco_cessation' => 'nullable|boolean',
            'fields.mngt_2_counseling_on_harmful_use_of_alcohol' => 'nullable|boolean',
            'fields.medications_anti_hypertension' => 'nullable|boolean',
            'fields.medications_yes_anti_hypertension_out_of_pkt' => 'nullable|boolean',
            'fields.medications_yes_anti_hypertension_both_pbf_and_oop' => 'nullable|boolean',
            'fields.medications_yes_anti_hypertension_provided' => 'nullable|boolean',
            'fields.medications_oral_hypoglycemic_agents_or_insulin' => 'nullable|boolean',
            'fields.medications_yes_oral_hypoglycemic_agents_or_insulin_provided' => 'nullable|boolean',
            'fields.medications_yes_oral_hypoglycemic_agents_or_insulin_out_of_pkt' => 'nullable|boolean',
            'fields.medications_oral_hypoglycemic_agents_or_insulin_both_pbf_and_oop' => 'nullable|boolean',
            'fields.risk_assessment_ii_date_of_follow_up' => 'nullable|date',
            'fields.physicians_name_risk_assessment_pt_ii' => 'nullable|string|max:255',

            // physical exam fields
            'fields.pe_skin_extremities_essentially_normal' => 'nullable|boolean',
            'fields.pe_skin_extremities_clubbing' => 'nullable|boolean',
            'fields.pe_skin_extremities_cold_clammy' => 'nullable|boolean',
            'fields.pe_skin_extremities_pale_nailbeds' => 'nullable|boolean',
            'fields.pe_skin_extremities_cyanosis' => 'nullable|boolean',
            'fields.pe_skin_extremities_poor_skin_turgor' => 'nullable|boolean',
            'fields.pe_skin_extremities_edema' => 'nullable|boolean',
            'fields.pe_skin_extremities_itching' => 'nullable|boolean',
            'fields.pe_skin_extremities_erythema' => 'nullable|boolean',
            'fields.pe_skin_extremities_lesions' => 'nullable|boolean',
            'fields.pe_heent_essentially_normal' => 'nullable|boolean',
            'fields.pe_heent_abnormal_pupillary_reaction' => 'nullable|boolean',
            'fields.pe_heent_cervical_lymphadenopathy' => 'nullable|boolean',
            'fields.pe_heent_icteric_sclera' => 'nullable|boolean',
            'fields.pe_heent_pale_conjunctivae' => 'nullable|boolean',
            'fields.pe_heent_sunken_eyeballs' => 'nullable|boolean',
            'fields.pe_chest_essentially_normal' => 'nullable|boolean',
            'fields.pe_chest_asymmetric_chest_expansion' => 'nullable|boolean',
            'fields.pe_chest_wheezes' => 'nullable|boolean',
            'fields.pe_chest_crackles' => 'nullable|boolean',
            'fields.pe_chest_enlarge_axillary_lymph_nodes' => 'nullable|boolean',
            'fields.pe_chest_decreased_breath_sounds' => 'nullable|boolean',
            'fields.pe_chest_lumps_over_breast' => 'nullable|boolean',
            'fields.pe_heart_essentially_normal' => 'nullable|boolean',
            'fields.pe_heart_displaced_apex_beat' => 'nullable|boolean',
            'fields.pe_heart_heart_murmur' => 'nullable|boolean',
            'fields.pe_heart_irregular_rhythm' => 'nullable|boolean',
            'fields.pe_heart_heaves' => 'nullable|boolean',
            'fields.pe_abdomen_essentially_normal' => 'nullable|boolean',
            'fields.pe_abdomen_abdominal_rigidity' => 'nullable|boolean',
            'fields.pe_abdomen_palpable_mass' => 'nullable|boolean',
            'fields.pe_abdomen_tenderness' => 'nullable|boolean',
            'fields.pe_abdomen_hyperactive_bowel_sounds' => 'nullable|boolean',
            'fields.pe_alert_type_allergy' => 'nullable|boolean',
            'fields.pe_alert_type_disability' => 'nullable|boolean',
            'fields.pe_alert_type_drug' => 'nullable|boolean',
            'fields.pe_alert_type_handicap' => 'nullable|boolean',
            'fields.pe_alert_type_impairment' => 'nullable|boolean',
            'fields.pe_alert_type_others' => 'nullable|boolean',
            'fields.pe_alert_type_description' => 'nullable|string',

            // animal bite fields
            'fields.animal_bite_loc_abdomen' => 'nullable|boolean',
            'fields.animal_bite_loc_chest' => 'nullable|boolean',
            'fields.animal_bite_loc_forearm' => 'nullable|boolean',
            'fields.animal_bite_loc_back' => 'nullable|boolean',
            'fields.animal_bite_loc_eye' => 'nullable|boolean',
            'fields.animal_bite_loc_hand' => 'nullable|boolean',
            'fields.animal_bite_loc_buttocks' => 'nullable|boolean',
            'fields.animal_bite_loc_foot' => 'nullable|boolean',
            'fields.animal_bite_loc_head' => 'nullable|boolean',
            'fields.animal_bite_loc_neck' => 'nullable|boolean',
            'fields.animal_bite_loc_thigh' => 'nullable|boolean',
            'fields.animal_bite_loc_pelvic' => 'nullable|boolean',
            'fields.animal_bite_loc_knee' => 'nullable|boolean',
            'fields.animal_bite_loc_legs' => 'nullable|boolean',
            'fields.animal_bite_loc_mouth' => 'nullable|boolean',
            'fields.animal_bite_loc_nose' => 'nullable|boolean',
            'fields.animal_bite_loc_ears' => 'nullable|boolean',
            'fields.animal_bite_loc_others' => 'nullable|boolean',
            'fields.animal_bite_loc_others_specify' => 'nullable|string',
            'fields.animal_type_dog' => 'nullable|boolean',
            'fields.animal_type_pig' => 'nullable|boolean',
            'fields.animal_type_rat' => 'nullable|boolean',
            'fields.animal_type_snake' => 'nullable|boolean',
            'fields.animal_type_cat' => 'nullable|boolean',
            'fields.animal_type_others' => 'nullable|boolean',
            'fields.animal_type_others_specify' => 'nullable|string',
            'fields.animal_bite_description_of_bite_event' => 'nullable|string',
            'fields.animal_bite_type_of_exposure_transdermal_bite' => 'nullable|boolean',
            'fields.animal_bite_type_of_exposure_punctured_wounds' => 'nullable|boolean',
            'fields.animal_bite_type_of_exposure_lacerations' => 'nullable|boolean',
            'fields.animal_bite_type_of_exposure_avulsions' => 'nullable|boolean',
            'fields.animal_bite_type_of_exposure_scratches_abrasions_w_sponti_bleed' => 'nullable|boolean',
            'fields.animal_bite_wash_bite' => 'nullable|string',
            'fields.animal_bite_date_of_exposure' => 'nullable|date',
            'fields.animal_bite_name_of_accompanying_adult' => 'nullable|string|max:100',
            'fields.animal_bite_contact_number' => 'nullable|string|max:25',
            'fields.animal_bite_relationship_to_patient' => 'nullable|string|max:50',

            // geriatric assessment fields
            'fields.geriatric_memory_1' => 'nullable|boolean',
            'fields.geriatric_depression' => 'nullable|boolean',
            // 'fields.geriatric_depression_refer_to_physician_specify' => 'nullable|string|max:100',
            'fields.geriatric_medication' => 'nullable|boolean',
            // 'fields.geriatric_medication_refer_to_physician_specify' => 'nullable|string|max:100',
            'fields.geriatric_urinary_incontinence' => 'nullable|boolean',
            // 'fields.geriatric_urinary_incontinence_symptoms_specify' => 'nullable|string|max:100',
            // 'fields.geriatric_urinary_incontinence_counsel_specify' => 'nullable|string|max:100',
            // 'fields.geriatric_urinary_incontinence_refer_to_physician_specify' => 'nullable|string|max:100',
            'fields.geriatric_physical_function_capacity' => 'nullable|boolean',
            // 'fields.geriatric_physical_function_capacity_refer_to_physician_specify' => 'nullable|string|max:100',
            'fields.geriatric_memory_2' => 'nullable|boolean',
            // 'fields.geriatric_memory_2_refer_to_physician_specify' => 'nullable|string|max:100',
            'fields.geriatric_fall' => 'nullable|boolean',
            // 'fields.geriatric_fall_refer_to_physician_specify' => 'nullable|string|max:100',
            'fields.geriatric_risk_for_falls_seconds' => 'nullable|integer|min:0',
            'fields.geriatric_risk_for_falls_seconds_indication' => 'nullable|string',
            'fields.geriatric_risk_for_falls_inches' => 'nullable|integer|min:0',
            'fields.geriatric_risk_for_falls_inches_indication' => 'nullable|string',
            'fields.geriatric_nutrition_cm' => 'nullable|integer|min:0',
            'fields.geriatric_nutrition_cm_indication' => 'nullable|string',
            'fields.geriatric_hearing_test_r_ear_indication' => 'nullable|string',
            'fields.geriatric_hearing_test_l_ear_indication' => 'nullable|string',
            // 'fields.geriatric_hearing_test_refer_to_physician_specify' => 'nullable|string|max:100',
            'fields.geriatric_vision_test_unaided_r_eye' => 'nullable|string',
            'fields.geriatric_vision_test_unaided_l_eye' => 'nullable|string',
            'fields.geriatric_vision_test_aided_r_eye' => 'nullable|string',
            'fields.geriatric_vision_test_aided_l_eye' => 'nullable|string',
            // 'fields.geriatric_vision_refer_to_opthalmologist_specify' => 'nullable|string|max:100',
            'fields.geriatric_summary_of_findings_counsel' => 'nullable|boolean',
            'fields.geriatric_summary_of_findings_date_of_return_visit' => 'nullable|date',
            'fields.geriatric_summary_of_findings_refer_to_a_physician' => 'nullable|string',
            'fields.geriatric_summary_of_findings_date_of_referral' => 'nullable|date',
            'fields.geriatric_summary_of_findings_reason_for_referral' => 'nullable|string',
            'fields.geriatric_name_and_designation_of_the_provider' => 'nullable|string',
            'fields.geriatric_name_and_address_of_the_facility' => 'nullable|string',
            'fields.geriatric_facility_contact_details' => 'nullable|string',
            'fields.geriatric_signature_of_the_provider' => 'nullable|string',
            'fields.geriatric_referred_to' => 'nullable|string',

            // laboratory fields
            'fields.lab_req_blood_chemistry' => 'nullable|boolean',
            'fields.lab_req_fecalysis' => 'nullable|boolean',
            'fields.lab_req_mtb_genexpert' => 'nullable|boolean',
            'fields.lab_req_urinalysis' => 'nullable|boolean',
            'fields.lab_req_clinical_chemistry' => 'nullable|boolean',
            'fields.lab_req_hematology' => 'nullable|boolean',
            'fields.lab_req_serology' => 'nullable|boolean',
            'fields.lab_req_complete_blood_count' => 'nullable|boolean',
            'fields.lab_req_immunology' => 'nullable|boolean',
            'fields.lab_req_sputum_microscopy' => 'nullable|boolean',

            // imaging fields
            'fields.imaging_ecg' => 'nullable|boolean',
            'fields.imaging_xray' => 'nullable|boolean',
            'fields.imaging_mri' => 'nullable|boolean',
            'fields.imaging_ct_scan' => 'nullable|boolean',
            'fields.imaging_ultrasound' => 'nullable|boolean',
            'fields.imaging_2d_echo' => 'nullable|boolean',
            'fields.imaging_via' => 'nullable|boolean',
            'fields.imaging_pap_smear' => 'nullable|boolean',
            'fields.imaging_mammogram' => 'nullable|boolean',
            'fields.imaging_with_contrast' => 'nullable|boolean',
            'fields.diagnosis' => 'nullable|string',
            'fields.treatment_plan' => 'nullable|string',
            'fields.follow_up_date' => 'nullable|date',
            'fields.prescription' => 'nullable|string',
            'fields.refer_to_higher_facility' => 'nullable|boolean',
            'fields.remarks' => 'nullable|string',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {

            // ---- Disregard Duplication for this form ----
            // Check for duplicate risk_profile_id
            $existingPchRiskForm = PchRiskAssessmentForm::where('pch_profile_id', '=', $fields['pch_profile_id'])->first();

            if ($existingPchRiskForm) {
                return response()->json(['error' => 'Record with the same duplicate pch_profile_id found. Please recheck.'], 409);
            }

            $pchRiskForm = new PchRiskAssessmentForm();

            // Dynamically populate the model with validated data
            foreach ($fields as $key => $value) {
                if (Schema::hasColumn($pchRiskForm->getTable(), $key)) {
                    $pchRiskForm->$key = $value;
                }
            }

            // Save the data
            $pchRiskForm->save();
            return response()->json(['message' => 'Entry successfully saved.'], 200);
        } catch (Exception $e) {
            Log::error('An error has occurred in adding of PCH risk form: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    // update risk profile
    public function updatePchRiskProfile(Request $request): JsonResponse
    {
        $fields = $request->input('fields');

        // Define validation rules
        $rules = [
            // profile metadata
            'fields' => 'required|array',
            'fields.id' => 'required|integer',
            'fields.profile_id' => 'sometimes|nullable|integer',
            'fields.facility_id_updated' => 'sometimes|integer',
            'fields.encoded_by' => 'sometimes|integer',
            'fields.offline_entry' => 'sometimes|boolean',

            // profile and personal information
            'fields.prefix' => 'sometimes|nullable|string|max:15',
            'fields.lname' => 'sometimes|string|max:255',
            'fields.fname' => 'sometimes|string|max:255',
            'fields.mname' => 'sometimes|string|max:255',
            'fields.suffix' => 'sometimes|nullable|string|max:15',
            'fields.sex' => 'sometimes|string|max:10',
            'fields.dob' => 'sometimes|date',
            'fields.age' => 'sometimes|numeric|min:0|max:120',
            'fields.birth_place' => 'sometimes|string',

            'fields.civil_status' => 'sometimes|string|max:20',
            'fields.educational_attainment' => 'sometimes|string|max:50',
            'fields.employment_status' => 'sometimes|string|max:50',
            'fields.occupation' => 'sometimes|nullable|string|max:255',
            'fields.monthly_income' => 'sometimes|nullable|string|max:50',
            'fields.religion' =>  'sometimes|nullable|string|max:50',
            'fields.other_religion' => 'sometimes|nullable|string|max:255',
            'fields.indigenous' => 'sometimes|nullable|string|max:50',
            'fields.blood_type' => 'sometimes|string|max:5',
            'fields.mother_fname' => 'sometimes|string|max:255',
            'fields.mother_mname' => 'sometimes|nullable|string|max:255',
            'fields.mother_lname' => 'sometimes|string|max:255',
            'fields.mother_dob' => 'sometimes|date',

            'fields.country_id' => 'sometimes|integer',
            'fields.region_id' => 'sometimes|integer',
            'fields.province_id' => 'sometimes|integer',
            'fields.muncity_id' => 'sometimes|integer',
            'fields.barangay_id' => 'sometimes|integer',

            'fields.number_or_street_name' => 'sometimes|nullable|string',
            'fields.zip_code' => 'sometimes|integer',
            'fields.email_address' => 'sometimes|nullable|email',
            'fields.mobile_number' => 'sometimes|nullable|string|max:25',
            'fields.landline_number' => 'sometimes|nullable|string|max:25',
            'fields.family_member' => 'sometimes|nullable|string|max:50',
            'fields.dswd_nhts_member' => 'sometimes|nullable|boolean',
            'fields.four_ps_member' => 'sometimes|nullable|boolean',
            'fields.facility_household_number' => 'sometimes|nullable|string|max:50',
            'fields.family_serial_number' => 'sometimes|nullable|string|max:50',
            'fields.philhealth_member' => 'sometimes|nullable|string|max:25',
            'fields.philhealth_membership_type' => 'sometimes|nullable|string|max:50',
            'fields.philhealth_number' => 'sometimes|nullable|string|max:50',
            'fields.philhealth_category' => 'sometimes|nullable|string|max:25',
            'fields.pcb_eligible' => 'sometimes|nullable|string|max:25',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the existing RiskProfile
        $pchRiskProfile = PchRiskProfile::where('id', "=", $fields['id'])->first();

        if (!$pchRiskProfile) {
            return response()->json(['error' => 'Profile not found.'], 404);
        }

        try {
            // Recalculate age and age_bracket_id if dob is being updated
            if (!empty($fields['dob'])) {
                $fields['age'] = $this->calculateAge($fields['dob']);

                $ageBrackets = [
                    [0, 0.0164, 1],
                    [0.0165, 0.0767, 2],
                    [0.0768, 0.9167, 3],
                    [1, 4, 4],
                    [5, 9, 5],
                    [10, 14, 6],
                    [15, 19, 7],
                    [20, 24, 8],
                    [25, 29, 9],
                    [30, 34, 10],
                    [35, 39, 11],
                    [40, 44, 12],
                    [45, 49, 13],
                    [50, 54, 14],
                    [55, 59, 15],
                    [60, 64, 16],
                    [65, 69, 17],
                    [70, PHP_INT_MAX, 18]
                ];

                foreach ($ageBrackets as [$min, $max, $bracketId]) {
                    if ($fields['age'] >= $min && $fields['age'] <= $max) {
                        $fields['age_bracket_id'] = $bracketId;
                        break;
                    }
                }
            }

            // Update the RiskProfile with new data
            $pchRiskProfile->update($fields);
            return response()->json(['message' => 'Profile successfully updated.'], 200);
        } catch (Exception $e) {
            Log::error('Error updating risk profile: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Something went wrong. Please try again later'], 500);
        }
    }

    // update risk form
    public function updatePchRiskForm(Request $request): JsonResponse
    {
        $fields = $request->input('fields');

        // Define validation rules
        $rules = [
            'fields' => 'required|array',
            'fields.pch_profile_id' => 'required|integer',
            'fields.nature_of_visit' => 'sometimes|string|max:255',
            'fields.nature_of_visit_duration' => 'sometimes|string|max:50',
            'fields.type_of_consultation' => 'sometimes|string|max:255',

            // vital signs
            'fields.vit_bp_1st_reading' => 'sometimes|string|max:10',
            'fields.vit_bp_2nd_reading' => 'sometimes|string|max:10',
            'fields.vit_bp_3rd_reading' => 'sometimes|string|max:10',
            'fields.vit_bp_average_2nd_to_3rd_reading' => 'sometimes|string|max:10',
            'fields.vit_oxygen_saturation' => 'sometimes|string',
            'fields.vit_heart_rate_or_pulse_rate' => 'sometimes|string',
            'fields.vit_is_normal_rate' => 'sometimes|boolean',
            'fields.vit_is_regular_rhythm' => 'sometimes|boolean',
            'fields.vit_respiratory_rate' => 'sometimes|numeric|min:0',
            'fields.vit_temperature' => 'sometimes|numeric|min:0',
            'fields.vit_weight' => 'sometimes|numeric|min:0',
            'fields.vit_height' => 'sometimes|numeric|min:0',
            'fields.vit_waist_circumference' => 'sometimes|numeric|min:0',
            'fields.vit_bmi' => 'sometimes|numeric|min:0',
            'fields.vit_chief_complaint' => 'sometimes|string',
            'fields.vit_history_of_present_illness_and_remarks' => 'sometimes|nullable|string',

            // comorbidities 
            'fields.comorb_hist_heart_attack_stroke_or_kidney_problems' => 'sometimes|nullable|boolean',
            'fields.comorb_hist_heart_attack_stroke_1st_degree_relatives' => 'sometimes|nullable|boolean',
            'fields.comorb_hypertension' => 'sometimes|nullable|boolean',
            'fields.comorb_hypertension_if_yes_taking_medications' => 'sometimes|nullable|boolean',
            'fields.comorb_diabetes_mellitus' => 'sometimes|nullable|boolean',
            'fields.comorb_diabetes_mellitus_if_yes_taking_medications' => 'sometimes|nullable|boolean',
            'fields.comorb_high_cholesterol' => 'sometimes|nullable|boolean',
            'fields.comorb_high_cholesterol_if_yes_taking_medications' => 'sometimes|nullable|boolean',
            'fields.comorb_tuberculosis' => 'sometimes|nullable|boolean',

            // lifestyle
            'fields.lifestyle_current_smoker' => 'sometimes|nullable|boolean',
            'fields.lifestyle_current_smoker_if_yes_tobacco_products' => 'sometimes|nullable|boolean',
            'fields.lifestyle_current_smoker_if_yes_vaporized_products' => 'sometimes|nullable|boolean',
            'fields.lifestyle_current_smoker_if_yes_both' => 'sometimes|nullable|boolean',
            'fields.lifestyle_binge_drinking_past_year' => 'sometimes|nullable|boolean',
            'fields.lifestyle_moderate_physical_activity_throughout_the_week' => 'sometimes|nullable|boolean',
            'fields.lifestyle_intake_of_fruits_and_veg_below_five_portions' => 'sometimes|nullable|boolean',
            'fields.lifestyle_non_laboratory_cvd_risk_percentage_color_code' => 'sometimes|nullable|string|max:10',

            // management pt. I
            'fields.mngt_counseling_on_healthy_diet' => 'sometimes|nullable|boolean',
            'fields.mngt_counseling_on_physical_activity' => 'sometimes|nullable|boolean',
            'fields.mngt_counseling_on_referred_for_bti' => 'sometimes|nullable|boolean',
            'fields.mngt_harmful_use_of_alcohol' => 'sometimes|nullable|boolean',
            'fields.mngt_referred_to_pcf_for_risk_screening' => 'sometimes|nullable|boolean',
            'fields.date_next_risk_assessment' => 'sometimes|nullable|date',
            'fields.assessed_by' => 'sometimes|nullable|string|max:100',
            'fields.verified_by' => 'sometimes|nullable|string|max:100',

            // immunization records
            'fields.imm_record_child_none' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_bcg' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_opv' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_polio_1' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_polio_2' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_polio_3' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_hep_b1' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_hep_b2' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_hep_b3' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_dpt1' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_dpt2' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_dpt3' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_hib1' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_hib2' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_hib3' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_covid19' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_measles_mcv1_mr' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_measles_mcv1_mmr' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_measles_mcv2_mr' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_measles_mcv2_mmr' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_booster' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_others' => 'sometimes|nullable|boolean',
            'fields.imm_record_child_others_please_specify' => 'sometimes|nullable|string',
            'fields.imm_record_schoolage_none' => 'sometimes|nullable|boolean',
            'fields.imm_record_schoolage_mr' => 'sometimes|nullable|boolean',
            'fields.imm_record_schoolage_td' => 'sometimes|nullable|boolean',
            'fields.imm_record_schoolage_hpv' => 'sometimes|nullable|boolean',
            'fields.imm_record_schoolage_others' => 'sometimes|nullable|boolean',
            'fields.imm_record_schoolage_others_please_specify' => 'sometimes|nullable|string',
            'fields.imm_record_pregnant_none' => 'sometimes|nullable|boolean',
            'fields.imm_record_pregnant_tetanus_toxoid' => 'sometimes|nullable|boolean',
            'fields.imm_record_pregnant_covid19' => 'sometimes|nullable|boolean',
            'fields.imm_record_pregnant_flu' => 'sometimes|nullable|boolean',
            'fields.imm_record_pregnant_others' => 'sometimes|nullable|boolean',
            'fields.imm_record_pregnant_others_please_specify' => 'sometimes|nullable|string',
            'fields.imm_record_adult_elderly_none' => 'sometimes|nullable|boolean',
            'fields.imm_record_adult_elderly_flu' => 'sometimes|nullable|boolean',
            'fields.imm_record_adult_elderly_pneumococcal' => 'sometimes|nullable|boolean',
            'fields.imm_record_adult_elderly_covid19' => 'sometimes|nullable|boolean',
            'fields.imm_record_adult_elderly_hpv' => 'sometimes|nullable|boolean',
            'fields.imm_record_adult_elderly_others' => 'sometimes|nullable|boolean',
            'fields.imm_record_adult_elderly_others_specify' => 'sometimes|nullable|string',

            // menstrual history
            'fields.menst_hist_menarche' => 'sometimes|nullable|boolean',
            'fields.menst_hist_age_menarche' => 'sometimes|nullable|numeric|min:0',
            'fields.menst_hist_menopause' => 'sometimes|nullable|boolean',
            'fields.menst_hist_age_menopause' => 'sometimes|nullable|numeric|min:0',
            'fields.menst_hist_no_of_pads_used_per_day' => 'sometimes|nullable|integer|min:0',
            'fields.menst_hist_interval_cycle_of_menstruation_in_days' => 'sometimes|nullable|numeric|min:0',
            'fields.menst_hist_birth_control_method_used' => 'sometimes|nullable|string',
            'fields.menst_hist_onset_of_sexual_intercourse_age' => 'sometimes|nullable|numeric|min:0',

            // pregnancy history
            'fields.preg_hist_gravidity' => 'sometimes|nullable|integer|min:0',
            'fields.preg_hist_parity' => 'sometimes|nullable|integer|min:0',
            'fields.preg_hist_no_of_full_term_pregnancy' => 'sometimes|nullable|integer|min:0',
            'fields.preg_hist_no_of_premature_pregnancy' => 'sometimes|nullable|integer|min:0',
            'fields.preg_hist_no_of_abortion' => 'sometimes|nullable|integer|min:0',
            'fields.preg_hist_no_of_living_children' => 'sometimes|nullable|integer|min:0',
            'fields.preg_hist_pre_eclampsia' => 'sometimes|nullable|boolean',
            'fields.preg_hist_with_access_to_family_planning' => 'sometimes|nullable|boolean',

            // family history
            'fields.fam_hist_asthma' => 'sometimes|nullable|boolean',
            'fields.fam_hist_copd' => 'sometimes|nullable|boolean',
            'fields.fam_hist_hypertension' => 'sometimes|nullable|boolean',
            'fields.fam_hist_tuberculosis' => 'sometimes|nullable|boolean',
            'fields.fam_hist_cancer' => 'sometimes|nullable|boolean',
            'fields.fam_hist_diabetes' => 'sometimes|nullable|boolean',
            'fields.fam_hist_kidney_disease' => 'sometimes|nullable|boolean',
            'fields.fam_hist_peripheral_vascular_diseases' => 'sometimes|nullable|boolean',
            'fields.fam_hist_mental_disorders' => 'sometimes|nullable|boolean',
            'fields.fam_hist_others' => 'sometimes|nullable|boolean',
            'fields.fam_hist_others_please_specify' => 'sometimes|nullable|string',

            // social history
            'fields.soc_hist_is_patient_illicit_drug_user' => 'sometimes|nullable|boolean',
            'fields.soc_hist_if_yes_indicate_type_of_illegal_drug_used' => 'sometimes|nullable|string|max:255',
            'fields.soc_hist_is_patient_sexually_active' => 'sometimes|nullable|boolean',
            'fields.soc_hist_sexual_activity_no_of_partner' => 'sometimes|nullable|string|max:10',
            'fields.soc_hist_sexual_activity_with_protection' => 'sometimes|nullable|boolean',

            // risk assessment pt. II
            'fields.risk_assessment_established_angina_pectoris' => 'sometimes|nullable|boolean',
            'fields.risk_assessment_with_left_ventricular_hypertrophy' => 'sometimes|nullable|boolean',
            'fields.risk_assessment_wo_est_cvd_with_8mmol_of_cholesterol' => 'sometimes|nullable|boolean',
            'fields.risk_assessment_wo_est_cvd_who_have_persistent_raised_bp' => 'sometimes|nullable|boolean',
            'fields.risk_assessment_with_type1_or_type2_diabetes' => 'sometimes|nullable|boolean',
            'fields.risk_assessment_with_known_renal_failure_or_impairment' => 'sometimes|nullable|boolean',
            'fields.bp_2nd_encounter_1st_reading' => 'sometimes|nullable|string|max:10',
            'fields.bp_2nd_encounter_2nd_reading' => 'sometimes|nullable|string|max:10',
            'fields.bp_2nd_encounter_3rd_reading' => 'sometimes|nullable|string|max:10',
            'fields.bp_2nd_encounter_average_2nd_to_3rd_reading' => 'sometimes|nullable|string|max:10',
            'fields.individual_have_all_classic_symptoms_marked' => 'sometimes|nullable|boolean',
            'fields.urine_ketones_result' => 'sometimes|nullable|numeric|min:0',
            'fields.urine_ketones_result_date_taken' => 'sometimes|nullable|date',
            'fields.total_cholesterol_result' => 'sometimes|nullable|numeric|min:0',
            'fields.total_cholesterol_result_date_taken' => 'sometimes|nullable|date',
            'fields.random_plasma_glucose_result' => 'sometimes|nullable|numeric|min:0',
            'fields.random_plasma_glucose_result_date_taken' => 'sometimes|nullable|date',
            'fields.fasting_plasma_glucose_result' => 'sometimes|nullable|numeric|min:0',
            'fields.fasting_plasma_glucose_result_date_taken' => 'sometimes|nullable|date',
            'fields.confirmatory_fpg_result' => 'sometimes|nullable|numeric|min:0',
            'fields.confirmatory_fpg_result_date_taken' => 'sometimes|nullable|date',
            'fields.basic_labs_for_confirmed_hypertensives_12l_ecg' => 'sometimes|nullable|boolean',
            'fields.basic_labs_for_confirmed_hypertensives_blood_test' => 'sometimes|nullable|boolean',
            'fields.basic_labs_for_confirmed_hypertensives_dipstick' => 'sometimes|nullable|boolean',
            'fields.laboratory_cvd_risk_percentage_color_code' => 'sometimes|nullable|string|max:10',

            // management pt. II
            'fields.mngt_2_counseling_on_healthy_diet' => 'sometimes|nullable|boolean',
            'fields.mngt_2_counseling_on_physical_activity' => 'sometimes|nullable|boolean',
            'fields.mngt_2_counseling_on_tobacco_cessation' => 'sometimes|nullable|boolean',
            'fields.mngt_2_counseling_on_harmful_use_of_alcohol' => 'sometimes|nullable|boolean',
            'fields.medications_anti_hypertension' => 'sometimes|nullable|boolean',
            'fields.medications_yes_anti_hypertension_out_of_pkt' => 'sometimes|nullable|boolean',
            'fields.medications_yes_anti_hypertension_both_pbf_and_oop' => 'sometimes|nullable|boolean',
            'fields.medications_yes_anti_hypertension_provided' => 'sometimes|nullable|boolean',
            'fields.medications_oral_hypoglycemic_agents_or_insulin' => 'sometimes|nullable|boolean',
            'fields.medications_yes_oral_hypoglycemic_agents_or_insulin_provided' => 'sometimes|nullable|boolean',
            'fields.medications_yes_oral_hypoglycemic_agents_or_insulin_out_of_pkt' => 'sometimes|nullable|boolean',
            'fields.medications_oral_hypoglycemic_agents_or_insulin_both_pbf_and_oop' => 'sometimes|nullable|boolean',
            'fields.risk_assessment_ii_date_of_follow_up' => 'sometimes|nullable|date',
            'fields.physicians_name_risk_assessment_pt_ii' => 'sometimes|nullable|string|max:255',

            // physical exam
            'fields.pe_skin_extremities_essentially_normal' => 'sometimes|nullable|boolean',
            'fields.pe_skin_extremities_clubbing' => 'sometimes|nullable|boolean',
            'fields.pe_skin_extremities_cold_clammy' => 'sometimes|nullable|boolean',
            'fields.pe_skin_extremities_pale_nailbeds' => 'sometimes|nullable|boolean',
            'fields.pe_skin_extremities_cyanosis' => 'sometimes|nullable|boolean',
            'fields.pe_skin_extremities_poor_skin_turgor' => 'sometimes|nullable|boolean',
            'fields.pe_skin_extremities_edema' => 'sometimes|nullable|boolean',
            'fields.pe_skin_extremities_itching' => 'sometimes|nullable|boolean',
            'fields.pe_skin_extremities_erythema' => 'sometimes|nullable|boolean',
            'fields.pe_skin_extremities_lesions' => 'sometimes|nullable|boolean',
            'fields.pe_heent_essentially_normal' => 'sometimes|nullable|boolean',
            'fields.pe_heent_abnormal_pupillary_reaction' => 'sometimes|nullable|boolean',
            'fields.pe_heent_cervical_lymphadenopathy' => 'sometimes|nullable|boolean',
            'fields.pe_heent_icteric_sclera' => 'sometimes|nullable|boolean',
            'fields.pe_heent_pale_conjunctivae' => 'sometimes|nullable|boolean',
            'fields.pe_heent_sunken_eyeballs' => 'sometimes|nullable|boolean',
            'fields.pe_chest_essentially_normal' => 'sometimes|nullable|boolean',
            'fields.pe_chest_asymmetric_chest_expansion' => 'sometimes|nullable|boolean',
            'fields.pe_chest_wheezes' => 'sometimes|nullable|boolean',
            'fields.pe_chest_crackles' => 'sometimes|nullable|boolean',
            'fields.pe_chest_enlarge_axillary_lymph_nodes' => 'sometimes|nullable|boolean',
            'fields.pe_chest_decreased_breath_sounds' => 'sometimes|nullable|boolean',
            'fields.pe_chest_lumps_over_breast' => 'sometimes|nullable|boolean',
            'fields.pe_heart_essentially_normal' => 'sometimes|nullable|boolean',
            'fields.pe_heart_displaced_apex_beat' => 'sometimes|nullable|boolean',
            'fields.pe_heart_heart_murmur' => 'sometimes|nullable|boolean',
            'fields.pe_heart_irregular_rhythm' => 'sometimes|nullable|boolean',
            'fields.pe_heart_heaves' => 'sometimes|nullable|boolean',
            'fields.pe_abdomen_essentially_normal' => 'sometimes|nullable|boolean',
            'fields.pe_abdomen_abdominal_rigidity' => 'sometimes|nullable|boolean',
            'fields.pe_abdomen_palpable_mass' => 'sometimes|nullable|boolean',
            'fields.pe_abdomen_tenderness' => 'sometimes|nullable|boolean',
            'fields.pe_abdomen_hyperactive_bowel_sounds' => 'sometimes|nullable|boolean',
            'fields.pe_alert_type_allergy' => 'sometimes|nullable|boolean',
            'fields.pe_alert_type_disability' => 'sometimes|nullable|boolean',
            'fields.pe_alert_type_drug' => 'sometimes|nullable|boolean',
            'fields.pe_alert_type_handicap' => 'sometimes|nullable|boolean',
            'fields.pe_alert_type_impairment' => 'sometimes|nullable|boolean',
            'fields.pe_alert_type_others' => 'sometimes|nullable|boolean',
            'fields.pe_alert_type_description' => 'sometimes|nullable|string',

            // animal bite
            'fields.animal_bite_loc_abdomen' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_chest' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_forearm' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_back' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_eye' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_hand' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_buttocks' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_foot' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_head' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_neck' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_thigh' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_pelvic' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_knee' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_legs' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_mouth' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_nose' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_ears' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_others' => 'sometimes|nullable|boolean',
            'fields.animal_bite_loc_others_specify' => 'sometimes|nullable|string',
            'fields.animal_type_dog' => 'sometimes|nullable|boolean',
            'fields.animal_type_pig' => 'sometimes|nullable|boolean',
            'fields.animal_type_rat' => 'sometimes|nullable|boolean',
            'fields.animal_type_snake' => 'sometimes|nullable|boolean',
            'fields.animal_type_cat' => 'sometimes|nullable|boolean',
            'fields.animal_type_others' => 'sometimes|nullable|boolean',
            'fields.animal_type_others_specify' => 'sometimes|nullable|string',
            'fields.animal_bite_description_of_bite_event' => 'sometimes|nullable|string',
            'fields.animal_bite_type_of_exposure_transdermal_bite' => 'sometimes|nullable|boolean',
            'fields.animal_bite_type_of_exposure_punctured_wounds' => 'sometimes|nullable|boolean',
            'fields.animal_bite_type_of_exposure_lacerations' => 'sometimes|nullable|boolean',
            'fields.animal_bite_type_of_exposure_avulsions' => 'sometimes|nullable|boolean',
            'fields.animal_bite_type_of_exposure_scratches_abrasions_w_sponti_bleed' => 'sometimes|nullable|boolean',
            'fields.animal_bite_wash_bite' => 'sometimes|nullable|string|max:255',
            'fields.animal_bite_date_of_exposure' => 'sometimes|nullable|date',
            'fields.animal_bite_name_of_accompanying_adult' => 'sometimes|nullable|string|max:100',
            'fields.animal_bite_contact_number' => 'sometimes|nullable|string|max:25',
            'fields.animal_bite_relationship_to_patient' => 'sometimes|nullable|string|max:50',

            // geriatric assessment
            'fields.geriatric_memory_1' => 'sometimes|nullable|boolean',
            'fields.geriatric_depression' => 'sometimes|nullable|boolean',
            // 'fields.geriatric_depression_refer_to_physician_specify' => 'sometimes|nullable|string|max:100',
            'fields.geriatric_medication' => 'sometimes|nullable|boolean',
            // 'fields.geriatric_medication_refer_to_physician_specify' => 'sometimes|nullable|string|max:100',
            'fields.geriatric_urinary_incontinence' => 'sometimes|nullable|boolean',
            // 'fields.geriatric_urinary_incontinence_symptoms_specify' => 'sometimes|nullable|string|max:100',
            // 'fields.geriatric_urinary_incontinence_counsel_specify' => 'sometimes|nullable|string|max:100',
            // 'fields.geriatric_urinary_incontinence_refer_to_physician_specify' => 'sometimes|nullable|string|max:100',
            'fields.geriatric_physical_function_capacity' => 'sometimes|nullable|boolean',
            // 'fields.geriatric_physical_function_capacity_refer_to_physician_specify' => 'sometimes|nullable|string|max:100',
            'fields.geriatric_memory_2' => 'sometimes|nullable|boolean',
            // 'fields.geriatric_memory_2_refer_to_physician_specify' => 'sometimes|nullable|string|max:100',
            'fields.geriatric_fall' => 'sometimes|nullable|boolean',
            // 'fields.geriatric_fall_refer_to_physician_specify' => 'sometimes|nullable|string|max:100',
            'fields.geriatric_risk_for_falls_seconds' => 'sometimes|nullable|integer|min:0',
            'fields.geriatric_risk_for_falls_seconds_indication' => 'sometimes|nullable|string',
            'fields.geriatric_risk_for_falls_inches' => 'sometimes|nullable|integer|min:0',
            'fields.geriatric_risk_for_falls_inches_indication' => 'sometimes|nullable|string',
            'fields.geriatric_nutrition_cm' => 'sometimes|nullable|integer|min:0',
            'fields.geriatric_nutrition_cm_indication' => 'sometimes|nullable|string',
            'fields.geriatric_hearing_test_r_ear_indication' => 'sometimes|nullable|string',
            'fields.geriatric_hearing_test_l_ear_indication' => 'sometimes|nullable|string',
            // 'fields.geriatric_hearing_test_refer_to_physician_specify' => 'sometimes|nullable|string|max:100',
            'fields.geriatric_vision_test_unaided_r_eye' => 'sometimes|nullable|string',
            'fields.geriatric_vision_test_unaided_l_eye' => 'sometimes|nullable|string',
            'fields.geriatric_vision_test_aided_r_eye' => 'sometimes|nullable|string',
            'fields.geriatric_vision_test_aided_l_eye' => 'sometimes|nullable|string',
            // 'fields.geriatric_vision_refer_to_opthalmologist_specify' => 'sometimes|nullable|string|max:100',
            'fields.geriatric_summary_of_findings_counsel' => 'sometimes|nullable|boolean',
            'fields.geriatric_summary_of_findings_date_of_return_visit' => 'sometimes|nullable|date',
            'fields.geriatric_summary_of_findings_refer_to_a_physician' => 'sometimes|nullable|string',
            'fields.geriatric_summary_of_findings_date_of_referral' => 'sometimes|nullable|date',
            'fields.geriatric_summary_of_findings_reason_for_referral' => 'sometimes|nullable|string',
            'fields.geriatric_name_and_designation_of_the_provider' => 'sometimes|nullable|string',
            'fields.geriatric_name_and_address_of_the_facility' => 'sometimes|nullable|string',
            'fields.geriatric_facility_contact_details' => 'sometimes|nullable|string',
            'fields.geriatric_signature_of_the_provider' => 'sometimes|nullable|string',
            'fields.geriatric_referred_to' => 'sometimes|nullable|string',

            // laboratory requests
            'fields.lab_req_blood_chemistry' => 'sometimes|nullable|boolean',
            'fields.lab_req_fecalysis' => 'sometimes|nullable|boolean',
            'fields.lab_req_mtb_genexpert' => 'sometimes|nullable|boolean',
            'fields.lab_req_urinalysis' => 'sometimes|nullable|boolean',
            'fields.lab_req_clinical_chemistry' => 'sometimes|nullable|boolean',
            'fields.lab_req_hematology' => 'sometimes|nullable|boolean',
            'fields.lab_req_serology' => 'sometimes|nullable|boolean',
            'fields.lab_req_complete_blood_count' => 'sometimes|nullable|boolean',
            'fields.lab_req_immunology' => 'sometimes|nullable|boolean',
            'fields.lab_req_sputum_microscopy' => 'sometimes|nullable|boolean',

            // imaging
            'fields.imaging_ecg' => 'sometimes|nullable|boolean',
            'fields.imaging_xray' => 'sometimes|nullable|boolean',
            'fields.imaging_mri' => 'sometimes|nullable|boolean',
            'fields.imaging_ct_scan' => 'sometimes|nullable|boolean',
            'fields.imaging_ultrasound' => 'sometimes|nullable|boolean',
            'fields.imaging_2d_echo' => 'sometimes|nullable|boolean',
            'fields.imaging_via' => 'sometimes|nullable|boolean',
            'fields.imaging_pap_smear' => 'sometimes|nullable|boolean',
            'fields.imaging_mammogram' => 'sometimes|nullable|boolean',
            'fields.imaging_with_contrast' => 'sometimes|nullable|boolean',
            'fields.diagnosis' => 'sometimes|nullable|string',
            'fields.treatment_plan' => 'sometimes|nullable|string',
            'fields.follow_up_date' => 'sometimes|nullable|date',
            'fields.prescription' => 'sometimes|nullable|string',
            'fields.refer_to_higher_facility' => 'sometimes|nullable|boolean',
            'fields.remarks' => 'sometimes|nullable|string',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the existing RiskFormAssessment
        $pchRiskAssessmentform = PchRiskAssessmentForm::where('pch_profile_id', "=", $fields['pch_profile_id'])->first();

        if (!$pchRiskAssessmentform) {
            return response()->json(['error' => 'Risk form not found.'], 404);
        }

        try {
            // Update the RiskFormAssessment with new data
            $pchRiskAssessmentform->update($fields);
            return response()->json(['message' => 'Risk form successfully updated.'], 200);
        } catch (Exception $e) {
            Log::error('Error updating risk form: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Something went wrong. Please try again later'], 500);
        }
    }

    // delete risk profile
    public function deletePchRiskProfile(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'fields.id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $id = $request->input('fields.id');

        try {
            $pchRiskProfile = PchRiskProfile::where('id', "=", $id)->first();

            if (!$pchRiskProfile) {
                return response()->json(['error' => 'Risk profile not found.'], 404);
            }

            $pchRiskProfile->delete();
            return response()->json(['message' => 'Risk profile successfully deleted.'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting risk profile: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    // delete risk form
    public function deletePchRiskForm(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'fields.pch_profile_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $id = $request->input('fields.pch_profile_id');

        try {
            $riskForm = PchRiskAssessmentForm::where('pch_profile_id', '=', $id)->first();

            if (!$riskForm) {
                return response()->json(['error' => 'PCH Risk form not found.'], 404);
            }

            $riskForm->delete();
            return response()->json(['message' => 'PCH Risk form successfully deleted.'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting PCH risk form: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Something went wrong. Please try again later'], 500);
        }
    }
}
