<?php

namespace App\Http\Controllers\TsekapV2;

use Illuminate\Http\Request;
use App\Models\TsekapV2\Facilities;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class FacilityController extends Controller{

    // ---- POST FUNCTIONS ----- //
    // get a health facility
    public function retrieveFacilityByCode(Request $request){
        $fields = $request->input('fields');

        // check authentication if user is logged in
        if(!Auth::check()){
            return response()->json(['error' => 'Unauthorized'], 401);
        }        
       
        // get user
        $user = Auth::user();

        // do not allow access unless no user is logged in.
        if(!$user){
            return response()->json(['error' => 'No user is logged in.'], 401);
        }

        // do not authorize update unless admin
        if($user['user_priv'] != 1){
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $facility = Facilities::find($fields['facility_code']);

        return response()->json($facility);
    }

    // add a health facility
    public function addFacility(Request $request){
        $fields = $request->input('fields');

        // check authentication if user is logged in
        if(!Auth::check()){
            return response()->json(['error' => 'Unauthorized'], 401);
        }        
       
        // get user
        $user = Auth::user();       
 
        // do not authorize update unless admin
        if ($user['user_priv'] != 1) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $rules = [
            'facility_code' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'latitude' => 'nullable|string|max:255',
            'longitude' => 'nullable|string|max:255',
            'abbr' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'brgy' => 'required|integer',
            'muncity' => 'required|integer',
            'province' => 'required|integer',
            'contact' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'status' => 'required|integer',
            'picture' => 'nullable|string|max:255',
            'chief_hospital' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:255',
            'hospital_type' => 'nullable|string|max:45',
            'tricity_id' => 'nullable|integer',
            'referral_used' => 'nullable|string|max:45',
        ];

        $validator = Validator::make($fields, $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $facility = Facilities::create($fields);

        return response()->json($facility, 201);
    }

    // update a health facility
    public function updateFacility(Request $request){
        $fields = $request->input('fields');

        // check authentication if user is logged in
        if(!Auth::check()){
            return response()->json(['error' => 'Unauthorized'], 401);
        }        
       
        // get user
        $user = Auth::user();       

        // do not allow access unless no user is logged in.
        if(!$user){
            return response()->json(['error' => 'No user is logged in.'], 401);
        }

        // do not authorize update unless admin
        if($user['user_priv'] != 1){
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $facility = Facilities::find($fields['facility_code']);

        if (!$facility) {
            return response()->json(['message' => 'Facilities not found'], 404);
        }

        $rules = [
            'facility_code' => 'nullable|string|max:100',
            'name' => 'sometimes|required|string|max:255',
            'latitude' => 'nullable|string|max:255',
            'longitude' => 'nullable|string|max:255',
            'abbr' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'brgy' => 'sometimes|required|integer',
            'muncity' => 'sometimes|required|integer',
            'province' => 'sometimes|required|integer',
            'contact' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255',
            'status' => 'sometimes|required|integer',
            'picture' => 'nullable|string|max:255',
            'chief_hospital' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:255',
            'hospital_type' => 'nullable|string|max:45',
            'tricity_id' => 'nullable|integer',
            'referral_used' => 'nullable|string|max:45',
        ];

        $validator = Validator::make($fields, $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $facility->update($fields);

        return response()->json($facility);
    }

    // delete a health facility
    public function deleteFacility(Request $request){
        $fields = $request->input('fields');

        // check authentication if user is logged in
        if(!Auth::check()){
            return response()->json(['error' => 'Unauthorized'], 401);
        }        
       
        // get user
        $user = Auth::user();

        // do not allow access unless no user is logged in.
        if(!$user){
            return response()->json(['error' => 'No user is logged in.'], 401);
        }

        // do not authorize update unless admin
        if($user['user_priv'] != 1){
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $facility = Facilities::find($fields['facility_code']);

        if (!$facility) {
            return response()->json(['message' => 'Facilities not found'], 404);
        }

        // delete facility
        $facility->delete();

        return response()->json(['message' => 'Facilities deleted']);
    }
}
