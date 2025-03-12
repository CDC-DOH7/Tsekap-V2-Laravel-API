<?php

namespace App\Http\Controllers\TsekapV2;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TsekapV2\Profile;
use Illuminate\Support\Facades\Validator;
use App\Jobs\RetrieveProfileJob;

class ProfileController extends Controller
{
    private function generateUniqueId($fname, $mname, $lname, $barangay_id, $muncity_id)
    {
        return $fname . $mname . $lname . $barangay_id . $muncity_id;
    }

    public function retrieveProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.first_name' => 'nullable|string',
            'fields.middle_name' => 'nullable|string',
            'fields.last_name' => 'nullable|string',
            'fields.dob' => 'nullable|string|date',
            'fields.barangay_id' => 'nullable|integer',
            'fields.municipal_id' => 'nullable|integer',
            'fields.province_id' => 'nullable|integer',
        ]);

        $fields = $request->input('fields');

        $job = new RetrieveProfileJob($fields);
        $result = $job->handle();

        if ($result === 'timeout') {
            return response()->json([
                'message' => 'Request timed out. Please try again.',
            ], 408); // HTTP 408 Request Timeout
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

    // add profile
    public function addProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if the user has admin privileges
        if ($user->user_priv != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validation rules
        $rules = [
            'unique_id' => 'required|unique:profiles',
            'familyID' => 'required',
            'phicID' => 'required',
            'nhtsID' => 'required',
            'head' => 'required',
            'relation' => 'required',
            'fname' => 'required',
            'mname' => 'required',
            'lname' => 'required',
            'suffix' => 'required',
            'dob' => 'required|date',
            'sex' => 'required',
            'barangay_id' => 'required|integer',
            'muncity_id' => 'required|integer',
            'province_id' => 'required|integer',
            'income' => 'required|integer',
            'unmet' => 'required|integer',
            'water' => 'required|integer',
            'toilet' => 'required|string|max:10',
            'education' => 'required|string|max:20',
            'hypertension' => 'required',
            'diabetic' => 'required',
            'pwd' => 'required',
            'pregnant' => 'required|date',
            'dengvaxia' => 'required|string|max:45',
            'sexually_active' => 'required|string|max:10',
            'nhts' => 'required',
            'four_ps' => 'required',
            'ip' => 'required',
            'member_others' => 'required',
            'balik_probinsya' => 'required',
            'updated_by' => 'required|string|max:10',
            'household_num' => 'required|string|max:30',
            'philhealth_categ' => 'required|string|max:15',
            'fourps_num' => 'required|string|max:30',
            'health_group' => 'required|string|max:20',
            'fam_plan' => 'required|string|max:10',
            'fam_plan_method' => 'required|string|max:20',
            'fam_plan_other_method' => 'required',
            'fam_plan_status' => 'required|string|max:25',
            'fam_plan_other_status' => 'required',
            'other_med_history' => 'required',
        ];

        // Validate input
        $validator = Validator::make($request->fields, $rules);

        // Check for validation errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Generate unique ID
        $unique_id = $this->generateUniqueId(
            $request->input('fname'),
            $request->input('mname'),
            $request->input('lname'),
            $request->input('barangay_id'),
            $request->input('muncity_id')
        );

        // Check if unique ID already exists
        if (Profile::where('unique_id', "=", $unique_id)->exists()) {
            return response()->json(['message' => 'Profile with this unique ID already exists'], 400);
        }

        // Create new profile with unique ID
        $profile = Profile::create(array_merge($request->all(), ['unique_id' => $unique_id]));

        return response()->json(['message' => 'Profile added successfully', 'profile' => $profile], 201);
    }


    public function updateProfile(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $request->user();

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
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

        // Check if the user has admin privileges
        if (!in_array($user->user_priv, [1, 3, 10])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Ensure profile ID is provided
        $profileId = $fields['id'] ?? null;
        if (!$profileId) {
            return response()->json(['message' => 'Profile ID is required'], 400);
        }

        // Check if profile exists
        $profile = Profile::find($profileId);
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
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
            return response()->json(['error' => 'Invalid inputs.', 'messages' => $validator->errors()], 422);
        }

        try {
            // Only update with validated fields
            $profile->update($fields);

            return response()->json(['message' => 'Profile updated successfully', 'profile' => $profile], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    // delete profile
    public function deleteProfile(Request $request)
    {
        $fields = $request->input('fields');

        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if the user has admin privileges
        if ($user->user_priv != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $profile = Profile::find($fields['id']);
        $profile->delete();

        return response()->json(['message' => 'Deleted profile.'], 200);
    }
}
