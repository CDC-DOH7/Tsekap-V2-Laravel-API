<?php

namespace App\Http\Controllers\TsekapV2;

use Illuminate\Http\Request;
use App\Models\TsekapV2\Facilities;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FacilityController extends Controller
{

    // ---- POST FUNCTIONS ----- //
    // get a health facility
    public function retrieveFacilityByCode(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        $fields = $request->input('fields');

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // do not authorize update unless 1, 3, 10
        if ((!$user || ($user->verified !== 1))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $rules = [
            'fields' => 'required|array',
            'fields.facility_code' => 'required|string|max:100',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $facility = Facilities::where('facility_code', "=", $fields['facility_code'])->first();

        return response()->json($facility);
    }

    // add a health facility
    public function addFacility(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        $fields = $request->input('fields');

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // do not authorize adding unless admin
        if ((!$user || !in_array($user->user_priv, [1])) || ($user->verified !== 1)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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
            return response()->json($validator->errors(), 400);
        }

        $facility = Facilities::create($fields);

        return response()->json($facility, 201);
    }

    // update a health facility
    public function updateFacility(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        $fields = $request->input('fields');

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // do not authorize update unless 1, 3, 10
        if ((!$user || !in_array($user->user_priv, [1])) || ($user->verified !== 1)) {
            return response()->json(['error' => 'Unauthorized'], 401);
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

        $facility = Facilities::where('facility_code', "=", $fields['facility_code'])->first();

        if (!$facility) {
            return response()->json(['message' => 'Facilities not found'], 404);
        }

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $facility->update($fields);

        return response()->json($facility);
    }

    public function deleteFacility(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        $fields = $request->input('fields');

        // do not authorize deletion unless 1, 3, 10
        if ((!$user || !in_array($user->user_priv, [1])) || ($user->verified !== 1)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $rules = [
            'fields' => 'required|array',
            'fields.facility_code' => 'required|string|max:100',
        ];

        // Validate request input
        $validator = Validator::make($request->all(), $rules);

        $facility = Facilities::where('facility_code', "=", $fields['facility_code'])->first();

        if (!$facility) {
            return response()->json(['message' => 'Facility not found'], 404);
        }

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $facility->delete();

        return response()->json(['message' => 'Facility deleted successfully']);
    }
}
