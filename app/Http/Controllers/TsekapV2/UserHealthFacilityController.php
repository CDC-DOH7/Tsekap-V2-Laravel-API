<?php

namespace App\Http\Controllers\TsekapV2;

use Illuminate\Http\Request;
use App\Models\TsekapV2\UserHealthFacility;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserHealthFacilityController extends Controller
{
    // Get a user-health facility mapping
    public function retrieveUserHealthFacility(Request $request)
    {
        $fields = $request->input('fields');

        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($fields, [
            'user_id' => 'required|integer',
            'facility_id' => 'required|integer',
            'user_designation' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $userHealthFacility = UserHealthFacility::where('user_id', "=", $fields['user_id'])
            ->where('facility_id', $fields['facility_id'])
            ->first();

        if (!$userHealthFacility) {
            return response()->json(['message' => 'Mapping not found'], 404);
        }

        return response()->json($userHealthFacility);
    }

    // add a user-health facility mapping
    public function addUserHealthFacility(Request $request)
    {
        $fields = $request->input('fields');

        // Check if the fields array is provided
        if (!$fields) {
            return response()->json(['error' => 'Invalid input: fields are required'], 400);
        }

        $rules = [
            'fields' => 'required|array',
            'fields.user_id' => 'required|integer',
            'fields.facility_id' => 'required|integer',
            'fields.user_designation' => 'string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $existingMapping = UserHealthFacility::where('user_id', "=", $fields['user_id'])
            ->where('facility_id', $fields['facility_id'])
            ->first();

        if ($existingMapping) {
            return response()->json(['message' => 'Mapping already exists'], 409);
        }

        $userHealthFacility = UserHealthFacility::create($fields);
        return response()->json($userHealthFacility, 201);
    }

    // Update a user-health facility mapping
    public function updateUserHealthFacility(Request $request)
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


        // Check if the fields array is provided
        if (!$fields) {
            return response()->json(['error' => 'Invalid input: fields are required'], 400);
        }

        $rules = [
            'fields' => 'required|array',
            'fields.user_id' => 'required|integer',
            'fields.facility_id' => 'required|integer',
            'fields.new_facility_id' => 'integer',
            'fields.user_designation' => 'string|max:255',
            'fields.assigned_at' => 'date',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Find the existing mapping
        $userHealthFacility = UserHealthFacility::where('user_id', "=", $fields['user_id'])
            ->where('facility_id', $fields['facility_id'])
            ->first();

        if (!$userHealthFacility) {
            return response()->json(['message' => 'Mapping not found'], 404);
        }

        // Update fields, including resetting the facility_id
        if (array_key_exists('user_designation', $fields)) {
            $userHealthFacility->user_designation = $fields['user_designation'];
        }

        if (array_key_exists('assigned_at', $fields)) {
            $userHealthFacility->assigned_at = $fields['assigned_at'];
        }

        // Reset the facility_id if a new one is provided
        if (array_key_exists('facility_id', $fields)) {
            $userHealthFacility->facility_id = $fields['new_facility_id'];
        }

        // Save the updated record
        $userHealthFacility->save();

        return response()->json([
            'message' => 'User health facility updated successfully',
            'data' => $userHealthFacility,
        ]);
    }


    // delete a user-health facility mapping
    public function deleteUserHealthFacility(Request $request)
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

        $userHealthFacility = UserHealthFacility::where('user_id', "=", $fields['user_id'])
            ->where('facility_id', $fields['facility_id'])->first();

        if (!$userHealthFacility) {
            return response()->json(['message' => 'Mapping not found'], 404);
        }

        $userHealthFacility->delete();

        return response()->json(['message' => 'Mapping deleted']);
    }
}
