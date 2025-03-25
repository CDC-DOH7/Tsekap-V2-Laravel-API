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

    // get nature of injury
    public function getExternalInjury(Request $request)
    {
        $user = $request->user(); // Authentication

        $externalinjury = ExternalInjury::select('id', 'name')->get();
        return response()->json($externalinjury);
    }

}
