<?php

namespace App\Http\Controllers\TsekapV2\Misc;

use App\Http\Controllers\Controller;
use App\Models\TsekapV2\Citizenship;
use App\Models\TsekapV2\Religion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TsekapV2\Facilities;
use App\Models\TsekapV2\Muncity;
use App\Models\TsekapV2\Province;
use App\Models\TsekapV2\Barangay;
use Illuminate\Support\Facades\Validator;
use Exception;

class MiscDataController extends Controller
{
    // get facilities
    public function getAllFacility(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'province_id' => 'nullable|integer',
            'muncity_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $province = $request->query('province_id');
        $municipality = $request->query('muncity_id');

        $query = Facilities::select(
            'id',
            'facility_code',
            'name',
            'abbr',
            'brgy',
            'muncity',
            'province',

            // --- redacted/optional fields ---

            // 'address',
            // 'latitude',
            // 'longitude',
            // 'contact',
            // 'email',
            // 'status',
            // 'level',
            // 'hospital_type',
            // 'referral_used'
        );

        if ($province) {
            $query->where('province', "=", $province);
        }

        if ($municipality) {
            $query->where('muncity', "=", $municipality);
        }

        try {
            $facilities = $query->get();
            return response()->json($facilities);
        } catch (Exception $e) {
            Log::error('Error retrieving facilities.' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred while retrieving facilities.'], 500);
        }
    }

    // get all facilities in current muncity
    public function getFacilitiesInCurrentMuncity(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'muncity_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        try {
            $muncityId = $request->query('muncity_id');
            $facilities = Facilities::where('muncity_id', '=', $muncityId)
                ->select('id', 'facility_code', 'name')
                ->get();
            return response()->json($facilities);
        } catch (Exception $e) {
            Log::error('Error retrieving facilities current muncity.' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error retrieving facilities in current muncity.'], 500);
        }
    }

    // get all provinces 
    public function getProvinces()
    {
        try {
            $provinces = Province::select('id', 'description')->get();
            return response()->json($provinces);
        } catch (Exception $e) {
            Log::error('Error in retrieving provinces.' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error in retrieving provinces.'], 500);
        }
    }

    // get muncity/city by provinces
    public function getMuncities(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'province_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        try {
            $provinceId = $request->query('province_id');
            $muncity = Muncity::where('province_id', '=', $provinceId)
                ->select('id', 'province_id', 'description')
                ->get();

            return response()->json($muncity);
        } catch (Exception $e) {
            Log::error('Error in retrieving muncities.' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error in retrieving muncities.'], 500);
        }
    }

    // get all muncities
    public function getAllMuncities(Request $request)
    {
        $muncities = Muncity::select('id', 'province_id', 'description')->get();
        return response()->json($muncities);
    }

    // get barangay by muncity/cities
    public function getBarangays(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'muncity_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        try {
            $muncityId = $request->query('muncity_id');
            $barangay = Barangay::where('muncity_id', '=', $muncityId)
                ->select('id', 'muncity_id', 'description')
                ->get();

            return response()->json($barangay);
        } catch (Exception $e) {
            Log::error('Error in retrieving barangays.' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error in retrieving barangays.'], 500);
        }
    }

    // get all barangays
    public function getAllBarangays(Request $request)
    {
        $barangays = Barangay::select('id', 'muncity_id', 'description')->get();
        return response()->json($barangays);
    }

    // get province by Id 
    public function getProvinceById(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'province_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        try {
            $provinceId = $request->query('province_id');
            $province = Muncity::where('id', '=', $provinceId)
                ->select('id', 'description')
                ->get();

            return response()->json($province);
        } catch (Exception $e) {
            Log::error('Error in retrieving province.' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error in retrieving province.'], 500);
        }
    }

    // get muncity/city by Id
    public function getMuncityById(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'muncity_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        try {
            $muncityId = $request->query('muncity_id');
            $muncity = Muncity::where('id', '=', $muncityId)
                ->select('id', 'description')
                ->get();

            return response()->json($muncity);
        } catch (Exception $e) {
            Log::error('Error in retrieving muncity.' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error in retrieving muncity.'], 500);
        }
    }

    // get barangay by Id
    public function getBarangayById(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'barangay_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        try {
            $barangayId = $request->query('barangay_id');
            $barangay = Barangay::where('id', '=', $barangayId)
                ->select('id', 'description')
                ->get();

            return response()->json($barangay);
        } catch (Exception $e) {
            Log::error('Error in retrieving barangay.' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error in retrieving barangay.'], 500);
        }
    }

    // get all religion
    public function getAllReligions()
    {
        try {
            $religions = Religion::select('id', 'name')->get();
            return response()->json($religions);
        } catch (Exception $e) {
            Log::error('Error in retrieving religions.' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error in retrieving religions.'], 500);
        }
    }

    // get all religion
    public function getAllCitizenships()
    {
        try {
            $citizenships = Citizenship::select('id', 'name')->get();
            return response()->json($citizenships);
        } catch (Exception $e) {
            Log::error('Error in retrieving citizenships.' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error in retrieving citizenships.'], 500);
        }
    }
}
