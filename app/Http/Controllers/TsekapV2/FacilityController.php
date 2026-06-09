<?php

namespace App\Http\Controllers\TsekapV2;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TsekapV2\Facilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FacilityController extends Controller
{
    private function getAuthenticatedUser(?string $username): JsonResponse|User
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->getAttribute('verified') !== 1) {
            Log::error('Denied access for: ' . " " . $queryUser->getAttribute('id'));
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    private function getAuthenticatedAdmin(?string $username): JsonResponse|User
    {
        $queryUser = User::where('username', '=', $username)->first();

        // do not authorize update unless 1, 3, 5, 10
        if ((!$queryUser || !in_array($queryUser->getAttribute('user_priv'), [1, 3, 5, 10])) || ($queryUser->getAttribute('verified') !== 1)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    // get a health facility
    public function retrieveFacilityByCode(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username'));
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $rules = [
            'fields' => 'required|array',
            'fields.id' => 'sometimes|required_without:fields.facility_code|integer',
            'fields.facility_code' => 'sometimes|required_without:fields.id|string|max:100',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $fields = $request->input('fields');

        try {
            if (isset($fields['id']) && $fields['id'] !== null) {
                $facility = Facilities::where('id', $fields['id'])->first();
            } else {
                $facility = Facilities::where('facility_code', $fields['facility_code'])->first();
            }

            if (!$facility) {
                return response()->json(['status' => 'error', 'message' => 'Facility not found'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $facility], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving facility: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    // add a health facility
    public function addFacility(Request $request): JsonResponse
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username')); // This replaces Auth::check()
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        $rules = [
            'fields' => 'required|array',
            'fields.facility_code' => 'required|string|max:100',
            'fields.name' => 'required|string|max:255',
            'fields.latitude' => 'nullable|string|max:255',
            'fields.longitude' => 'nullable|string|max:255',
            'fields.abbr' => 'required|string|max:255',
            'fields.address' => 'required|string|max:255',
            'fields.brgy' => 'required|integer',
            'fields.muncity' => 'required|integer',
            'fields.province' => 'required|integer',
            'fields.contact' => 'required|string|max:255',
            'fields.email' => 'required|string|email|max:255',
            'fields.status' => 'required|integer',
            'fields.picture' => 'nullable|string|max:255',
            'fields.chief_hospital' => 'nullable|string|max:100',
            'fields.level' => 'nullable|string|max:255',
            'fields.hospital_type' => 'nullable|string|max:45',
            'fields.tricity_id' => 'nullable|integer',
            'fields.referral_used' => 'nullable|string|max:45',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $fields = $request->input('fields');

        if (Facilities::where('facility_code', $fields['facility_code'])->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Facility with this code already exists'], 400);
        }

        try {
            $facility = Facilities::create($fields);

            return response()->json(['status' => 'success', 'message' => 'Facility added successfully', 'data' => $facility], 201);
        } catch (\Exception $e) {
            Log::error('Error adding facility: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    // update a health facility
    public function updateFacility(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username'));
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $rules = [
            'fields' => 'required|array',
            'fields.facility_code' => 'required|string|max:100',
            'fields.name' => 'sometimes|required|string|max:255',
            'fields.latitude' => 'nullable|string|max:255',
            'fields.longitude' => 'nullable|string|max:255',
            'fields.abbr' => 'sometimes|required|string|max:255',
            'fields.address' => 'sometimes|required|string|max:255',
            'fields.brgy' => 'sometimes|required|integer',
            'fields.muncity' => 'sometimes|required|integer',
            'fields.province' => 'sometimes|required|integer',
            'fields.contact' => 'sometimes|required|string|max:255',
            'fields.email' => 'sometimes|required|string|email|max:255',
            'fields.status' => 'sometimes|required|integer',
            'fields.picture' => 'nullable|string|max:255',
            'fields.chief_hospital' => 'nullable|string|max:100',
            'fields.level' => 'nullable|string|max:255',
            'fields.hospital_type' => 'nullable|string|max:45',
            'fields.tricity_id' => 'nullable|integer',
            'fields.referral_used' => 'nullable|string|max:45',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $fields = $request->input('fields');

        try {
            $facility = Facilities::where('facility_code', $fields['facility_code'])->first();

            if (!$facility) {
                return response()->json(['status' => 'error', 'message' => 'Facility not found'], 404);
            }

            $facility->update($fields);

            return response()->json(['status' => 'success', 'message' => 'Facility updated successfully', 'data' => $facility], 200);
        } catch (\Exception $e) {
            Log::error('Error updating facility: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    public function deleteFacility(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username'));
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $rules = [
            'fields' => 'required|array',
            'fields.facility_code' => 'required|string|max:100',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $fields = $request->input('fields');

        try {
            $facility = Facilities::where('facility_code', $fields['facility_code'])->first();

            if (!$facility) {
                return response()->json(['status' => 'error', 'message' => 'Facility not found'], 404);
            }

            $facility->delete();

            return response()->json(['status' => 'success', 'message' => 'Facility deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting facility: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }
}
