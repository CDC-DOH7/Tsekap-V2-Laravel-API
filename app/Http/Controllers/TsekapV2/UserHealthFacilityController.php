<?php

namespace App\Http\Controllers\TsekapV2;

use Illuminate\Http\Request;
use App\Models\TsekapV2\UserHealthFacility;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class UserHealthFacilityController extends Controller
{
    private function getAuthenticatedUser($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser) {
            Log::error('Denied access for: ' + $queryUser->id);
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        return $queryUser;
    }

    public function retrieveUserHealthFacility(Request $request)
    {
        $fields = $request->input('fields');

        $user = $this->getAuthenticatedUser($request->user()->username);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        if ($user->user_priv != 1) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($fields, [
            'user_id' => 'required|integer',
            'facility_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        try {
            $userHealthFacility = UserHealthFacility::where('user_id', '=', $fields['user_id'])
                ->where('facility_id', '=', $fields['facility_id'])
                ->first();

            if (!$userHealthFacility) {
                return response()->json(['status' => 'error', 'message' => 'Mapping not found'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $userHealthFacility]);
        } catch (Exception $e) {
            Log::error('Error retrieving user health facility: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    public function addUserHealthFacility(Request $request)
    {
        $fields = $request->input('fields');

        $user = $this->getAuthenticatedUser($request->user()->username);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.user_id' => 'required|integer',
            'fields.facility_id' => 'required|integer',
            'fields.user_designation' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
        }

        try {
            $existingMapping = UserHealthFacility::where('user_id', '=', $fields['user_id'])
                ->where('facility_id', '=', $fields['facility_id'])
                ->first();

            if ($existingMapping) {
                return response()->json(['status' => 'error', 'message' => 'Mapping already exists'], 409);
            }

            $userHealthFacility = UserHealthFacility::create($fields);

            return response()->json(['status' => 'success', 'data' => $userHealthFacility], 201);
        } catch (Exception $e) {
            Log::error('Error adding user health facility: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    public function updateUserHealthFacility(Request $request)
    {
        $fields = $request->input('fields');

        $user = $this->getAuthenticatedUser($request->user()->username);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        if ($user->user_priv != 1) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.user_id' => 'required|integer',
            'fields.facility_id' => 'required|integer',
            'fields.new_facility_id' => 'nullable|integer',
            'fields.user_designation' => 'nullable|string|max:255',
            'fields.assigned_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        try {
            $userHealthFacility = UserHealthFacility::where('user_id', '=', $fields['user_id'])
                ->where('facility_id', '=', $fields['facility_id'])
                ->first();

            if (!$userHealthFacility) {
                return response()->json(['status' => 'error', 'message' => 'Mapping not found'], 404);
            }

            if (array_key_exists('user_designation', $fields)) {
                $userHealthFacility->user_designation = $fields['user_designation'];
            }

            if (array_key_exists('assigned_at', $fields)) {
                $userHealthFacility->assigned_at = $fields['assigned_at'];
            }

            if (array_key_exists('new_facility_id', $fields)) {
                $userHealthFacility->facility_id = $fields['new_facility_id'];
            }

            $userHealthFacility->save();

            return response()->json(['status' => 'success', 'data' => $userHealthFacility]);
        } catch (Exception $e) {
            Log::error('Error updating user health facility: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    public function deleteUserHealthFacility(Request $request)
    {
        $fields = $request->input('fields');

        $user = $this->getAuthenticatedUser($request->user()->username);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        if ($user->user_priv != 1) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($fields, [
            'user_id' => 'required|integer',
            'facility_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        try {
            $userHealthFacility = UserHealthFacility::where('user_id', '=', $fields['user_id'])
                ->where('facility_id', '=', $fields['facility_id'])
                ->first();

            if (!$userHealthFacility) {
                return response()->json(['status' => 'error', 'message' => 'Mapping not found'], 404);
            }

            $userHealthFacility->delete();

            return response()->json(['status' => 'success', 'message' => 'Mapping deleted successfully']);
        } catch (Exception $e) {
            Log::error('Error deleting user health facility: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }
}
