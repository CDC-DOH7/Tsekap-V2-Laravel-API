<?php

namespace App\Http\Controllers\TsekapV2\Forms\PatientInjuryForm;

use App\Http\Controllers\Controller;
use App\Models\TsekapV2\Citizenship;
use App\Models\TsekapV2\Religion;

use Illuminate\Http\Request;
use Exception;

use App\Models\TsekapV2\Facilities;
use App\Models\TsekapV2\Muncity;
use App\Models\TsekapV2\Barangay;

use App\Models\TsekapV2\Injury\ListModels\BodyParts;
use App\Models\TsekapV2\Injury\ListModels\ExternalInjury;
use App\Models\TsekapV2\Injury\ListModels\HospitalFacility;
use App\Models\TsekapV2\Injury\ListModels\NatureOfInjury;
use App\Models\TsekapV2\Injury\ListModels\TransportAccident;
use App\Models\TsekapV2\Injury\ListModels\TransportSafetyMeasure;

class InjuryListController extends Controller
{

    // get body parts
    public function getBodyParts(Request $request)
    {
        $user = $request->user(); // Authentication

        $bodyparts = BodyParts::select('id', 'name')->get();
        return response()->json($bodyparts);
    }

    // get nature of injury
    public function getNatureInjury(Request $request)
    {
        $user = $request->user(); // Authentication

        $natureinjury = NatureOfInjury::select('id', 'name')->get();
        return response()->json($natureinjury);
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

    // get barangay by muncity/cities
    public function getBarangays(Request $request)
    {
        $muncityId = $request->query('muncity_id');

        $barangay = Barangay::where('muncity_id', '=', $muncityId)
            ->select('id', 'muncity_id', 'description')
            ->get();
        return response()->json($barangay);
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
