<?php

namespace App\Http\Controllers\TsekapV2\Forms\PatientInjuryForm;

use Exception;
use App\Models\User;
use App\Models\TsekapV2\UserHealthFacility;
use App\Models\TsekapV2\Facilities;
use App\Models\TsekapV2\Forms\PatientInjuryForm\PatientInjuryGeneralData;
use App\Models\TsekapV2\Forms\PatientInjuryForm\PatientInjuryPreadmissionData;
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
            Log::error('Denied access to (Patient Injury, DataController) for:' . " " . $queryUser->getAttribute('id'));
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
            Log::error('Denied administrative access to (Patient Injury, DataController) for: ' . $queryUser->getAttribute('id'));
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    private function getHealthFacilityForUser($user)
    {
        $userHealthFacilityMapping = UserHealthFacility::where('user_id', $user->id)->first();
        if ($userHealthFacilityMapping) {
            return Facilities::select('id', 'name', 'address', 'hospital_type')
                ->where('id', $userHealthFacilityMapping->getAttribute('facility_id_updated'))
                ->first();
        }
        return null;
    }

    //---- !!! ADAPTED FUNCTION !!! ----//
    public function retrievePatientInjuryGeneralDataWithoutFacility(Request $request)
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
        $query = PatientInjuryGeneralData::select(
            'patient_injury_form_general_data.id',
            'patient_injury_form_general_data.profile_id',

            'patient_injury_form_general_data.facility_id_updated',
            'patient_injury_form_general_data.name_of_reporting_facility',
            'patient_injury_form_general_data.address_of_reporting_facility',
            'patient_injury_form_general_data.type_of_dru',
            'patient_injury_form_general_data.type_of_patient',
            'patient_injury_form_general_data.encoded_by',
            'patient_injury_form_general_data.offline_entry',
            'patient_injury_form_general_data.hospital_case_no',

            'patient_injury_form_general_data.lname',
            'patient_injury_form_general_data.fname',
            'patient_injury_form_general_data.mname',
            'patient_injury_form_general_data.sex',
            'patient_injury_form_general_data.dob',
            'patient_injury_form_general_data.age',
            'patient_injury_form_general_data.age_in_months',
            'patient_injury_form_general_data.age_in_days',
            'patient_injury_form_general_data.age_bracket_id',

            'patient_injury_form_general_data.purok_sitio',
            'patient_injury_form_general_data.province_id',
            'patient_injury_form_general_data.municipal_id',
            'patient_injury_form_general_data.barangay_id',
            'patient_injury_form_general_data.phic_id',
            'patient_injury_form_general_data.created_at',
            'patient_injury_form_general_data.updated_at',

            'muncity.description as municipal_name',
            'province.description as province_name'
        )
            ->join('muncity', 'patient_injury_form_general_data.municipal_id', '=', 'muncity.id')
            ->join('province', 'patient_injury_form_general_data.province_id', '=', 'province.id');

        // Apply user privilege filters
        if ($user->getAttribute('user_priv') === 3) {
            $query->where('patient_injury_form_general_data.province_id', $user->getAttribute('province'));
        }

        // Apply keyword filter
        if ($keyword) {
            $query->where(function ($q) use ($filter, $keyword) {
                $columns = [
                    'id' => 'patient_injury_form_general_data.id', // included for filtering of ID also
                    'facility_id_updated' => 'patient_injury_form_general_data.facility_id_updated',
                    'fname' => 'patient_injury_form_general_data.fname',
                    'lname' => 'patient_injury_form_general_data.lname',
                    'dob' => 'patient_injury_form_general_data.dob'
                ];

                if ($filter === 'dob') {
                    // Parse keyword as date
                    $parsedDate = date('Y-m-d', strtotime($keyword));
                    $q->where($columns['dob'], $parsedDate);
                } elseif (isset($columns[$filter])) {
                    $q->where($columns[$filter], 'like', "%$keyword%");
                } else {
                    $q->where('patient_injury_form_general_data.fname', 'like', "%$keyword%")
                        ->orWhere('patient_injury_form_general_data.lname', 'like', "%$keyword%")
                        ->orWhere('patient_injury_form_general_data.dob', 'like', "%$keyword%")
                        ->orWhere('patient_injury_form_general_data.id', '=', $keyword) // included for filtering of ID also
                        ->orWhere('patient_injury_form_general_data.facility_id_updated', '=', $keyword);
                }
            });
        }

        // Paginate and return results
        $results = $query->orderBy('created_at', 'desc')->simplePaginate(30);
        return response()->json($results, 200);
    }

    // retrieval by facility using GET parameters
    public function retrievePatientInjuryGeneralDataByFacility(Request $request)
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
        $query = PatientInjuryGeneralData::select(
            'patient_injury_form_general_data.id',
            'patient_injury_form_general_data.profile_id',

            'patient_injury_form_general_data.facility_id_updated',
            'patient_injury_form_general_data.name_of_reporting_facility',
            'patient_injury_form_general_data.address_of_reporting_facility',
            'patient_injury_form_general_data.type_of_dru',
            'patient_injury_form_general_data.type_of_patient',
            'patient_injury_form_general_data.encoded_by',
            'patient_injury_form_general_data.offline_entry',
            'patient_injury_form_general_data.hospital_case_no',

            'patient_injury_form_general_data.lname',
            'patient_injury_form_general_data.fname',
            'patient_injury_form_general_data.mname',
            'patient_injury_form_general_data.sex',
            'patient_injury_form_general_data.dob',
            'patient_injury_form_general_data.age',
            'patient_injury_form_general_data.age_in_months',
            'patient_injury_form_general_data.age_in_days',
            'patient_injury_form_general_data.age_bracket_id',

            'patient_injury_form_general_data.purok_sitio',
            'patient_injury_form_general_data.province_id',
            'patient_injury_form_general_data.municipal_id',
            'patient_injury_form_general_data.barangay_id',
            'patient_injury_form_general_data.phic_id',
            'patient_injury_form_general_data.created_at',
            'patient_injury_form_general_data.updated_at',
            'muncity.description as municipal_name',
            'province.description as province_name'
        )
            ->join('muncity', 'patient_injury_form_general_data.municipal_id', '=', 'muncity.id')
            ->join('province', 'patient_injury_form_general_data.province_id', '=', 'province.id');

        // Apply user privilege filters
        if ($user->getAttribute('user_priv') === 3) {
            $query->where('patient_injury_form_general_data.province_id', $user->getAttribute('province'));
        } elseif ($user->getAttribute('user_priv') === 6 && $facility) {
            $query->where('patient_injury_form_general_data.facility_id_updated', $facility->id);
        }

        // Apply keyword filter
        if ($keyword) {
            $query->where(function ($q) use ($filter, $keyword) {
                $columns = [
                    'facility_id_updated_updated' => 'patient_injury_form_general_data.facility_id_updated',
                    'fname' => 'patient_injury_form_general_data.fname',
                    'lname' => 'patient_injury_form_general_data.lname',
                    'dob' => 'patient_injury_form_general_data.dob'
                ];

                if ($filter === 'dob') {
                    // Parse keyword as date
                    $parsedDate = date('Y-m-d', strtotime($keyword));
                    $q->where($columns['dob'], $parsedDate);
                } elseif (isset($columns[$filter])) {
                    $q->where($columns[$filter], 'like', "%$keyword%");
                } else {
                    $q->where('patient_injury_form_general_data.fname', 'like', "%$keyword%")
                        ->orWhere('patient_injury_form_general_data.lname', 'like', "%$keyword%")
                        ->orWhere('patient_injury_form_general_data.dob', 'like', "%$keyword%")
                        ->orWhere('patient_injury_form_general_data.facility_id_updated', '=', $keyword);
                }
            });
        }

        // Paginate and return results
        $results = $query->orderBy('created_at', 'desc')->simplePaginate(30);
        return response()->json($results, 200);
    }

    public function retrievePatientInjuryPreadmissionData(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username')); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        // Validate the request using the Validator facade
        $validator = Validator::make($request->all(), [
            'general_data_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        $id = $request->query('general_data_id');

        // Building the query
        $query = PatientInjuryPreadmissionData::select(
            'patient_injury_form_preadmission_data.id',
            'patient_injury_form_preadmission_data.general_data_id',
            'patient_injury_form_preadmission_data.poi_province_id',
            'patient_injury_form_preadmission_data.poi_municipal_id',
            'patient_injury_form_preadmission_data.poi_barangay_id',
            'patient_injury_form_preadmission_data.poi_purok_sitio',
            'patient_injury_form_preadmission_data.poi_date_of_injury',
            'patient_injury_form_preadmission_data.poi_time_of_injury',
            'patient_injury_form_preadmission_data.date_of_consultation',
            'patient_injury_form_preadmission_data.time_of_consultation',
            'patient_injury_form_preadmission_data.injury_intent_type',
            'patient_injury_form_preadmission_data.first_aid_given_yes_no',
            'patient_injury_form_preadmission_data.first_aid_given_by_whom',
            'patient_injury_form_preadmission_data.first_aid_given_what',
            'patient_injury_form_preadmission_data.noi_multiple_injuries_yes_no',
            'patient_injury_form_preadmission_data.noi_abrasions_yes_no',
            'patient_injury_form_preadmission_data.noi_abrasions_details',
            'patient_injury_form_preadmission_data.noi_abrasions_body_parts',
            'patient_injury_form_preadmission_data.noi_avulsion_yes_no',
            'patient_injury_form_preadmission_data.noi_avulsion_details',
            'patient_injury_form_preadmission_data.noi_avulsion_body_parts',
            'patient_injury_form_preadmission_data.noi_burn_yes_no',
            'patient_injury_form_preadmission_data.noi_burn_details',
            'patient_injury_form_preadmission_data.noi_burn_body_parts',
            'patient_injury_form_preadmission_data.noi_burn_degree',
            'patient_injury_form_preadmission_data.noi_concussion_yes_no',
            'patient_injury_form_preadmission_data.noi_concussion_details',
            'patient_injury_form_preadmission_data.noi_concussion_body_parts',
            'patient_injury_form_preadmission_data.noi_contusion_yes_no',
            'patient_injury_form_preadmission_data.noi_contusion_details',
            'patient_injury_form_preadmission_data.noi_contusion_body_parts',
            'patient_injury_form_preadmission_data.noi_fracture_yes_no',
            'patient_injury_form_preadmission_data.noi_fracture_type',
            'patient_injury_form_preadmission_data.noi_fracture_details',
            'patient_injury_form_preadmission_data.noi_fracture_body_parts',
            'patient_injury_form_preadmission_data.noi_open_wound_yes_no',
            'patient_injury_form_preadmission_data.noi_open_wound_details',
            'patient_injury_form_preadmission_data.noi_open_wound_body_parts',
            'patient_injury_form_preadmission_data.noi_traumatic_amputation_yes_no',
            'patient_injury_form_preadmission_data.noi_traumatic_amputation_details',
            'patient_injury_form_preadmission_data.noi_traumatic_amputation_body_parts',
            'patient_injury_form_preadmission_data.noi_others_yes_no',
            'patient_injury_form_preadmission_data.noi_others_specify',
            'patient_injury_form_preadmission_data.noi_others_details',
            'patient_injury_form_preadmission_data.noi_others_body_parts',
            'patient_injury_form_preadmission_data.eci_bites_stings_yes_no',
            'patient_injury_form_preadmission_data.eci_bites_stings_details',
            'patient_injury_form_preadmission_data.eci_burns_yes_no',
            'patient_injury_form_preadmission_data.eci_burns_details',
            'patient_injury_form_preadmission_data.eci_burns_specify_others',
            'patient_injury_form_preadmission_data.eci_chemical_substance_yes_no',
            'patient_injury_form_preadmission_data.eci_chemical_substance_details',
            'patient_injury_form_preadmission_data.eci_contact_with_sharps_yes_no',
            'patient_injury_form_preadmission_data.eci_contact_with_sharps_details',
            'patient_injury_form_preadmission_data.eci_drowning_yes_no',
            'patient_injury_form_preadmission_data.eci_drowning_details',
            'patient_injury_form_preadmission_data.eci_drowning_specify_others',
            'patient_injury_form_preadmission_data.eci_exposure_to_force_of_nature_yes_no',
            'patient_injury_form_preadmission_data.eci_exposure_to_force_of_nature_details',
            'patient_injury_form_preadmission_data.eci_fall_yes_no',
            'patient_injury_form_preadmission_data.eci_fall_details',
            'patient_injury_form_preadmission_data.eci_firecracker_yes_no',
            'patient_injury_form_preadmission_data.eci_firecracker_details',
            'patient_injury_form_preadmission_data.eci_sa_or_alleged_rape_yes_no',
            'patient_injury_form_preadmission_data.eci_sa_or_alleged_rape_details',
            'patient_injury_form_preadmission_data.eci_gunshot_yes_no',
            'patient_injury_form_preadmission_data.eci_gunshot_details',
            'patient_injury_form_preadmission_data.eci_hanging_strangulation_yes_no',
            'patient_injury_form_preadmission_data.eci_hanging_strangulation_details',
            'patient_injury_form_preadmission_data.eci_mauling_assaults_yes_no',
            'patient_injury_form_preadmission_data.eci_mauling_assaults_details',
            'patient_injury_form_preadmission_data.eci_vehicular_accident_yes_no',
            'patient_injury_form_preadmission_data.eci_vehicular_accident_details',
            'patient_injury_form_preadmission_data.coi_vehicular_accident_location',
            'patient_injury_form_preadmission_data.coi_vehicular_accident_type',
            'patient_injury_form_preadmission_data.coi_patients_vehicle',
            'patient_injury_form_preadmission_data.coi_patients_vehicle_specify_others',
            'patient_injury_form_preadmission_data.coi_other_vehicle_or_object_involved',
            'patient_injury_form_preadmission_data.coi_other_vehicle_or_object_involved_specify_others',
            'patient_injury_form_preadmission_data.coi_act_of_pat_at_time_of_incident',
            'patient_injury_form_preadmission_data.coi_act_of_pat_at_time_of_incident_specify_others',
            'patient_injury_form_preadmission_data.coi_oth_risk_factors_at_the_time_of_the_incident',
            'patient_injury_form_preadmission_data.coi_oth_risk_factors_at_the_time_of_the_incident_specify_others',
            'patient_injury_form_preadmission_data.coi_safety',
            'patient_injury_form_preadmission_data.coi_safety_specify_others',
            'patient_injury_form_preadmission_data.coi_position_of_patient',
            'patient_injury_form_preadmission_data.coi_position_of_patient_specify_others',
            'patient_injury_form_preadmission_data.coi_place_of_occurrence',
            'patient_injury_form_preadmission_data.coi_place_of_occurrence_specify_workplace',
            'patient_injury_form_preadmission_data.coi_place_of_occurrence_specify_others',
            'patient_injury_form_preadmission_data.hfd_er_opd_bhs_rhu_yes_no',
            'patient_injury_form_preadmission_data.hfd_transferred_from_other_facility_yes_no',
            'patient_injury_form_preadmission_data.hfd_referred_by_other_other_facility_yes_no',
            'patient_injury_form_preadmission_data.hfd_originating_facility',
            'patient_injury_form_preadmission_data.hfd_status_on_arrival',
            'patient_injury_form_preadmission_data.hfd_transport_mode',
            'patient_injury_form_preadmission_data.hfd_transport_mode_specify_others',
            'patient_injury_form_preadmission_data.hfd_initial_impression',
            'patient_injury_form_preadmission_data.hfd_icd10_code_nature_of_injury',
            'patient_injury_form_preadmission_data.hfd_icd10_code_external_cause_of_injury',
            'patient_injury_form_preadmission_data.hfd_disposition',
            'patient_injury_form_preadmission_data.hfd_outcome',
            'patient_injury_form_preadmission_data.hfd_in_patient_admitted_yes_no',
            'patient_injury_form_preadmission_data.hfd_in_patient_complete_final_diagnosis',
            'patient_injury_form_preadmission_data.hfd_in_patient_disposition',
            'patient_injury_form_preadmission_data.hfd_in_patient_outcome',
            'patient_injury_form_preadmission_data.hfd_in_patient_icd10_code_nature_of_injury',
            'patient_injury_form_preadmission_data.hfd_in_patient_icd10_code_external_cause_of_injury',
            'patient_injury_form_preadmission_data.created_at',
            'patient_injury_form_preadmission_data.updated_at',
        );

        if ($id) {
            $query->where('patient_injury_form_preadmission_data.general_data_id', "=", $id);
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

    public function addPatientInjuryGeneralData(Request $request)
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

            // dru metadata
            'fields.facility_id_updated' => 'required|integer',
            'fields.name_of_reporting_facility' => 'required|string|max:255',
            'fields.address_of_reporting_facility' => 'required|string|max:255',
            'fields.type_of_dru' => 'required|string|max:50',
            'fields.type_of_patient' => 'required|string|max:50',
            'fields.encoded_by' => 'required|integer',
            'fields.offline_entry' => 'required|boolean',
            'fields.hospital_case_no' => 'sometimes|nullable|string|max:100',

            // profile and personal information
            'fields.lname' => 'required|string|max:255',
            'fields.fname' => 'required|string|max:255',
            'fields.mname' => 'sometimes|nullable|string|max:255',
            'fields.sex' => 'required|string|max:10',
            'fields.dob' => 'required|date',
            'fields.age' => 'sometimes|numeric|min:0|max:120',
            'fields.age_in_months' => 'sometimes|numeric|min:0|max:12',
            'fields.age_in_days' => 'sometimes|numeric|min:0|max:31',
            'fields.age_bracket_id' => 'required|integer',

            'fields.purok_sitio' => 'sometimes|nullable|string|max:255',
            'fields.province_id' => 'required|integer',
            'fields.municipal_id' => 'required|integer',
            'fields.barangay_id' => 'required|integer',
            'fields.phic_id' => 'sometimes|nullable|string|max:50',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Check for duplicates
        $existingPatientInjuryGeneralData = PatientInjuryGeneralData::where('fname', $fields['fname'])
            ->where('lname', $fields['lname'])
            ->where('dob', $fields['dob'])
            ->where('facility_id_updated', $fields['facility_id_updated'])
            ->whereDate('created_at', '!=', now()->toDateString()); // Exclude records created today

        if (!empty($fields['mname'])) {
            $existingPatientInjuryGeneralData->where('mname', $fields['mname']);
        } else {
            $existingPatientInjuryGeneralData->whereNull('mname');
        }

        if ($existingPatientInjuryGeneralData->exists()) {
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
            $patientInjuryGeneralData = new PatientInjuryGeneralData($fields);
            $patientInjuryGeneralData->save();

            return response()->json([
                'message' => 'Entry successfully saved.',
                'id' => $patientInjuryGeneralData->getAttribute('id')
            ], 200);
        } catch (Exception $e) {
            Log::error('An error occurred while adding a patient injury general data: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    public function addPatientInjuryPreadmissionData(Request $request)
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
            'fields.id' => 'required|integer|min:0',
            'fields.general_data_id' => 'required|integer|min:0',

            'fields.poi_province_id' => 'required|integer|min:0',
            'fields.poi_municipal_id' => 'required|integer|min:0',
            'fields.poi_barangay_id' => 'required|integer|min:0',
            'fields.poi_purok_sitio' => 'required|string|max:255',
            'fields.poi_date_of_injury' => 'required|date',
            'fields.poi_time_of_injury' => 'required|string|max:5',
            'fields.date_of_consultation' => 'required|date',
            'fields.time_of_consultation' => 'required|string|max:5',

            'fields.injury_intent_type' => 'required|string|max:50',
            'fields.first_aid_given_yes_no' => 'required|string|max:10',
            'fields.first_aid_given_by_whom' => 'sometimes|nullable|string',
            'fields.first_aid_given_what' => 'sometimes|nullable|string',

            'fields.noi_multiple_injuries_yes_no' => 'sometimes|nullable|string|max:10',

            'fields.noi_abrasions_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_abrasions_details' => 'sometimes|nullable|string',
            'fields.noi_abrasions_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_avulsion_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_avulsion_details' => 'sometimes|nullable|string',
            'fields.noi_avulsion_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_burn_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_burn_details' => 'sometimes|nullable|string',
            'fields.noi_burn_body_parts' => 'sometimes|nullable|string|max:255',
            'fields.noi_burn_degree' => 'sometimes|nullable|integer|min:0',

            'fields.noi_concussion_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_concussion_details' => 'sometimes|nullable|string',
            'fields.noi_concussion_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_contusion_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_contusion_details' => 'sometimes|nullable|string',
            'fields.noi_contusion_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_fracture_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_fracture_type' => 'sometimes|nullable|string|max:50',
            'fields.noi_fracture_details' => 'sometimes|nullable|string',
            'fields.noi_fracture_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_open_wound_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_open_wound_details' => 'sometimes|nullable|string',
            'fields.noi_open_wound_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_traumatic_amputation_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_traumatic_amputation_details' => 'sometimes|nullable|string',
            'fields.noi_traumatic_amputation_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_others_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_others_specify' => 'sometimes|nullable|string|max:50',
            'fields.noi_others_details' => 'sometimes|nullable|string',
            'fields.noi_others_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.eci_bites_stings_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_bites_stings_details' => 'sometimes|nullable|string',

            'fields.eci_burns_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_burns_details' => 'sometimes|nullable|string',
            'fields.eci_burns_specify_others' => 'sometimes|nullable|string',

            'fields.eci_chemical_substance_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_chemical_substance_details' => 'sometimes|nullable|string',

            'fields.eci_contact_with_sharps_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_contact_with_sharps_details' => 'sometimes|nullable|string',

            'fields.eci_drowning_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_drowning_details' => 'sometimes|nullable|string',
            'fields.eci_drowning_specify_others' => 'sometimes|nullable|string',

            'fields.eci_exposure_to_force_of_nature_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_exposure_to_force_of_nature_details' => 'sometimes|nullable|string',

            'fields.eci_fall_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_fall_details' => 'sometimes|nullable|string',

            'fields.eci_firecracker_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_firecracker_details' => 'sometimes|nullable|string',

            'fields.eci_sa_or_alleged_rape_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_sa_or_alleged_rape_details' => 'sometimes|nullable|string',

            'fields.eci_gunshot_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_gunshot_details' => 'sometimes|nullable|string',

            'fields.eci_hanging_strangulation_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_hanging_strangulation_details' => 'sometimes|nullable|string',

            'fields.eci_mauling_assaults_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_mauling_assaults_details' => 'sometimes|nullable|string',

            'fields.eci_vehicular_accident_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_vehicular_accident_details' => 'sometimes|nullable|string',

            'fields.coi_vehicular_accident_location' => 'sometimes|nullable|string|max:10',
            'fields.coi_vehicular_accident_type' => 'sometimes|nullable|string|max:50',

            'fields.coi_patients_vehicle' => 'sometimes|nullable|string|max:50',
            'fields.coi_patients_vehicle_specify_others' => 'sometimes|nullable|string',

            'fields.coi_other_vehicle_or_object_involved' => 'sometimes|nullable|string|max:50',
            'fields.coi_other_vehicle_or_object_involved_specify_others' => 'sometimes|nullable|string',

            'fields.coi_act_of_pat_at_time_of_incident' => 'sometimes|nullable|string|max:50',
            'fields.coi_act_of_pat_at_time_of_incident_specify_others' => 'sometimes|nullable|string',

            'fields.coi_oth_risk_factors_at_the_time_of_the_incident' => 'sometimes|nullable|string|max:50',
            'fields.coi_oth_risk_factors_at_the_time_of_the_incident_specify_others' => 'sometimes|nullable|string',

            'fields.coi_safety' => 'sometimes|nullable|string|max:70',
            'fields.coi_safety_specify_others' => 'sometimes|nullable|string',

            'fields.coi_position_of_patient' => 'sometimes|nullable|string|max:50',
            'fields.coi_position_of_patient_specify_others' => 'sometimes|nullable|string',

            'fields.coi_place_of_occurrence' => 'sometimes|nullable|string|max:50',
            'fields.coi_place_of_occurrence_specify_workplace' => 'sometimes|nullable|string',
            'fields.coi_place_of_occurrence_specify_others' => 'sometimes|nullable|string',

            'fields.hfd_er_opd_bhs_rhu_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.hfd_transferred_from_other_facility_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.hfd_referred_by_other_other_facility_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.hfd_originating_facility' => 'sometimes|nullable|string',
            'fields.hfd_status_on_arrival' => 'sometimes|nullable|string|max:50',
            'fields.hfd_transport_mode' => 'sometimes|nullable|string|max:50',
            'fields.hfd_transport_mode_specify_others' => 'sometimes|nullable|string',
            'fields.hfd_initial_impression' => 'sometimes|nullable|string',
            'fields.hfd_icd10_code_nature_of_injury' => 'sometimes|nullable|string|max:100',
            'fields.hfd_icd10_code_external_cause_of_injury' => 'sometimes|nullable|string|max:100',
            'fields.hfd_disposition' => 'sometimes|nullable|string|max:100',
            'fields.hfd_outcome' => 'sometimes|nullable|string|max:50',

            'fields.hfd_in_patient_admitted_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.hfd_in_patient_complete_final_diagnosis' => 'sometimes|nullable|string',
            'fields.hfd_in_patient_disposition' => 'sometimes|nullable|string|max:100',
            'fields.hfd_in_patient_outcome' => 'sometimes|nullable|string|max:50',
            'fields.hfd_in_patient_icd10_code_nature_of_injury' => 'sometimes|nullable|string|max:100',
            'fields.hfd_in_patient_icd10_code_external_cause_of_injury' => 'sometimes|nullable|string|max:100',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {

            // ---- Disregard Duplication for this form ----
            // Check for duplicate general_data_id
            $existingPatientInjuryPreadmissionData = PatientInjuryPreadmissionData::where('general_data_id', '=', $fields['general_data_id'])->first();

            if ($existingPatientInjuryPreadmissionData) {
                return response()->json(['error' => 'Record with the same duplicate general_data_id found. Please recheck.'], 409);
            }

            $patientInjuryPreadmissionData = new PatientInjuryPreadmissionData();

            // Dynamically populate the model with validated data
            foreach ($fields as $key => $value) {
                if (Schema::hasColumn($patientInjuryPreadmissionData->getTable(), $key)) {
                    $patientInjuryPreadmissionData->$key = $value;
                }
            }

            // Save the data
            $patientInjuryPreadmissionData->save();
            return response()->json(['message' => 'Entry successfully saved.'], 200);
        } catch (Exception $e) {
            Log::error('An error has occurred in adding of Patient Injury Preadmission Data: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    // update risk profile
    public function updatePatientInjuryGeneralData(Request $request)
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
            'fields.id' => 'sometimes|integer',
            'fields.profile_id' => 'sometimes|integer',

            // dru metadata
            'fields.facility_id_updated' => 'sometimes|integer',
            'fields.name_of_reporting_facility' => 'sometimes|string|max:255',
            'fields.address_of_reporting_facility' => 'sometimes|string|max:255',
            'fields.type_of_dru' => 'sometimes|string|max:50',
            'fields.type_of_patient' => 'sometimes|string|max:50',
            'fields.encoded_by' => 'sometimes|integer',
            'fields.offline_entry' => 'sometimes|boolean',
            'fields.hospital_case_no' => 'sometimes|nullable|string|max:100',

            // profile and personal information
            'fields.lname' => 'sometimes|string|max:255',
            'fields.fname' => 'sometimes|string|max:255',
            'fields.mname' => 'sometimes|nullable|string|max:255',
            'fields.sex' => 'sometimes|string|max:10',
            'fields.dob' => 'sometimes|date',
            'fields.age' => 'sometimes|numeric|min:0|max:120',
            'fields.age_in_months' => 'sometimes|numeric|min:0|max:12',
            'fields.age_in_days' => 'sometimes|numeric|min:0|max:31',
            'fields.age_bracket_id' => 'sometimes|integer',

            'fields.purok_sitio' => 'sometimes|nullable|string|max:255',
            'fields.province_id' => 'sometimes|integer',
            'fields.municipal_id' => 'sometimes|integer',
            'fields.barangay_id' => 'sometimes|integer',
            'fields.phic_id' => 'sometimes|nullable|string|max:50',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the existing RiskProfile
        $pchRiskProfile = PatientInjuryGeneralData::where('id', "=", $fields['id'])->first();

        if (!$pchRiskProfile) {
            return response()->json(['error' => 'Profile not found.'], 404);
        }

        try {
            // Update the RiskProfile with new data
            $pchRiskProfile->update($fields);
            return response()->json(['message' => 'Patient injury general data successfully updated.'], 200);
        } catch (Exception $e) {
            Log::error('Error updating patient injury general data: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Something went wrong. Please try again later'], 500);
        }
    }

    // update risk form
    public function updatePatientInjuryPreadmissionData(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username')); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        // Define validation rules
        $rules = [
            'fields' => 'sometimes|array',
            'fields.general_data_id' => 'sometimes|integer|min:0',

            'fields.poi_province_id' => 'sometimes|integer|min:0',
            'fields.poi_municipal_id' => 'sometimes|integer|min:0',
            'fields.poi_barangay_id' => 'sometimes|integer|min:0',
            'fields.poi_purok_sitio' => 'sometimes|string|max:255',
            'fields.poi_date_of_injury' => 'sometimes|date',
            'fields.poi_time_of_injury' => 'sometimes|string|max:5',
            'fields.date_of_consultation' => 'sometimes|date',
            'fields.time_of_consultation' => 'sometimes|string|max:5',

            'fields.injury_intent_type' => 'sometimes|string|max:50',
            'fields.first_aid_given_yes_no' => 'sometimes|string|max:10',
            'fields.first_aid_given_by_whom' => 'sometimes|nullable|string',
            'fields.first_aid_given_what' => 'sometimes|nullable|string',

            'fields.noi_multiple_injuries_yes_no' => 'sometimes|nullable|string|max:10',

            'fields.noi_abrasions_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_abrasions_details' => 'sometimes|nullable|string',
            'fields.noi_abrasions_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_avulsion_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_avulsion_details' => 'sometimes|nullable|string',
            'fields.noi_avulsion_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_burn_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_burn_details' => 'sometimes|nullable|string',
            'fields.noi_burn_body_parts' => 'sometimes|nullable|string|max:255',
            'fields.noi_burn_degree' => 'sometimes|nullable|integer|min:0',

            'fields.noi_concussion_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_concussion_details' => 'sometimes|nullable|string',
            'fields.noi_concussion_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_contusion_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_contusion_details' => 'sometimes|nullable|string',
            'fields.noi_contusion_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_fracture_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_fracture_type' => 'sometimes|nullable|string|max:50',
            'fields.noi_fracture_details' => 'sometimes|nullable|string',
            'fields.noi_fracture_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_open_wound_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_open_wound_details' => 'sometimes|nullable|string',
            'fields.noi_open_wound_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_traumatic_amputation_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_traumatic_amputation_details' => 'sometimes|nullable|string',
            'fields.noi_traumatic_amputation_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.noi_others_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.noi_others_specify' => 'sometimes|nullable|string|max:50',
            'fields.noi_others_details' => 'sometimes|nullable|string',
            'fields.noi_others_body_parts' => 'sometimes|nullable|string|max:255',

            'fields.eci_bites_stings_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_bites_stings_details' => 'sometimes|nullable|string',

            'fields.eci_burns_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_burns_details' => 'sometimes|nullable|string',
            'fields.eci_burns_specify_others' => 'sometimes|nullable|string',

            'fields.eci_chemical_substance_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_chemical_substance_details' => 'sometimes|nullable|string',

            'fields.eci_contact_with_sharps_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_contact_with_sharps_details' => 'sometimes|nullable|string',

            'fields.eci_drowning_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_drowning_details' => 'sometimes|nullable|string',
            'fields.eci_drowning_specify_others' => 'sometimes|nullable|string',

            'fields.eci_exposure_to_force_of_nature_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_exposure_to_force_of_nature_details' => 'sometimes|nullable|string',

            'fields.eci_fall_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_fall_details' => 'sometimes|nullable|string',

            'fields.eci_firecracker_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_firecracker_details' => 'sometimes|nullable|string',

            'fields.eci_sa_or_alleged_rape_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_sa_or_alleged_rape_details' => 'sometimes|nullable|string',

            'fields.eci_gunshot_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_gunshot_details' => 'sometimes|nullable|string',

            'fields.eci_hanging_strangulation_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_hanging_strangulation_details' => 'sometimes|nullable|string',

            'fields.eci_mauling_assaults_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_mauling_assaults_details' => 'sometimes|nullable|string',

            'fields.eci_vehicular_accident_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.eci_vehicular_accident_details' => 'sometimes|nullable|string',

            'fields.coi_vehicular_accident_location' => 'sometimes|nullable|string|max:10',
            'fields.coi_vehicular_accident_type' => 'sometimes|nullable|string|max:50',

            'fields.coi_patients_vehicle' => 'sometimes|nullable|string|max:50',
            'fields.coi_patients_vehicle_specify_others' => 'sometimes|nullable|string',

            'fields.coi_other_vehicle_or_object_involved' => 'sometimes|nullable|string|max:50',
            'fields.coi_other_vehicle_or_object_involved_specify_others' => 'sometimes|nullable|string',

            'fields.coi_act_of_pat_at_time_of_incident' => 'sometimes|nullable|string|max:50',
            'fields.coi_act_of_pat_at_time_of_incident_specify_others' => 'sometimes|nullable|string',

            'fields.coi_oth_risk_factors_at_the_time_of_the_incident' => 'sometimes|nullable|string|max:50',
            'fields.coi_oth_risk_factors_at_the_time_of_the_incident_specify_others' => 'sometimes|nullable|string',

            'fields.coi_safety' => 'sometimes|nullable|string|max:70',
            'fields.coi_safety_specify_others' => 'sometimes|nullable|string',

            'fields.coi_position_of_patient' => 'sometimes|nullable|string|max:50',
            'fields.coi_position_of_patient_specify_others' => 'sometimes|nullable|string',

            'fields.coi_place_of_occurrence' => 'sometimes|nullable|string|max:50',
            'fields.coi_place_of_occurrence_specify_workplace' => 'sometimes|nullable|string',
            'fields.coi_place_of_occurrence_specify_others' => 'sometimes|nullable|string',

            'fields.hfd_er_opd_bhs_rhu_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.hfd_transferred_from_other_facility_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.hfd_referred_by_other_other_facility_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.hfd_originating_facility' => 'sometimes|nullable|string',
            'fields.hfd_status_on_arrival' => 'sometimes|nullable|string|max:50',
            'fields.hfd_transport_mode' => 'sometimes|nullable|string|max:50',
            'fields.hfd_transport_mode_specify_others' => 'sometimes|nullable|string',
            'fields.hfd_initial_impression' => 'sometimes|nullable|string',
            'fields.hfd_icd10_code_nature_of_injury' => 'sometimes|nullable|string|max:100',
            'fields.hfd_icd10_code_external_cause_of_injury' => 'sometimes|nullable|string|max:100',
            'fields.hfd_disposition' => 'sometimes|nullable|string|max:100',
            'fields.hfd_outcome' => 'sometimes|nullable|string|max:50',

            'fields.hfd_in_patient_admitted_yes_no' => 'sometimes|nullable|string|max:10',
            'fields.hfd_in_patient_complete_final_diagnosis' => 'sometimes|nullable|string',
            'fields.hfd_in_patient_disposition' => 'sometimes|nullable|string|max:100',
            'fields.hfd_in_patient_outcome' => 'sometimes|nullable|string|max:50',
            'fields.hfd_in_patient_icd10_code_nature_of_injury' => 'sometimes|nullable|string|max:100',
            'fields.hfd_in_patient_icd10_code_external_cause_of_injury' => 'sometimes|nullable|string|max:100',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the existing RiskFormAssessment
        $patientInjuryPreadmissionData = PatientInjuryPreadmissionData::where('general_data_id', "=", $fields['general_data_id'])->first();

        if (!$patientInjuryPreadmissionData) {
            return response()->json(['error' => 'Patient injury preadmission data not found.'], 404);
        }

        try {
            // Update the RiskFormAssessment with new data
            $patientInjuryPreadmissionData->update($fields);
            return response()->json(['message' => 'Patient injury preadmission data successfully updated.'], 200);
        } catch (Exception $e) {
            Log::error('Error updating patient injury preadmission data: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Something went wrong. Please try again later'], 500);
        }
    }

    // delete patient injury general data
    public function deletePatientInjuryGeneralData(Request $request)
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
            $pchRiskProfile = PatientInjuryGeneralData::where('id', "=", $id)->first();

            if (!$pchRiskProfile) {
                return response()->json(['error' => 'Patient injury general data not found.'], 404);
            }

            $pchRiskProfile->delete();
            return response()->json(['message' => 'Patient injury general data successfully deleted.'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting patient injury general data: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    // delete patient injury preadmission data
    public function deletePatientInjuryPreadmissionData(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username')); // This replaces Auth::check()x
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'fields.general_data_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $id = $request->input('fields.general_data_id');

        try {
            $riskForm = PatientInjuryPreadmissionData::where('general_data_id', '=', $id)->first();

            if (!$riskForm) {
                return response()->json(['error' => 'Patient injury general data not found.'], 404);
            }

            $riskForm->delete();
            return response()->json(['message' => 'Patient injury general data successfully deleted.'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting patient injury general data: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Something went wrong. Please try again later'], 500);
        }
    }
}
