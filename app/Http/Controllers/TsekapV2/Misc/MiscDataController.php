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

class MiscDataController extends Controller
{
    // get facilities
    public function getAllFacility(Request $request)
    {
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

            // redacted fields:
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

        // Log the query for debugging
        Log::info('Facilities Query:', [
            'query' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);

        $facilities = $query->get();

        return response()->json($facilities);
    }

    public function getFacilitiesInCurrentMuncity(Request $request)
    {
        $muncityId = $request->query('muncity_id');

        $facilities = Facilities::where('muncity_id', '=', $muncityId)
            ->select('id', 'facility_code', 'name')
            ->get();
        return response()->json($facilities);
    }

    // get all provinces 
    public function getProvinces()
    {
        $provinces = Province::select('id', 'description')->get();
        return response()->json($provinces);
    }

    // get muncity/city by provinces
    public function getMuncities(Request $request)
    {
        $provinceId = $request->query('province_id');

        $muncity = Muncity::where('province_id', '=', $provinceId)
            ->select('id', 'province_id', 'description')
            ->get();

        return response()->json($muncity);
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
        $muncityId = $request->query('muncity_id');

        $barangay = Barangay::where('muncity_id', '=', $muncityId)
            ->select('id', 'muncity_id', 'description')
            ->get();
        return response()->json($barangay);
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

        $provinceId = $request->query('province_id');

        $province = Muncity::where('id', '=', $provinceId)
            ->select('id', 'description')
            ->get();

        return response()->json($province);
    }

    // get muncity/city by Id
    public function getMuncityById(Request $request)
    {
        $muncityId = $request->query('muncity_id');

        $muncity = Muncity::where('id', '=', $muncityId)
            ->select('id', 'description')
            ->get();

        return response()->json($muncity);
    }

    // get barangay by Id
    public function getBarangayById(Request $request)
    {
        $barangayId = $request->query('barangay_id');

        $barangay = Barangay::where('id', '=', $barangayId)
            ->select('id', 'description')
            ->get();
        return response()->json($barangay);
    }

    // get all religion
    public function getAllReligions()
    {
        $religions = Religion::select('id', 'name')->get();
        return response()->json($religions);
    }

    // get all religion
    public function getAllCitizenships()
    {
        $citizenships = Citizenship::select('id', 'name')->get();
        return response()->json($citizenships);
    }
}
