<?php

namespace App\Http\Controllers\TsekapV2\Forms\RiskAssessmentForm;

use Exception;
use App\Models\User;
use App\Models\TsekapV2\UserHealthFacility;
use App\Models\TsekapV2\Facilities;
use App\Models\TsekapV2\Forms\RiskAssessment\RiskAssessmentForm;
use App\Models\TsekapV2\Forms\RiskAssessment\RiskProfile;
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

        if (!$queryUser || $queryUser->verified !== 1) {
            Log::error('Denied access to (Risk Assessment, DataController) for:' . " " . $queryUser->id);
            return response()->json(['error' => 'User not found'], 404);
        }

        return $queryUser;
    }

    // for users with privilege of 1,3,and 10
    private function getAuthenticatedAdmin($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        // do not authorize update unless 1, 3, 10
        if ((!$queryUser || !in_array($queryUser->user_priv, [1, 3, 10])) || ($queryUser->verified !== 1)) {
            Log::error('Denied administrative access to (Risk Assessment, DataController) for: ' + $queryUser->id);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    private function getHealthFacilityForUser($user)
    {
        $userHealthFacilityMapping = UserHealthFacility::where('user_id', $user->id)->first();
        if ($userHealthFacilityMapping) {
            return Facilities::select('id', 'name', 'address', 'hospital_type')
                ->where('id', $userHealthFacilityMapping->facility_id)
                ->first();
        }
        return null;
    }

    // retrieval without facility

    // ---- !!! ACTUAL WORKING FUNCTION !!! ----// 
    // public function retrievePatientRiskProfileWithoutFacility(Request $request)
    // {
    //     // Ensure the user is authenticated via Sanctum
    //     $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()

    //     if ($user instanceof \Illuminate\Http\JsonResponse) {
    //         return $user;
    //     }

    //     // Validate the request
    //     $validator = Validator::make($request->all(), [
    //         'fields.filter' => 'required|string',
    //         'fields.keyword' => 'nullable|string|max:255',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => 'Invalid input'], 400);
    //     }

    //     $fields = $request->input('fields', []);

    //     $filter = isset($fields['filter']) ? $fields['filter'] : null;
    //     $keyword = isset($fields['keyword']) ? $fields['keyword'] : null;

    //     // Base query for risk profiles
    //     $query = RiskProfile::select(
    //         'risk_profile.id',
    //         'risk_profile.fname',
    //         'risk_profile.mname',
    //         'risk_profile.lname',
    //         'risk_profile.dob',
    //         'risk_profile.sex',
    //         'risk_profile.age',
    //         'risk_profile.religion',
    //         'risk_profile.other_religion',
    //         'risk_profile.citizenship',
    //         'risk_profile.other_citizenship',
    //         'risk_profile.indigenous_person',
    //         'risk_profile.employment_status',
    //         'risk_profile.contact',
    //         'risk_profile.civil_status',
    //         'risk_profile.barangay_id',
    //         'risk_profile.municipal_id',
    //         'risk_profile.province_id',
    //         'risk_profile.street',
    //         'risk_profile.purok',
    //         'risk_profile.sitio',
    //         'risk_profile.phic_id',
    //         'risk_profile.pwd_id',
    //         'risk_profile.facility_id_updated',
    //         'risk_profile.offline_entry',
    //         'risk_profile.encoded_by',
    //         'risk_profile.created_at',
    //         'risk_profile.updated_at',
    //         'muncity.description as municipal_name',
    //         'province.description as province_name'
    //     )
    //         ->join('muncity', 'risk_profile.municipal_id', '=', 'muncity.id')
    //         ->join('province', 'risk_profile.province_id', '=', 'province.id');

    //     // Apply user privilege filters
    //     if ($user->user_priv === 3) {
    //         $query->where('risk_profile.province_id', $user->province);
    //     }

    //     // Apply keyword filter
    //     if ($keyword) {
    //         $query->where(function ($q) use ($filter, $keyword) {
    //             $columns = [
    //                 'facility_id_updated' => 'risk_profile.facility_id_updated',
    //                 'fname' => 'risk_profile.fname',
    //                 'lname' => 'risk_profile.lname',
    //                 'dob' => 'risk_profile.dob'
    //             ];

    //             if ($filter === 'dob') {
    //                 // Parse keyword as date
    //                 $parsedDate = date('Y-m-d', strtotime($keyword));
    //                 $q->where($columns['dob'], $parsedDate);
    //             } elseif (isset($columns[$filter])) {
    //                 $q->where($columns[$filter], 'like', "%$keyword%");
    //             } else {
    //                 $q->where('risk_profile.fname', 'like', "%$keyword%")
    //                     ->orWhere('risk_profile.lname', 'like', "%$keyword%")
    //                     ->orWhere('risk_profile.dob', 'like', "%$keyword%")
    //                     ->orWhere('risk_profile.facility_id_updated', '=', $keyword);
    //             }
    //         });
    //     }

    //     // Paginate and return results
    //     $results = $query->simplePaginate(30);

    //     return response()->json($results, 200);
    // }

    //---- !!! EXPERIMENTAL FUNCTION !!! ----//
    public function retrievePatientRiskProfileWithoutFacility(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()
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
        $query = RiskProfile::select(
            'risk_profile.id',
            'risk_profile.fname',
            'risk_profile.mname',
            'risk_profile.lname',
            'risk_profile.suffix', // redacted previously
            'risk_profile.dob',
            'risk_profile.sex',
            'risk_profile.age',
            'risk_profile.religion',
            'risk_profile.other_religion',
            'risk_profile.citizenship',
            'risk_profile.other_citizenship',
            'risk_profile.indigenous_person',
            'risk_profile.employment_status',
            'risk_profile.contact',
            'risk_profile.civil_status',
            'risk_profile.barangay_id',
            'risk_profile.municipal_id',
            'risk_profile.province_id',
            'risk_profile.street',
            'risk_profile.purok',
            'risk_profile.sitio',
            'risk_profile.phic_id',
            'risk_profile.pwd_id',
            'risk_profile.facility_id_updated',
            'risk_profile.offline_entry',
            'risk_profile.encoded_by',
            'risk_profile.created_at',
            'risk_profile.updated_at',
            'muncity.description as municipal_name',
            'province.description as province_name'
        )
            ->join('muncity', 'risk_profile.municipal_id', '=', 'muncity.id')
            ->join('province', 'risk_profile.province_id', '=', 'province.id');

        // Apply user privilege filters
        if ($user->user_priv === 3) {
            $query->where('risk_profile.province_id', $user->province);
        }

        // Apply keyword filter
        if ($keyword) {
            $query->where(function ($q) use ($filter, $keyword) {
                $columns = [
                    'id' => 'risk_profile.id', // included for filtering of ID also
                    'facility_id_updated' => 'risk_profile.facility_id_updated',
                    'fname' => 'risk_profile.fname',
                    'lname' => 'risk_profile.lname',
                    'dob' => 'risk_profile.dob'
                ];

                if ($filter === 'dob') {
                    // Parse keyword as date
                    $parsedDate = date('Y-m-d', strtotime($keyword));
                    $q->where($columns['dob'], $parsedDate);
                } elseif (isset($columns[$filter])) {
                    $q->where($columns[$filter], 'like', "%$keyword%");
                } else {
                    $q->where('risk_profile.fname', 'like', "%$keyword%")
                        ->orWhere('risk_profile.lname', 'like', "%$keyword%")
                        ->orWhere('risk_profile.dob', 'like', "%$keyword%")
                        ->orWhere('risk_profile.id', '=', $keyword) // included for filtering of ID also
                        ->orWhere('risk_profile.facility_id_updated', '=', $keyword);
                }
            });
        }

        // Paginate and return results
        $results = $query->orderBy('created_by', 'desc')->simplePaginate(30);
        return response()->json($results, 200);
    }

    // retrieval by facility using GET parameters
    public function retrievePatientRiskProfileByFacility(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()
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

        if (($user->user_priv === 6 || $user->verified !== 1) && !$facility) {
            return response()->json(['error' => 'Facility not found for user'], 404);
        }

        // Base query for risk profiles
        $query = RiskProfile::select(
            'risk_profile.id',
            'risk_profile.fname',
            'risk_profile.mname',
            'risk_profile.lname',
            'risk_profile.suffix', // redacted previously
            'risk_profile.dob',
            'risk_profile.sex',
            'risk_profile.age',
            'risk_profile.religion',
            'risk_profile.other_religion',
            'risk_profile.citizenship',
            'risk_profile.other_citizenship',
            'risk_profile.indigenous_person',
            'risk_profile.employment_status',
            'risk_profile.contact',
            'risk_profile.civil_status',
            'risk_profile.barangay_id',
            'risk_profile.municipal_id',
            'risk_profile.province_id',
            'risk_profile.street',
            'risk_profile.purok',
            'risk_profile.sitio',
            'risk_profile.phic_id',
            'risk_profile.pwd_id',
            'risk_profile.facility_id_updated',
            'risk_profile.offline_entry',
            'risk_profile.encoded_by',
            'risk_profile.created_at',
            'risk_profile.updated_at',
            'muncity.description as municipal_name',
            'province.description as province_name'
        )
            ->join('muncity', 'risk_profile.municipal_id', '=', 'muncity.id')
            ->join('province', 'risk_profile.province_id', '=', 'province.id');

        // Apply user privilege filters
        if ($user->user_priv === 3) {
            $query->where('risk_profile.province_id', $user->province);
        } elseif ($user->user_priv === 6 && $facility) {
            $query->where('risk_profile.facility_id_updated', $facility->id);
        }

        // Apply keyword filter
        if ($keyword) {
            $query->where(function ($q) use ($filter, $keyword) {
                $columns = [
                    'facility_id_updated' => 'risk_profile.facility_id_updated',
                    'fname' => 'risk_profile.fname',
                    'lname' => 'risk_profile.lname',
                    'dob' => 'risk_profile.dob'
                ];

                if ($filter === 'dob') {
                    // Parse keyword as date
                    $parsedDate = date('Y-m-d', strtotime($keyword));
                    $q->where($columns['dob'], $parsedDate);
                } elseif (isset($columns[$filter])) {
                    $q->where($columns[$filter], 'like', "%$keyword%");
                } else {
                    $q->where('risk_profile.fname', 'like', "%$keyword%")
                        ->orWhere('risk_profile.lname', 'like', "%$keyword%")
                        ->orWhere('risk_profile.dob', 'like', "%$keyword%")
                        ->orWhere('risk_profile.facility_id_updated', '=', $keyword);
                }
            });
        }

        // Paginate and return results
        $results = $query->orderBy('created_by', 'desc')->simplePaginate(30);
        return response()->json($results, 200);
    }

    public function retrievePatientRiskAssessment(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        // Validate the request using the Validator facade
        $validator = Validator::make($request->all(), [
            'profile_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        $id = $request->query('profile_id');

        // Building the query
        $query = RiskAssessmentForm::select(
            'id',
            'risk_profile_id',
            'ar_chest_pain',
            'ar_difficulty_breathing',
            'ar_loss_of_consciousness',
            'ar_slurred_speech',
            'ar_facial_asymmetry',
            'ar_weakness_numbness',
            'ar_disoriented',
            'ar_chest_retractions',
            'ar_seizure_convulsion',
            'ar_act_self_harm_suicide',
            'ar_agitated_behavior',
            'ar_eye_injury',
            'ar_severe_injuries',

            'pmh_hypertension',
            'pmh_heart_disease',
            'pmh_diabetes',
            'pmh_specify_diabetes',
            'pmh_cancer',
            'pmh_specify_cancer',
            'pmh_copd',
            'pmh_asthma',
            'pmh_allergies',
            'pmh_specify_allergies',
            'pmh_mn_and_s_disorder',
            'pmh_specify_mn_and_s_disorder',
            'pmh_vision_problems',
            'pmh_previous_surgical',
            'pmh_specify_previous_surgical',
            'pmh_thyroid_disorders',
            'pmh_kidney_disorders',
            'fmh_hypertension',
            'fmh_stroke',
            'fmh_heart_disease',
            'fmh_diabetes_mellitus',
            'fmh_asthma',
            'fmh_cancer',
            'fmh_kidney_disease',
            'fmh_first_degree_relative',
            'fmh_having_tuberculosis_5_years',
            'fmh_mn_and_s_disorder',
            'fmh_copd',
            'rf_tobacco_use',
            'rf_alcohol_intake',
            'rf_alcohol_binge_drinker',
            'rf_physical_activity',
            'rf_nutrition_dietary',
            'rf_weight',
            'rf_height',
            'rf_body_mass',
            'rf_waist_circumference',
            'rs_systolic_t1',
            'rs_diastolic_t1',
            'rs_systolic_t2',
            'rs_diastolic_t2',
            'rs_blood_sugar_fbs',
            'rs_blood_sugar_rbs',
            'rs_blood_sugar_date_taken',
            'rs_blood_sugar_symptoms',
            'rs_lipid_cholesterol',
            'rs_lipid_hdl',
            'rs_lipid_ldl',
            'rs_lipid_vldl',
            'rs_lipid_triglyceride',
            'rs_lipid_date_taken',
            'rs_urine_protein',
            'rs_urine_protein_date_taken',
            'rs_urine_ketones',
            'rs_urine_ketones_date_taken',
            'rs_chronic_respiratory_disease',
            'rs_if_yes_any_symptoms',
            'mngm_med_hypertension',
            'mngm_med_hypertension_specify',
            'mngm_med_diabetes',
            'mngm_med_diabetes_options',
            'mngm_med_diabetes_specify',
            'mngm_date_follow_up',
            'mngm_remarks',
            'offline_entry',
            'created_at',
            'updated_at'
        );

        if ($id) {
            $query->where('risk_profile_id', $id);
        }
        return response()->json($query->simplePaginate(30), 200);
    }

    public function addRiskProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        // Define validation rules
        $rules = [
            'fields' => 'required|array',
            'fields.profile_id' => 'nullable|integer',
            'fields.lname' => 'required|string|max:255',
            'fields.fname' => 'required|string|max:255',
            'fields.mname' => 'nullable|string|max:255',
            'fields.suffix' => 'nullable|string|max:10',
            'fields.sex' => 'required|string|max:10',
            'fields.dob' => 'required|date',
            'fields.age' => 'required|integer|min:0|max:150',
            'fields.age_bracket_id' => 'nullable|integer',
            'fields.civil_status' => 'required|string|max:20',
            'fields.religion' => 'required|string|max:50',
            'fields.other_religion' => 'nullable|string|max:50',
            'fields.contact' => 'required|string|max:20',
            'fields.province_id' => 'required|integer',
            'fields.municipal_id' => 'required|integer',
            'fields.barangay_id' => 'required|integer',
            'fields.street' => 'nullable|string|max:255',
            'fields.purok' => 'nullable|string|max:255',
            'fields.sitio' => 'nullable|string|max:255',
            'fields.phic_id' => 'nullable|string|max:20',
            'fields.pwd_id' => 'nullable|string|max:20',
            'fields.citizenship' => 'required|string|max:50',
            'fields.other_citizenship' => 'nullable|string|max:50',
            'fields.indigenous_person' => 'required|string|max:8',
            'fields.employment_status' => 'required|string|max:50',
            'fields.facility_id_updated' => 'required|integer',
            'fields.encoded_by' => 'required|integer',
            'fields.offline_entry' => 'required|boolean',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Check for malformed parameters
        if (empty($fields['offline_entry']) && !empty($fields['profile_id'])) {
            return response()->json(['error' => 'Malformed parameter. Please recheck request.'], 403);
        }

        // Check for duplicates
        $existingRiskProfile = RiskProfile::where('fname', $fields['fname'])
            ->where('lname', $fields['lname'])
            ->where('dob', $fields['dob'])
            ->where('facility_id_updated', $fields['facility_id_updated']);

        if (!empty($fields['mname'])) {
            $existingRiskProfile->where('mname', $fields['mname']);
        } else {
            $existingRiskProfile->whereNull('mname');
        }

        if (!empty($fields['profile_id'])) {
            $existingRiskProfile->where('profile_id', $fields['profile_id']);
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
            $riskProfile = new RiskProfile($fields);
            $riskProfile->save();

            return response()->json([
                'message' => 'Entry successfully saved.',
                'id' => $riskProfile->id
            ], 200);
        } catch (Exception $e) {
            Log::error('An error occurred while adding a risk profile: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    public function addRiskForm(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        // Define validation rules
        $rules = [
            'fields' => 'required|array',
            'fields.risk_profile_id' => 'required|integer',
            // ar
            'fields.ar_chest_pain' => 'required|string|max:8',
            'fields.ar_difficulty_breathing' => 'required|string|max:8',
            'fields.ar_loss_of_consciousness' => 'required|string|max:8',
            'fields.ar_slurred_speech' => 'required|string|max:8',
            'fields.ar_facial_asymmetry' => 'required|string|max:8',
            'fields.ar_weakness_numbness' => 'required|string|max:8',
            'fields.ar_disoriented' => 'required|string|max:8',
            'fields.ar_chest_retractions' => 'required|string|max:8',
            'fields.ar_seizure_convulsion' => 'required|string|max:8',
            'fields.ar_act_self_harm_suicide' => 'required|string|max:8',
            'fields.ar_agitated_behavior' => 'required|string|max:8',
            'fields.ar_eye_injury' => 'required|string|max:8',
            'fields.ar_severe_injuries' => 'required|string|max:8',

            // pmh
            'fields.pmh_hypertension' => 'required|string|max:8',
            'fields.pmh_heart_disease' => 'required|string|max:8',
            'fields.pmh_diabetes' => 'required|string|max:8',
            'fields.pmh_specify_diabetes' => 'nullable|string|max:255',
            'fields.pmh_cancer' => 'required|string|max:8',
            'fields.pmh_specify_cancer' => 'nullable|string|max:255',
            'fields.pmh_copd' => 'required|string|max:8',
            'fields.pmh_asthma' => 'required|string|max:8',
            'fields.pmh_allergies' => 'required|string|max:8',
            'fields.pmh_specify_allergies' => 'nullable|string|max:255',
            'fields.pmh_mn_and_s_disorder' => 'required|string|max:8',
            'fields.pmh_specify_mn_and_s_disorder' => 'nullable|string|max:255',
            'fields.pmh_vision_problems' => 'required|string|max:8',
            'fields.pmh_previous_surgical' => 'required|string|max:8',
            'fields.pmh_specify_previous_surgical' => 'nullable|string|max:255',
            'fields.pmh_thyroid_disorders' => 'required|string|max:8',
            'fields.pmh_kidney_disorders' => 'required|string|max:8',

            // fmh
            'fields.fmh_hypertension' => 'required|string|max:20',
            'fields.fmh_stroke' => 'required|string|max:20',
            'fields.fmh_heart_disease' => 'required|string|max:20',
            'fields.fmh_diabetes_mellitus' => 'required|string|max:20',
            'fields.fmh_asthma' => 'required|string|max:20',
            'fields.fmh_cancer' => 'required|string|max:20',
            'fields.fmh_kidney_disease' => 'required|string|max:20',
            'fields.fmh_first_degree_relative' => 'required|string|max:20',
            'fields.fmh_having_tuberculosis_5_years' => 'required|string|max:20',
            'fields.fmh_mn_and_s_disorder' => 'required|string|max:20',
            'fields.fmh_copd' => 'required|string|max:20',

            // rf
            'fields.rf_tobacco_use' => 'required|string|max:255',
            'fields.rf_alcohol_intake' => 'required|string|max:8',
            'fields.rf_alcohol_binge_drinker' => 'nullable|string|max:8',
            'fields.rf_physical_activity' => 'required|string|max:8',
            'fields.rf_nutrition_dietary' => 'required|string|max:8',
            'fields.rf_weight' => 'required|numeric',
            'fields.rf_height' => 'required|numeric',
            'fields.rf_body_mass' => 'required|numeric',
            'fields.rf_waist_circumference' => 'required|numeric',

            // rs
            'fields.rs_systolic_t1' => 'required|numeric',
            'fields.rs_diastolic_t1' => 'required|numeric',
            'fields.rs_systolic_t2' => 'nullable|numeric',
            'fields.rs_diastolic_t2' => 'nullable|numeric',
            'fields.rs_blood_sugar_fbs' => 'nullable|numeric',
            'fields.rs_blood_sugar_rbs' => 'nullable|numeric',
            'fields.rs_blood_sugar_date_taken' => 'nullable|date',
            'fields.rs_blood_sugar_symptoms' => 'nullable|string|max:255',
            'fields.rs_lipid_cholesterol' => 'nullable|numeric',
            'fields.rs_lipid_hdl' => 'nullable|numeric',
            'fields.rs_lipid_ldl' => 'nullable|numeric',
            'fields.rs_lipid_vldl' => 'nullable|numeric',
            'fields.rs_lipid_triglyceride' => 'nullable|numeric',
            'fields.rs_lipid_date_taken' => 'nullable|date',
            'fields.rs_urine_protein' => 'nullable|numeric',
            'fields.rs_urine_protein_date_taken' => 'nullable|date',
            'fields.rs_urine_ketones' => 'nullable|numeric',
            'fields.rs_urine_ketones_date_taken' => 'nullable|date',
            'fields.rs_chronic_respiratory_disease' => 'nullable|string|max:255',
            'fields.rs_if_yes_any_symptoms' => 'nullable|string|max:255',

            // mngm
            'fields.mngm_med_hypertension' => 'nullable|string|max:8',
            'fields.mngm_med_hypertension_specify' => 'nullable|string|max:255',
            'fields.mngm_med_diabetes' => 'nullable|string|max:8',
            'fields.mngm_med_diabetes_options' => 'nullable|string|max:50',
            'fields.mngm_med_diabetes_specify' => 'nullable|string|max:255',
            'fields.mngm_date_follow_up' => 'nullable|date',
            'fields.mngm_remarks' => 'nullable|string|max:255',

            // offline entry field
            'fields.offline_entry' => 'required|boolean',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // Check for duplicate risk_profile_id
            $existingRiskForm = RiskAssessmentForm::where('risk_profile_id', '=', $fields['risk_profile_id'])->first();

            if ($existingRiskForm) {
                return response()->json(['error' => 'Record with the same duplicate risk_profile_id found. Please recheck.'], 409);
            }

            $riskform = new RiskAssessmentForm();

            // Dynamically populate the model with validated data
            foreach ($fields as $key => $value) {
                if (Schema::hasColumn($riskform->getTable(), $key)) {
                    $riskform->$key = $value;
                }
            }

            // Save the data
            $riskform->save();
            return response()->json(['message' => 'Entry successfully saved.'], 200);
        } catch (Exception $e) {
            Log::error('An error has occurred in adding of risk form: ' + $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    // update risk profile
    public function updateRiskProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        // Define validation rules
        $rules = [
            'fields' => 'required|array',
            'fields.id' => 'nullable|integer',
            'fields.profile_id' => 'nullable|integer',
            'fields.lname' => 'required|string|max:255',
            'fields.fname' => 'required|string|max:255',
            'fields.mname' => 'nullable|string|max:255',
            'fields.suffix' => 'nullable|string|max:10',
            'fields.sex' => 'required|string|max:10',
            'fields.dob' => 'required|date',
            'fields.age' => 'required|integer|min:0|max:150',
            'fields.civil_status' => 'required|string|max:20',
            'fields.religion' => 'required|string|max:50',
            'fields.other_religion' => 'nullable|string|max:50',
            'fields.contact' => 'required|string|max:20',
            'fields.province_id' => 'required|integer',
            'fields.municipal_id' => 'required|integer',
            'fields.barangay_id' => 'required|integer',
            'fields.street' => 'nullable|string|max:255',
            'fields.purok' => 'nullable|string|max:255',
            'fields.sitio' => 'nullable|string|max:255',
            'fields.phic_id' => 'nullable|string|max:20',
            'fields.pwd_id' => 'nullable|string|max:20',
            'fields.citizenship' => 'required|string|max:50',
            'fields.other_citizenship' => 'nullable|string|max:50',
            'fields.indigenous_person' => 'required|string|max:8',
            'fields.employment_status' => 'required|string|max:50',
            'fields.facility_id_updated' => 'required|integer',
            'fields.encoded_by' => 'required|integer',
            'fields.offline_entry' => 'required|boolean',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the existing RiskProfile
        $riskprofile = RiskProfile::where('id', "=", $fields['id'])->first();

        if (!$riskprofile) {
            return response()->json(['error' => 'Profile not found.'], 404);
        }

        try {
            // Update the RiskProfile with new data
            $riskprofile->update($fields);
            return response()->json(['message' => 'Profile successfully updated.'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting risk form: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Something went wrong. Please try again later'], 500);
        }
    }

    // update risk form
    public function updateRiskForm(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        // Define validation rules
        $rules = [
            'fields' => 'required|array',
            'fields.risk_profile_id' => 'required|integer',
            // ar
            'fields.ar_chest_pain' => 'required|string|max:8',
            'fields.ar_difficulty_breathing' => 'required|string|max:8',
            'fields.ar_loss_of_consciousness' => 'required|string|max:8',
            'fields.ar_slurred_speech' => 'required|string|max:8',
            'fields.ar_facial_asymmetry' => 'required|string|max:8',
            'fields.ar_weakness_numbness' => 'required|string|max:8',
            'fields.ar_disoriented' => 'required|string|max:8',
            'fields.ar_chest_retractions' => 'required|string|max:8',
            'fields.ar_seizure_convulsion' => 'required|string|max:8',
            'fields.ar_act_self_harm_suicide' => 'required|string|max:8',
            'fields.ar_agitated_behavior' => 'required|string|max:8',
            'fields.ar_eye_injury' => 'required|string|max:8',
            'fields.ar_severe_injuries' => 'required|string|max:8',

            // pmh
            'fields.pmh_hypertension' => 'required|string|max:8',
            'fields.pmh_heart_disease' => 'required|string|max:8',
            'fields.pmh_diabetes' => 'required|string|max:8',
            'fields.pmh_specify_diabetes' => 'nullable|string|max:255',
            'fields.pmh_cancer' => 'required|string|max:8',
            'fields.pmh_specify_cancer' => 'nullable|string|max:255',
            'fields.pmh_copd' => 'required|string|max:8',
            'fields.pmh_asthma' => 'required|string|max:8',
            'fields.pmh_allergies' => 'required|string|max:8',
            'fields.pmh_specify_allergies' => 'nullable|string|max:255',
            'fields.pmh_mn_and_s_disorder' => 'required|string|max:8',
            'fields.pmh_specify_mn_and_s_disorder' => 'nullable|string|max:255',
            'fields.pmh_vision_problems' => 'required|string|max:8',
            'fields.pmh_previous_surgical' => 'required|string|max:8',
            'fields.pmh_specify_previous_surgical' => 'nullable|string|max:255',
            'fields.pmh_thyroid_disorders' => 'required|string|max:8',
            'fields.pmh_kidney_disorders' => 'required|string|max:8',

            // fmh
            'fields.fmh_hypertension' => 'required|string|max:20',
            'fields.fmh_stroke' => 'required|string|max:20',
            'fields.fmh_heart_disease' => 'required|string|max:20',
            'fields.fmh_diabetes_mellitus' => 'required|string|max:20',
            'fields.fmh_asthma' => 'required|string|max:20',
            'fields.fmh_cancer' => 'required|string|max:20',
            'fields.fmh_kidney_disease' => 'required|string|max:20',
            'fields.fmh_first_degree_relative' => 'required|string|max:20',
            'fields.fmh_having_tuberculosis_5_years' => 'required|string|max:20',
            'fields.fmh_mn_and_s_disorder' => 'required|string|max:20',
            'fields.fmh_copd' => 'required|string|max:20',

            // rf
            'fields.rf_tobacco_use' => 'required|string|max:255',
            'fields.rf_alcohol_intake' => 'required|string|max:8',
            'fields.rf_alcohol_binge_drinker' => 'required|string|max:8',
            'fields.rf_physical_activity' => 'required|string|max:8',
            'fields.rf_nutrition_dietary' => 'required|string|max:8',
            'fields.rf_weight' => 'required|numeric',
            'fields.rf_height' => 'required|numeric',
            'fields.rf_body_mass' => 'required|numeric',
            'fields.rf_waist_circumference' => 'required|numeric',

            // rs
            'fields.rs_systolic_t1' => 'required|numeric',
            'fields.rs_diastolic_t1' => 'required|numeric',
            'fields.rs_systolic_t2' => 'required|numeric',
            'fields.rs_diastolic_t2' => 'required|numeric',
            'fields.rs_blood_sugar_fbs' => 'nullable|numeric',
            'fields.rs_blood_sugar_rbs' => 'nullable|numeric',
            'fields.rs_blood_sugar_date_taken' => 'nullable|date',
            'fields.rs_blood_sugar_symptoms' => 'nullable|string|max:255',
            'fields.rs_lipid_cholesterol' => 'nullable|numeric',
            'fields.rs_lipid_hdl' => 'nullable|numeric',
            'fields.rs_lipid_ldl' => 'nullable|numeric',
            'fields.rs_lipid_vldl' => 'nullable|numeric',
            'fields.rs_lipid_triglyceride' => 'nullable|numeric',
            'fields.rs_lipid_date_taken' => 'nullable|date',
            'fields.rs_urine_protein' => 'nullable|numeric',
            'fields.rs_urine_protein_date_taken' => 'nullable|date',
            'fields.rs_urine_ketones' => 'nullable|numeric',
            'fields.rs_urine_ketones_date_taken' => 'nullable|date',
            'fields.rs_chronic_respiratory_disease' => 'nullable|string|max:255',
            'fields.rs_if_yes_any_symptoms' => 'nullable|string|max:255',

            // mngm
            'fields.mngm_med_hypertension' => 'nullable|string|max:8',
            'fields.mngm_med_hypertension_specify' => 'nullable|string|max:255',
            'fields.mngm_med_diabetes' => 'nullable|string|max:8',
            'fields.mngm_med_diabetes_options' => 'nullable|string|max:50',
            'fields.mngm_med_diabetes_specify' => 'nullable|string|max:255',
            'fields.mngm_date_follow_up' => 'nullable|date',
            'fields.mngm_remarks' => 'nullable|string|max:255',

            // offline entry field
            'fields.offline_entry' => 'required|boolean',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the existing RiskFormAssessment
        $riskform = RiskAssessmentForm::where('risk_profile_id', "=", $fields['risk_profile_id'])->first();

        if (!$riskform) {
            return response()->json(['error' => 'Risk form not found.'], 404);
        }

        try {
            // Update the RiskFormAssessment with new data
            $riskform->update($fields);
            return response()->json(['message' => 'Risk form successfully updated.'], 200);
        } catch (Exception $e) {
            Log::error('Error updating risk form: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Something went wrong. Please try again later'], 500);
        }
    }

    // delete risk profile
    public function deleteRiskProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->username); // This replaces Auth::check()
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
            $riskProfile = RiskProfile::where('id', "=", $id)->first();

            if (!$riskProfile) {
                return response()->json(['error' => 'Risk profile not found.'], 404);
            }

            $riskProfile->delete();
            return response()->json(['message' => 'Risk profile successfully deleted.'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting risk profile: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    // delete risk form
    public function deleteRiskForm(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->username); // This replaces Auth::check()x
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'fields.risk_profile_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $id = $request->input('fields.risk_profile_id');

        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        try {
            $riskForm = RiskAssessmentForm::where('risk_profile_id', '=', $id)->first();

            if (!$riskForm) {
                return response()->json(['error' => 'Risk form not found.'], 404);
            }

            $riskForm->delete();
            return response()->json(['message' => 'Risk form successfully deleted.'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting risk form: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Something went wrong. Please try again later'], 500);
        }
    }
}
