<?php

namespace App\Http\Controllers\TsekapV2\Misc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TsekapV2\Facilities;
use App\Models\TsekapV2\Muncity;
use App\Models\TsekapV2\Province;
use App\Models\TsekapV2\Barangay;

class MiscDataController extends Controller
{
    // get facilities
    public function getAllFacility(Request $request)
    {
        $province = $request->query('province');
        $municipality = $request->query('muncity');

        $query = Facilities::select(
            'id',
            'facility_code',
            'name',
            'latitude',
            'longitude',
            'abbr',
            'address',
            'brgy',
            'muncity',
            'province',
            'contact',
            'email',
            'status',
            'level',
            'hospital_type',
            'referral_used'
        );

        if ($province) {
            $query->where('province', $province);
        }

        if ($municipality) {
            $query->where('muncity', $municipality);
        }

        // Log the query for debugging
        \Log::info('Facilities Query:', [
            'query' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);

        $facilities = $query->get();

        return response()->json($facilities);
    }

    // get province
    public function getProvince()
    {
        $province = Province::select('id', 'description')->get();
        return response()->json($province);
    }
    
    // get muncityity/city
    public function getMuncity(Request $request)
    {
        $provinceId = $request->query('province_id');

        $muncity = Muncity::where('province_id', '=', $provinceId)
            ->select('id', 'province_id', 'description')
            ->get();

        return response()->json($muncity);
    }

    // get barangay
    public function getBarangay(Request $request)
    {
        $muncityId = $request->query('muncity_id');

        $barangay = Barangay::where('muncity_id', '=', $muncityId)
            ->select('id', 'muncity_id', 'description')
            ->get();
        return response()->json($barangay);
    }

    // get all muncityities/cities
    public function getAllMuncities()
    {
        $muncities = Muncity::select('id', 'province_id', 'description')->get();
        return response()->json($muncities);
    }

    // get all barangays
    public function getAllBarangays()
    {
        $muncities = Barangay::select('id', 'province_id', 'muncity_id', 'description')->get();
        return response()->json($muncities);
    }
}
