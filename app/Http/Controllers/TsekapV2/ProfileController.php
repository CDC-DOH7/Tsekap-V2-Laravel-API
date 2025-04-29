<?php

namespace App\Http\Controllers\TsekapV2;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\TsekapV2\Profile;
use App\Models\TsekapV2\ProfileOtherDetails;
use App\Jobs\RetrieveProfileJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User; // Import the User model
use Illuminate\Support\Facades\Log; // Import the Log facade
use Exception;

class ProfileController extends Controller
{
    private function getAuthenticatedUser($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->verified !== 1) {
            Log::error('Denied access for: ' . " " . $queryUser->id);
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    // for users with privilege of 1,3,and 10
    private function getAuthenticatedAdmin($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if ((!$queryUser || !in_array($queryUser->user_priv, [1, 3, 10])) || ($queryUser->verified !== 1)) {
            Log::error('Denied administrative access for: ' + $queryUser->id);
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    // generator functions 
    private function generateFamilyId(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        // get formatted time to be used as metadata
        $getFormattedDate = date('His');

        $ctrlNo = str_pad($getFormattedDate, 4, 0, STR_PAD_LEFT);
        $idNo = str_pad($user->id, 4, 0, STR_PAD_LEFT); // prove that there is a user that is logged-in

        return date('mdy') . '-' . $idNo . '-' . $ctrlNo;
    }

    private function generateUniqueId($fname, $mname, $lname, $barangay_id, $muncity_id)
    {
        return $fname . $mname . $lname . $barangay_id . $muncity_id;
    }

    public function retrieveProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.family_id' => 'nullable|string',
            'fields.first_name' => 'nullable|string',
            'fields.middle_name' => 'nullable|string',
            'fields.last_name' => 'nullable|string',
            'fields.dob' => 'nullable|string|date',
            'fields.barangay_id' => 'nullable|integer',
            'fields.municipal_id' => 'nullable|integer',
            'fields.province_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $fields = $request->input('fields');

        if (!is_array($fields)) {
            return response()->json(['error' => 'Invalid input format.'], 400);
        }

        // Process the job
        $job = new RetrieveProfileJob($fields);
        $result = $job->handle();

        if ($result === 'timeout') {
            return response()->json([
                'message' => 'Request timed out. Please try again.',
            ], 408);
        }

        if ($result === 'Validation failed') {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        if (empty($result)) {
            return response()->json([
                'message' => 'No profiles found. Please refine your search criteria and try again.',
            ], 404);
        }

        return response()->json([
            'message' => 'Profiles retrieved successfully',
            'profiles' => $result,
        ], 200);
    }


    public function addProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.lname' => 'required|string|max:255',
            'fields.fname' => 'required|string|max:255',
            'fields.mname' => 'sometimes|string|max:255|nullable',
            'fields.suffix' => 'sometimes|string|max:255|nullable',
            'fields.sex' => 'sometimes|string|max:255|nullable',
            'fields.dob' => 'required|date',
            'fields.birth_place' => 'sometimes|string|max:255|nullable',
            'fields.civil_status' => 'required|string|max:255',
            'fields.contact' => 'sometimes|string|max:255|nullable',
            'fields.health_group' => 'sometimes|string|max:255|nullable',
            'fields.religion' => 'sometimes|string|max:255|nullable',
            'fields.other_religion' => 'sometimes|string|max:255|nullable',
            'fields.deceased' => 'sometimes|string|max:255|nullable',
            'fields.deceased_date' => 'sometimes|date|nullable',

            // Contact & Family Information
            'fields.familyID' => 'nullable|string|max:255|nullable',
            'fields.head' => 'sometimes|string|max:100|nullable',
            'fields.relation' => 'sometimes|string|max:255|nullable',

            // Address Information
            'fields.barangay_id' => 'required|integer',
            'fields.muncity_id' => 'required|integer',
            'fields.province_id' => 'required|integer',
            'fields.purok_id' => 'sometimes|integer|nullable',
            'fields.sitio_id' => 'sometimes|integer|nullable',
            'fields.purok_name' => 'sometimes|string|max:255|nullable',
            'fields.sitio_name' => 'sometimes|string|max:255|nullable',
            'fields.street_name' => 'sometimes|string|max:255|nullable',

            // Physical Attributes
            'fields.height' => 'required|numeric',
            'fields.weight' => 'required|numeric',

            // Health & Medical Information
            'fields.phicID' => 'sometimes|string|max:100|nullable',
            'fields.nhtsID' => 'sometimes|string|max:100|nullable',
            'fields.ip' => 'sometimes|string|max:255|nullable',
            'fields.hypertension' => 'sometimes|string|max:255|nullable',
            'fields.diabetic' => 'sometimes|string|max:255|nullable',
            'fields.pwd' => 'sometimes|string|max:255|nullable',
            'fields.pregnant' => 'sometimes|string|max:255|nullable',
            'fields.dengvaxia' => 'sometimes|string|max:45|nullable',
            'fields.cancer' => 'sometimes|string|max:255|nullable',
            'fields.cancer_type' => 'sometimes|string|max:255|nullable',
            'fields.mental_med' => 'sometimes|string|max:255|nullable',
            'fields.tbdots_med' => 'sometimes|string|max:255|nullable',
            'fields.cvd_med' => 'sometimes|string|max:255|nullable',
            'fields.covid_status' => 'sometimes|string|max:255|nullable',
            'fields.other_med_history' => 'sometimes|string|max:255|nullable',

            // Women’s Health
            'fields.menarche' => 'sometimes|string|max:255|nullable',
            'fields.menarche_age' => 'sometimes|integer|nullable',
            'fields.sexually_active' => 'sometimes|string|max:10|nullable',
            'fields.fam_plan' => 'sometimes|string|max:10|nullable',
            'fields.fam_plan_method' => 'sometimes|string|max:20|nullable',
            'fields.fam_plan_other_method' => 'sometimes|string|max:255|nullable',
            'fields.fam_plan_status' => 'sometimes|string|max:25|nullable',
            'fields.fam_plan_other_status' => 'sometimes|string|max:255|nullable',

            // Social & Economic Factors
            'fields.income' => 'sometimes|numeric|nullable',
            'fields.unmet' => 'sometimes|numeric|nullable',
            'fields.water' => 'sometimes|integer|nullable',
            'fields.toilet' => 'sometimes|string|max:10|nullable',
            'fields.education' => 'sometimes|string|max:20|nullable',
            'fields.four_ps' => 'sometimes|string|max:255|nullable',
            'fields.fourps_num' => 'sometimes|string|max:30|nullable',
            'fields.nhts' => 'sometimes|string|max:255|nullable',
            'fields.member_others' => 'sometimes|string|max:255|nullable',
            'fields.balik_probinsya' => 'sometimes|string|max:255|nullable',
            'fields.household_num' => 'sometimes|string|max:30|nullable',
            'fields.philhealth_categ' => 'sometimes|string|max:15|nullable',

            // Additional Information
            'fields.newborn_screen' => 'sometimes|string|max:255|nullable',
            'fields.newborn_text' => 'sometimes|string|max:255|nullable',
            'fields.pwd_desc' => 'sometimes|string|max:255|nullable',
            'fields.report_facilityId' => 'sometimes|integer|nullable',
            'fields.Hospital_caseno' => 'sometimes|string|max:100|nullable',

            // Administrative Information
            'fields.nameof_encoder' => 'sometimes|string|max:255|nullable',
            'fields.designation' => 'sometimes|string|max:255|nullable',
            'fields.updated_by' => 'sometimes|string|max:255|nullable',
            'fields.created_at' => 'sometimes|date|nullable',
            'fields.updated_at' => 'sometimes|date|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
        }

        // Extract Profile fields
        $profileData = collect($request->input('fields'))->except([
            'purok_name',
            'sitio_name',
            'street_name'
        ])->toArray();

        // Ensure suffix and other values that can be nullable has empty values
        $profileData['suffix'] = isset($profileData['suffix']) && $profileData['suffix'] !== '' ? $profileData['suffix'] : ' ';
        $profileData['phicID'] = isset($profileData['phicID']) && $profileData['phicID'] !== '' ? $profileData['phicID'] : ' ';
        $profileData['nhtsID'] = isset($profileData['nhtsID']) && $profileData['nhtsID'] !== '' ? $profileData['nhtsID'] : ' ';
        $profileData['unmet'] = isset($profileData['unmet']) && $profileData['unmet'] !== '' ? $profileData['unmet'] : 0;

        $profileData['pregnant'] = isset($profileData['pregnant']) && $profileData['pregnant'] !== '' ? $profileData['pregnant'] : null;
        $profileData['nhts'] = isset($profileData['nhts']) && $profileData['nhts'] !== '' ? $profileData['nhts'] : ' ';
        $profileData['four_ps'] = isset($profileData['four_ps']) && $profileData['four_ps'] !== '' ? $profileData['four_ps'] : ' ';
        $profileData['member_others'] = isset($profileData['member_others']) && $profileData['member_others'] !== '' ? $profileData['member_others'] : ' ';
        $profileData['balik_probinsya'] = isset($profileData['balik_probinsya']) && $profileData['balik_probinsya'] !== '' ? $profileData['balik_probinsya'] : ' ';
        $profileData['household_num'] = isset($profileData['household_num']) && $profileData['household_num'] !== '' ? $profileData['household_num'] : ' ';
        $profileData['philhealth_categ'] = isset($profileData['philhealth_categ']) && $profileData['philhealth_categ'] !== '' ? $profileData['philhealth_categ'] : ' ';
        $profileData['fourps_num'] = isset($profileData['fourps_num']) && $profileData['fourps_num'] !== '' ? $profileData['fourps_num'] : ' ';
        $profileData['fam_plan_method'] = isset($profileData['fam_plan_method']) && $profileData['fam_plan_method'] !== '' ? $profileData['fam_plan_method'] : ' ';
        $profileData['fam_plan_status'] = isset($profileData['fam_plan_status']) && $profileData['fam_plan_status'] !== '' ? $profileData['fam_plan_status'] : ' ';
        $profileData['fam_plan_other_method'] = isset($profileData['fam_plan_other_method']) && $profileData['fam_plan_other_method'] !== '' ? $profileData['fam_plan_other_method'] : ' ';
        $profileData['fam_plan_other_status'] = isset($profileData['fam_plan_other_status']) && $profileData['fam_plan_other_status'] !== '' ? $profileData['fam_plan_other_status'] : ' ';
        $profileData['other_med_history'] = isset($profileData['other_med_history']) && $profileData['other_med_history'] !== '' ? $profileData['other_med_history'] : ' ';

        // Generate unique ID if not provided
        if (empty($profileData['unique_id'])) {
            $profileData['unique_id'] = $this->generateUniqueId(
                $profileData['fname'],
                $profileData['mname'] ?? '',
                $profileData['lname'],
                $profileData['barangay_id'],
                $profileData['muncity_id']
            );
        }

        // Check for duplicate unique ID
        if (Profile::where('unique_id', '=', $profileData['unique_id'])->exists()) {
            Log::notice('Attempted duplicate entry for record: ' + $profileData['fname'] + ' ' + $profileData['lname']);
            Log::notice('Duplicate unique ID: ' + $profileData['unique_id']);
            return response()->json(['status' => 'error', 'message' => 'Profile with this unique ID already exists'], 400);
        }

        // Generate family ID if head of the family
        if (isset($profileData['head']) && strtolower($profileData['head']) === "head") {
            $profileData['familyID'] = $this->generateFamilyId($request);
        }

        try {
            // Create Profile
            $profile = Profile::create($profileData);

            // Extract ProfileOtherDetail fields
            $profileOtherDetailsData = collect($request->input('fields'))->only([
                'purok_name',
                'sitio_name',
                'street_name'
            ])->toArray();

            // Ensure profile_id is assigned even if other fields are empty
            $profileOtherDetailsData['profile_id'] = $profile->id;

            // Save ProfileOtherDetails if at least one address field exists
            if (!empty(array_filter($profileOtherDetailsData))) {
                ProfileOtherDetails::create($profileOtherDetailsData);
            }

            return response()->json(['status' => 'success', 'message' => 'Profile added successfully', 'data' => $profile], 201);
        } catch (Exception $e) {
            Log::error('Error adding profile: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'Error adding profile: ' . $e->getMessage()], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->username);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields', []);

        // Convert ISO 8601 date fields to YYYY-MM-DD format
        if (!empty($fields['dob'])) {
            $fields['dob'] = Carbon::parse($fields['dob'])->toDateString();
        }

        if (!empty($fields['pregnant'])) {
            $fields['pregnant'] = Carbon::parse($fields['pregnant'])->toDateString();
        }

        if (!empty($fields['deceased_date'])) {
            $fields['deceased_date'] = Carbon::parse($fields['deceased_date'])->toDateString();
        }

        // Ensure profile ID is provided
        $profileId = $fields['id'] ?? null;
        if (!$profileId) {
            return response()->json(['status' => 'error', 'message' => 'Profile ID is required'], 400);
        }

        // Check if profile exists
        $profile = Profile::find($profileId);
        if (!$profile) {
            return response()->json(['status' => 'error', 'message' => 'Profile not found'], 404);
        }

        // Validate fields
        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.unique_id' => 'sometimes|string|max:255|unique:profiles,unique_id,' . $profile->id,
            'fields.familyID' => 'sometimes|string|max:255',
            'fields.phicID' => 'sometimes|string|max:100',
            'fields.nhtsID' => 'sometimes|string|max:100',
            'fields.head' => 'sometimes|string|max:100',
            'fields.relation' => 'sometimes|string|max:255',
            'fields.fname' => 'sometimes|string|max:255',
            'fields.mname' => 'sometimes|string|max:255',
            'fields.lname' => 'sometimes|string|max:255',
            'fields.suffix' => 'sometimes|string|max:255',
            'fields.dob' => 'sometimes|date',
            'fields.sex' => 'sometimes|string|max:255',
            'fields.barangay_id' => 'sometimes|integer',
            'fields.muncity_id' => 'sometimes|integer',
            'fields.province_id' => 'sometimes|integer',
            'fields.purok_name' => 'sometimes|string|max:255|nullable',
            'fields.sitio_name' => 'sometimes|string|max:255|nullable',
            'fields.street_name' => 'sometimes|string|max:255|nullable',
            'fields.income' => 'sometimes|integer',
            'fields.unmet' => 'sometimes|integer',
            'fields.water' => 'sometimes|integer',
            'fields.toilet' => 'sometimes|string|max:10',
            'fields.education' => 'sometimes|string|max:20',
            'fields.hypertension' => 'sometimes|string|max:255',
            'fields.diabetic' => 'sometimes|string|max:255',
            'fields.pwd' => 'sometimes|string|max:255',
            'fields.pregnant' => 'sometimes|date',
            'fields.dengvaxia' => 'sometimes|string|max:45',
            'fields.created_at' => 'sometimes|date',
            'fields.updated_at' => 'sometimes|date',
            'fields.sitio_id' => 'sometimes|integer|nullable',
            'fields.purok_id' => 'sometimes|integer|nullable',
            'fields.birth_place' => 'sometimes|string|max:255|nullable',
            'fields.civil_status' => 'sometimes|string|max:255|nullable',
            'fields.religion' => 'sometimes|string|max:255|nullable',
            'fields.other_religion' => 'sometimes|string|max:255|nullable',
            'fields.contact' => 'sometimes|string|max:255|nullable',
            'fields.height' => 'sometimes|numeric',
            'fields.weight' => 'sometimes|numeric',
            'fields.cancer' => 'sometimes|string|max:255|nullable',
            'fields.cancer_type' => 'sometimes|string|max:255|nullable',
            'fields.mental_med' => 'sometimes|string|max:255|nullable',
            'fields.tbdots_med' => 'sometimes|string|max:255|nullable',
            'fields.cvd_med' => 'sometimes|string|max:255|nullable',
            'fields.covid_status' => 'sometimes|string|max:255|nullable',
            'fields.menarche' => 'sometimes|string|max:255|nullable',
            'fields.menarche_age' => 'sometimes|integer|nullable',
            'fields.newborn_screen' => 'sometimes|string|max:255|nullable',
            'fields.newborn_text' => 'sometimes|string|max:255|nullable',
            'fields.deceased' => 'sometimes|string|max:255|nullable',
            'fields.deceased_date' => 'sometimes|date|nullable',
            'fields.pwd_desc' => 'sometimes|string|max:255|nullable',
            'fields.sexually_active' => 'sometimes|string|max:10',
            'fields.nhts' => 'sometimes|string|max:255',
            'fields.four_ps' => 'sometimes|string|max:255',
            'fields.ip' => 'sometimes|string|max:255',
            'fields.member_others' => 'sometimes|string|max:255',
            'fields.balik_probinsya' => 'sometimes|string|max:255',
            'fields.updated_by' => 'sometimes|string|max:10',
            'fields.household_num' => 'sometimes|string|max:30',
            'fields.philhealth_categ' => 'sometimes|string|max:15',
            'fields.fourps_num' => 'sometimes|string|max:30',
            'fields.health_group' => 'sometimes|string|max:20',
            'fields.fam_plan' => 'sometimes|string|max:10',
            'fields.fam_plan_method' => 'sometimes|string|max:20',
            'fields.fam_plan_other_method' => 'sometimes|string|max:255',
            'fields.fam_plan_status' => 'sometimes|string|max:25',
            'fields.fam_plan_other_status' => 'sometimes|string|max:255',
            'fields.other_med_history' => 'sometimes|string',
            'fields.report_facilityId' => 'sometimes|integer|nullable',
            'fields.Hospital_caseno' => 'sometimes|string|max:100|nullable',
            'fields.nameof_encoder' => 'sometimes|string|max:255|nullable',
            'fields.designation' => 'sometimes|string|max:255|nullable'
        ]);

        if ($validator->fails()) {
            Log::error("Validation error in ProfileController (function: updateProfile)");
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
        }

        try {
            // Only update with validated fields
            $profile->update($fields);
            return response()->json(['status' => 'success', 'message' => 'Profile updated successfully.', 'profile' => $profile], 200);
        } catch (Exception $e) {
            Log::error('Error in updating profile: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'Error in updating profile.'], 500);
        }
    }

    // delete profile
    public function deleteProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->username);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        // Validate fields
        $validator = Validator::make($fields, [
            'id' => 'required|integer|exists:profiles,id',
        ]);

        if ($validator->fails()) {
            Log::error("Validation error in ProfileController (function: deleteProfile)");
            return response()->json(['error' => 'Invalid inputs.', 'messages' => $validator->errors()], 422);
        }

        try {
            $profile = Profile::find($fields['id']);

            if (!$profile) {
                return response()->json(['status' => 'error', 'message' => 'Profile not found'], 404);
            }

            $profile->delete();
            return response()->json(['status' => 'success', 'message' => 'Deleted profile.'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting profile: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'Error deleting profile.'], 500);
        }
    }
}
