<?php

namespace App\Http\Controllers\TsekapV2\Forms\PchRiskAssessmentForm;

use Exception;
use App\Models\User;
use App\Models\TsekapV2\UserHealthFacility;
use App\Models\TsekapV2\Facilities;
use App\Models\TsekapV2\Forms\PchRiskAssessment\PchRiskAssessmentForm;
use App\Models\TsekapV2\Forms\PchRiskAssessment\PchRiskProfile;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DataController extends Controller
{
    private function getAuthenticatedUser($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->getAttribute('verified') !== 1) {
            Log::error('Denied access to (PCH Risk Assessment, DataController) for:' . " " . $queryUser->getAttribute('id'));
            return response()->json(['error' => 'User not found'], 404);
        }

        return $queryUser;
    }

    // for users with privilege of 1,3,and 10
    private function getAuthenticatedAdmin($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        // do not authorize update unless 1, 3, 10
        if ((!$queryUser || !in_array($queryUser->getAttribute('user_priv'), [1, 3, 10])) || ($queryUser->getAttribute('verified') !== 1)) {
            Log::error('Denied administrative access to (PCH Risk Assessment, DataController) for: ' + $queryUser->getAttribute('id'));
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    private function getHealthFacilityForUser($user)
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
    public function retrievePchRiskProfileWithoutFacility(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username')); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

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
            'pch_risk_assessment_tool_profile.id',
            'pch_risk_assessment_tool_profile.profile_id',
            'pch_risk_assessment_tool_profile.facility_id_updated',
            'pch_risk_assessment_tool_profile.encoded_by',
            'pch_risk_assessment_tool_profile.offline_entry',
            'pch_risk_assessment_tool_profile.prefix',
            'pch_risk_assessment_tool_profile.lname',
            'pch_risk_assessment_tool_profile.fname',
            'pch_risk_assessment_tool_profile.mname',
            'pch_risk_assessment_tool_profile.suffix',
            'pch_risk_assessment_tool_profile.sex',
            'pch_risk_assessment_tool_profile.dob',
            'pch_risk_assessment_tool_profile.birth_place',
            'pch_risk_assessment_tool_profile.civil_status',
            'pch_risk_assessment_tool_profile.educational_attainment',
            'pch_risk_assessment_tool_profile.employment_status',
            'pch_risk_assessment_tool_profile.occupation',
            'pch_risk_assessment_tool_profile.religion',
            'pch_risk_assessment_tool_profile.other_religion',
            'pch_risk_assessment_tool_profile.indigenous',
            'pch_risk_assessment_tool_profile.blood_type',
            'pch_risk_assessment_tool_profile.mother_fname',
            'pch_risk_assessment_tool_profile.mother_mname',
            'pch_risk_assessment_tool_profile.mother_lname',
            'pch_risk_assessment_tool_profile.mother_dob',
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
            'pch_risk_assessment_tool_profile.family_member',
            'pch_risk_assessment_tool_profile.dswd_nhts_member',
            'pch_risk_assessment_tool_profile.four_ps_member',
            'pch_risk_assessment_tool_profile.facility_household_number',
            'pch_risk_assessment_tool_profile.family_serial_number',
            'pch_risk_assessment_tool_profile.philhealth_member',
            'pch_risk_assessment_tool_profile.philhealth_number',
            'pch_risk_assessment_tool_profile.philhealth_membership_type',
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
    public function retrievePchRiskProfileByFacility(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username')); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

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
            'pch_risk_assessment_tool_profile.prefix',
            'pch_risk_assessment_tool_profile.lname',
            'pch_risk_assessment_tool_profile.fname',
            'pch_risk_assessment_tool_profile.mname',
            'pch_risk_assessment_tool_profile.suffix',
            'pch_risk_assessment_tool_profile.sex',
            'pch_risk_assessment_tool_profile.dob',
            'pch_risk_assessment_tool_profile.birth_place',
            'pch_risk_assessment_tool_profile.civil_status',
            'pch_risk_assessment_tool_profile.educational_attainment',
            'pch_risk_assessment_tool_profile.employment_status',
            'pch_risk_assessment_tool_profile.occupation',
            'pch_risk_assessment_tool_profile.religion',
            'pch_risk_assessment_tool_profile.other_religion',
            'pch_risk_assessment_tool_profile.indigenous',
            'pch_risk_assessment_tool_profile.blood_type',
            'pch_risk_assessment_tool_profile.mother_fname',
            'pch_risk_assessment_tool_profile.mother_mname',
            'pch_risk_assessment_tool_profile.mother_lname',
            'pch_risk_assessment_tool_profile.mother_dob',
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

    public function retrievePchRiskAssessmentForm(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username')); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

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
            'dietary_fiber_intake_2_to_3_servings_of_fruits_daily',
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
            'diagnosed_as_having_diabetes',
            'symptoms_polyphagia',
            'symptoms_polydipsia',
            'symptoms_polyuria',
            'has_raised_blood_glucose',
            'presence_of_urine_ketones_newly_diagnosed',
            'urine_ketones',
            'urine_ketones_date_taken',
            'fbs',
            'rbs',
            'fbs_rbs_date_taken',
            'has_raised_blood_lipid',
            'total_cholesterol',
            'total_cholesterol_date_taken',
            'management',
            'lifestyle_modification',
            'medications',
            'presence_of_urine_protein',
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
        );

        if ($id) {
            $query->where('pch_profile_id', "=", $id);
        }
        return response()->json($query->simplePaginate(30), 200);
    }

    private function calculateAge($dob, $asOfDate = null)
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

    public function addPchRiskProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username'));
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

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
            'fields.age' => 'sometimes|numeric|min:0|max:120',
            'fields.birth_place' => 'required|string',

            'fields.civil_status' => 'required|string|max:20',
            'fields.educational_attainment' => 'required|string|max:50',
            'fields.employment_status' => 'required|string|max:50',
            'fields.occupation' => 'sometimes|nullable|string|max:255',
            'fields.religion' =>  'sometimes|nullable|string|max:50',
            'fields.other_religion' => 'sometimes|nullable|string|max:255',
            'fields.indigenous' => 'sometimes|nullable|string|max:50',
            'fields.blood_type' => 'required|string|max:5',
            'fields.mother_fname' => 'required|string|max:255',
            'fields.mother_mname' => 'sometimes|nullable|string|max:255',
            'fields.mother_lname' => 'required|string|max:255',
            'fields.mother_dob' => 'required|date',

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
            'fields.family_member' => 'sometimes|nullable|string|max:50',
            'fields.dswd_nhts_member' => 'sometimes|nullable|string|max:15',
            'fields.four_ps_member' => 'sometimes|nullable|string|max:15',
            'fields.facility_household_number' => 'sometimes|nullable|string|max:50',
            'fields.family_serial_number' => 'sometimes|nullable|string|max:50',
            'fields.philhealth_member' => 'sometimes|nullable|string|max:25',
            'fields.philhealth_number' => 'sometimes|nullable|string|max:50',
            'fields.philhealth_membership_type' => 'sometimes|nullable|string|max:25',
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
            $pchRiskProfile = new PchRiskProfile($fields);
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

    public function addPchRiskForm(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username')); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        // Define validation rules
        $rules = [
            'fields' => 'required|array',
            'fields.pch_profile_id' => 'required|integer',
            'fields.nature_of_visit' => 'required|string|max:255',
            'fields.nature_of_visit_duration' => 'required|string|max:255',
            'fields.type_of_consultation' => 'required|string|max:255',

            'fields.vit_bp_systolic' => 'required|integer|min:0|max:300',
            'fields.vit_bp_diastolic' => 'required|integer|min:0|max:200',
            'fields.vit_oxygen_saturation' => 'required|integer|min:0|max:100',
            'fields.vit_heart_rate_or_pulse_rate' => 'required|integer|min:0',
            'fields.vit_is_normal_rate' => 'required|string|max:50',
            'fields.vit_is_regular_rhythm' => 'required|string|max:50',
            'fields.vit_respiratory_rate' => 'required|integer|min:0',
            'fields.vit_temperature' => 'required|numeric',
            'fields.vit_weight' => 'required|numeric',
            'fields.vit_height' => 'required|numeric',
            'fields.vit_bmi' => 'required|numeric',
            'fields.vit_chief_complaint' => 'required|string|max:255',
            'fields.vit_history_of_present_illness_and_remarks' => 'sometimes|nullable|string',

            'fields.pe_skin_extremities_description' => 'sometimes|nullable|string|max:50',
            'fields.pe_heent_description' => 'sometimes|nullable|string|max:50',
            'fields.pe_chest_description' => 'sometimes|nullable|string|max:50',
            'fields.pe_heart' => 'sometimes|nullable|string|max:50',
            'fields.pe_abdomen' => 'sometimes|nullable|string|max:50',
            'fields.pe_alert_type' => 'sometimes|nullable|string|max:50',
            'fields.pe_description' => 'sometimes|nullable|string|max:50',

            'fields.ab_anatomical_location' => 'sometimes|nullable|string|max:50',
            'fields.ab_anatomical_location_others_specify' => 'sometimes|nullable|string|max:255',
            'fields.ab_animal_type' => 'sometimes|nullable|string|max:50',
            'fields.ab_animal_type_others_specify' => 'sometimes|nullable|string|max:255',
            'fields.ab_description_of_event' => 'sometimes|nullable|string',
            'fields.ab_type_of_exposure' => 'sometimes|nullable|string|max:50',
            'fields.ab_wash_bite' => 'sometimes|nullable|string|max:50',
            'fields.ab_date_of_exposure' => 'sometimes|nullable|date',

            'fields.comorbidities' => 'sometimes|nullable|string|max:50',
            'fields.comorbidities_others' => 'sometimes|nullable|string|max:255',

            'fields.ph_immunization_record_child' => 'sometimes|nullable|string|max:255',
            'fields.ph_immunization_record_child_others_specify' => 'sometimes|nullable|string|max:255',
            'fields.ph_immunization_record_pregnant' => 'sometimes|nullable|string|max:255',
            'fields.ph_immunization_record_pregnant_others_specify' => 'sometimes|nullable|string|max:255',
            'fields.ph_immunization_record_adult_and_elderly' => 'sometimes|nullable|string|max:255',
            'fields.ph_immunization_record_adult_and_elderly_others_specify' => 'sometimes|nullable|string|max:255',

            'fields.wr_menarche' => 'sometimes|nullable|string|max:15',
            'fields.wr_menarche_age' => 'sometimes|nullable|integer|min:0',
            'fields.wr_menopause' => 'sometimes|nullable|string|max:15',
            'fields.wr_menopause_age' => 'sometimes|nullable|integer|min:0',
            'fields.wr_no_of_pads_used_per_day' => 'sometimes|nullable|integer|min:0',
            'fields.wr_interval_cycle_of_menstruation_in_days' => 'sometimes|nullable|integer|min:0',
            'fields.wr_birth_control_method_used' => 'sometimes|nullable|string',
            'fields.wr_onset_of_sexual_intercourse_age' => 'sometimes|nullable|integer|min:0',
            'fields.wr_pregnancy_history_gravidity' => 'sometimes|nullable|integer|min:0',
            'fields.wr_pregnancy_history_parity' => 'sometimes|nullable|integer|min:0',
            'fields.wr_pre_eclampsia' => 'sometimes|nullable|string|max:15',
            'fields.wr_with_access_to_family_planning_counseling' => 'sometimes|nullable|string|max:15',
            'fields.wr_pregnancy_history_num_of_full_term_pregnancy' => 'sometimes|nullable|integer|min:0',
            'fields.wr_pregnancy_history_num_of_premature_pregnancy' => 'sometimes|nullable|integer|min:0',
            'fields.wr_number_of_abortion' => 'sometimes|nullable|integer|min:0',
            'fields.wr_number_of_living_children' => 'sometimes|nullable|integer|min:0',

            'fields.fmh_first_degree_relatives_with' => 'sometimes|nullable|string|max:255',
            'fields.fmh_first_degree_relatives_with_specify_others' => 'sometimes|nullable|string|max:255',

            'fields.sh_smoking' => 'sometimes|nullable|string|max:50',
            'fields.sh_use_of_vape' => 'sometimes|nullable|string|max:15',
            'fields.sh_use_of_vape_age_started' => 'sometimes|nullable|integer|min:0',

            'fields.soch_illicit_drug_use' => 'sometimes|nullable|string|max:15',
            'fields.soch_illicit_drug_use_specify_illicit_drug_used' => 'sometimes|nullable|string|max:255',
            'fields.soch_sexual_activity_is_sexually_active' => 'sometimes|nullable|string|max:15',
            'fields.soch_sexual_activity_number_of_partners' => 'sometimes|nullable|string|max:15',
            'fields.soch_sexual_activity_with_protection' => 'sometimes|nullable|string|max:15',
            'fields.soch_sexual_activity_testing_done' => 'sometimes|nullable|string|max:15',

            'fields.excessive_alcohol_intake' => 'sometimes|nullable|string|max:15',
            'fields.dietary_fiber_intake_3_servings_of_vegetable_daily' => 'sometimes|nullable|string|max:15',
            'fields.dietary_fiber_intake_2_to_3_servings_of_fruits_daily' => 'sometimes|nullable|string|max:15',
            'fields.high_fat_or_high_salt_food_intake' => 'sometimes|nullable|string|max:15',
            'fields.physical_activity' => 'sometimes|nullable|string|max:15',

            'fields.pahas_or_tia_q1' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q2' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q3' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q4' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q5' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q6' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q7' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q8' => 'sometimes|nullable|string|max:15',

            'fields.diagnosed_as_having_diabetes' => 'sometimes|nullable|string|max:15',
            'fields.symptoms_polyphagia' => 'sometimes|nullable|string|max:15',
            'fields.symptoms_polydipsia' => 'sometimes|nullable|string|max:15',
            'fields.symptoms_polyuria' => 'sometimes|nullable|string|max:15',
            'fields.blood_glucose' => 'sometimes|nullable|numeric',
            'fields.has_raised_blood_glucose' => 'sometimes|nullable|string|max:15',
            'fields.presence_of_urine_ketones_newly_diagnosed' => 'sometimes|nullable|string|max:15',
            'fields.urine_ketones' => 'sometimes|nullable|numeric',
            'fields.urine_ketones_date_taken' => 'sometimes|nullable|date',
            'fields.fbs' => 'sometimes|nullable|integer|min:0',
            'fields.rbs' => 'sometimes|nullable|integer|min:0',
            'fields.fbs_rbs_date_taken' => 'sometimes|nullable|date',
            'fields.blood_lipid' => 'sometimes|nullable|numeric',
            'fields.has_raised_blood_lipid' => 'sometimes|nullable|string|max:15',
            'fields.total_cholesterol' => 'sometimes|nullable|numeric',
            'fields.total_cholesterol_date_taken' => 'sometimes|nullable|date',
            'fields.management' => 'sometimes|nullable|string|max:15',
            'fields.lifestyle_modification' => 'sometimes|nullable|string|max:15',
            'fields.medications' => 'sometimes|nullable|string',
            'fields.presence_of_urine_protein' => 'sometimes|nullable|string|max:15',
            'fields.urine_protein' => 'sometimes|nullable|numeric',
            'fields.urine_protein_date_taken' => 'sometimes|nullable|date',
            'fields.date_follow_up' => 'sometimes|nullable|date',

            'fields.do_laboratory_request' => 'sometimes|nullable|string|max:255',
            'fields.do_imaging' => 'sometimes|nullable|string|max:255',
            'fields.do_imaging_with_contrast' => 'sometimes|nullable|string|max:10',
            'fields.do_diagnosis' => 'sometimes|nullable|string',
            'fields.do_treatment_plan' => 'sometimes|nullable|string|max:255',
            'fields.do_follow_up_date' => 'sometimes|nullable|date',
            'fields.do_prescription' => 'sometimes|nullable|string',
            'fields.do_remarks' => 'sometimes|nullable|string',
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
    public function updatePchRiskProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username')); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

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
            'fields.dswd_nhts_member' => 'sometimes|nullable|string|max:15',
            'fields.four_ps_member' => 'sometimes|nullable|string|max:15',
            'fields.facility_household_number' => 'sometimes|nullable|string|max:50',
            'fields.family_serial_number' => 'sometimes|nullable|string|max:50',
            'fields.philhealth_member' => 'sometimes|nullable|string|max:25',
            'fields.philhealth_number' => 'sometimes|nullable|string|max:50',
            'fields.philhealth_membership_type' => 'sometimes|nullable|string|max:25',
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
            // Update the RiskProfile with new data
            $pchRiskProfile->update($fields);
            return response()->json(['message' => 'Profile successfully updated.'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting risk form: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Something went wrong. Please try again later'], 500);
        }
    }

    // update risk form
    public function updatePchRiskForm(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username')); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        // Define validation rules
        $rules = [
            'fields' => 'required|array',
            'fields.pch_profile_id' => 'required|integer',
            'fields.nature_of_visit' => 'sometimes|string|max:255',
            'fields.nature_of_visit_duration' => 'sometimes|date',
            'fields.type_of_consultation' => 'sometimes|string|max:255',

            'fields.vit_bp_systolic' => 'sometimes|integer|min:0|max:300',
            'fields.vit_bp_diastolic' => 'sometimes|integer|min:0|max:200',
            'fields.vit_oxygen_saturation' => 'sometimes|integer|min:0|max:100',
            'fields.vit_heart_rate_or_pulse_rate' => 'sometimes|integer|min:0',
            'fields.vit_is_normal_rate' => 'sometimes|string|max:50',
            'fields.vit_is_regular_rhythm' => 'sometimes|string|max:50',
            'fields.vit_respiratory_rate' => 'sometimes|integer|min:0',
            'fields.vit_temperature' => 'sometimes|numeric',
            'fields.vit_weight' => 'sometimes|numeric',
            'fields.vit_height' => 'sometimes|numeric',
            'fields.vit_bmi' => 'sometimes|numeric',
            'fields.vit_chief_complaint' => 'sometimes|string|max:255',
            'fields.vit_history_of_present_illness_and_remarks' => 'sometimes|nullable|string',

            'fields.pe_skin_extremities_description' => 'sometimes|nullable|string|max:50',
            'fields.pe_heent_description' => 'sometimes|nullable|string|max:50',
            'fields.pe_chest_description' => 'sometimes|nullable|string|max:50',
            'fields.pe_heart' => 'sometimes|nullable|string|max:50',
            'fields.pe_abdomen' => 'sometimes|nullable|string|max:50',
            'fields.pe_alert_type' => 'sometimes|nullable|string|max:50',
            'fields.pe_description' => 'sometimes|nullable|string|max:50',

            'fields.ab_anatomical_location' => 'sometimes|nullable|string|max:50',
            'fields.ab_anatomical_location_others_specify' => 'sometimes|nullable|string|max:255',
            'fields.ab_animal_type' => 'sometimes|nullable|string|max:50',
            'fields.ab_animal_type_others_specify' => 'sometimes|nullable|string|max:255',
            'fields.ab_description_of_event' => 'sometimes|nullable|string',
            'fields.ab_type_of_exposure' => 'sometimes|nullable|string|max:50',
            'fields.ab_wash_bite' => 'sometimes|nullable|string|max:50',
            'fields.ab_date_of_exposure' => 'sometimes|nullable|date',

            'fields.comorbidities' => 'sometimes|nullable|string|max:50',
            'fields.comorbidities_others' => 'sometimes|nullable|string|max:255',

            'fields.ph_immunization_record_child' => 'sometimes|nullable|string|max:255',
            'fields.ph_immunization_record_child_others_specify' => 'sometimes|nullable|string|max:255',
            'fields.ph_immunization_record_pregnant' => 'sometimes|nullable|string|max:255',
            'fields.ph_immunization_record_pregnant_others_specify' => 'sometimes|nullable|string|max:255',
            'fields.ph_immunization_record_adult_and_elderly' => 'sometimes|nullable|string|max:255',
            'fields.ph_immunization_record_adult_and_elderly_others_specify' => 'sometimes|nullable|string|max:255',

            'fields.wr_menarche' => 'sometimes|nullable|string|max:15',
            'fields.wr_menarche_age' => 'sometimes|nullable|integer|min:0',
            'fields.wr_menopause' => 'sometimes|nullable|string|max:15',
            'fields.wr_menopause_age' => 'sometimes|nullable|integer|min:0',
            'fields.wr_no_of_pads_used_per_day' => 'sometimes|nullable|integer|min:0',
            'fields.wr_interval_cycle_of_menstruation_in_days' => 'sometimes|nullable|integer|min:0',
            'fields.wr_birth_control_method_used' => 'sometimes|nullable|string',
            'fields.wr_onset_of_sexual_intercourse_age' => 'sometimes|nullable|integer|min:0',
            'fields.wr_pregnancy_history_gravidity' => 'sometimes|nullable|integer|min:0',
            'fields.wr_pregnancy_history_parity' => 'sometimes|nullable|integer|min:0',
            'fields.wr_pre_eclampsia' => 'sometimes|nullable|string|max:15',
            'fields.wr_with_access_to_family_planning_counseling' => 'sometimes|nullable|string|max:15',
            'fields.wr_pregnancy_history_num_of_full_term_pregnancy' => 'sometimes|nullable|integer|min:0',
            'fields.wr_pregnancy_history_num_of_premature_pregnancy' => 'sometimes|nullable|integer|min:0',
            'fields.wr_number_of_abortion' => 'sometimes|nullable|integer|min:0',
            'fields.wr_number_of_living_children' => 'sometimes|nullable|integer|min:0',

            'fields.fmh_first_degree_relatives_with' => 'sometimes|nullable|string|max:50',
            'fields.fmh_first_degree_relatives_with_specify_others' => 'sometimes|nullable|string|max:255',

            'fields.sh_smoking' => 'sometimes|nullable|string|max:50',
            'fields.sh_use_of_vape' => 'sometimes|nullable|string|max:15',
            'fields.sh_use_of_vape_age_started' => 'sometimes|nullable|integer|min:0',

            'fields.soch_illicit_drug_use' => 'sometimes|nullable|string|max:15',
            'fields.soch_illicit_drug_use_specify_illicit_drug_used' => 'sometimes|nullable|string|max:255',
            'fields.soch_sexual_activity_is_sexually_active' => 'sometimes|nullable|string|max:15',
            'fields.soch_sexual_activity_number_of_partners' => 'sometimes|nullable|integer|min:0',
            'fields.soch_sexual_activity_with_protection' => 'sometimes|nullable|string|max:15',
            'fields.soch_sexual_activity_testing_done' => 'sometimes|nullable|string|max:15',

            'fields.excessive_alcohol_intake' => 'sometimes|nullable|string|max:15',
            'fields.dietary_fiber_intake_3_servings_of_vegetable_daily' => 'sometimes|nullable|string|max:15',
            'fields.dietary_fiber_intake_2_to_3_servings_of_fruits_daily' => 'sometimes|nullable|string|max:15',
            'fields.high_fat_or_high_salt_food_intake' => 'sometimes|nullable|string|max:15',
            'fields.physical_activity' => 'sometimes|nullable|string|max:15',

            'fields.pahas_or_tia_q1' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q2' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q3' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q4' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q5' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q6' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q7' => 'sometimes|nullable|string|max:15',
            'fields.pahas_or_tia_q8' => 'sometimes|nullable|string|max:15',

            'fields.diagnosed_as_having_diabetes' => 'sometimes|nullable|string|max:15',
            'fields.symptoms_polyphagia' => 'sometimes|nullable|string|max:15',
            'fields.symptoms_polydipsia' => 'sometimes|nullable|string|max:15',
            'fields.symptoms_polyuria' => 'sometimes|nullable|string|max:15',
            'fields.blood_glucose' => 'sometimes|nullable|numeric',
            'fields.has_raised_blood_glucose' => 'sometimes|nullable|string|max:15',
            'fields.presence_of_urine_ketones_newly_diagnosed' => 'sometimes|nullable|string|max:15',
            'fields.urine_ketones' => 'sometimes|nullable|numeric',
            'fields.urine_ketones_date_taken' => 'sometimes|nullable|date',
            'fields.fbs' => 'sometimes|nullable|integer|min:0',
            'fields.rbs' => 'sometimes|nullable|integer|min:0',
            'fields.fbs_rbs_date_taken' => 'sometimes|nullable|date',
            'fields.blood_lipid' => 'sometimes|nullable|numeric',
            'fields.has_raised_blood_lipid' => 'sometimes|nullable|string|max:15',
            'fields.total_cholesterol' => 'sometimes|nullable|numeric',
            'fields.total_cholesterol_date_taken' => 'sometimes|nullable|date',
            'fields.management' => 'sometimes|nullable|string|max:15',
            'fields.lifestyle_modification' => 'sometimes|nullable|string|max:15',
            'fields.medications' => 'sometimes|nullable|string',
            'fields.presence_of_urine_protein' => 'sometimes|nullable|string|max:15',
            'fields.urine_protein' => 'sometimes|nullable|numeric',
            'fields.urine_protein_date_taken' => 'sometimes|nullable|date',
            'fields.date_follow_up' => 'sometimes|nullable|date',

            'fields.do_laboratory_request' => 'sometimes|nullable|string|max:255',
            'fields.do_imaging' => 'sometimes|nullable|string|max:255',
            'fields.do_imaging_with_contrast' => 'sometimes|nullable|string|max:10',
            'fields.do_diagnosis' => 'sometimes|nullable|string',
            'fields.do_treatment_plan' => 'sometimes|nullable|string|max:255',
            'fields.do_follow_up_date' => 'sometimes|nullable|date',
            'fields.do_prescription' => 'sometimes|nullable|string',
            'fields.do_remarks' => 'sometimes|nullable|string',
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
    public function deletePchRiskProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username')); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

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
    public function deletePchRiskForm(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username')); // This replaces Auth::check()x
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

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
