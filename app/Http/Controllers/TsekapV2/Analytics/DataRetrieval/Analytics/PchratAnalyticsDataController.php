<?php

namespace App\Http\Controllers\TsekapV2\Analytics\DataRetrieval\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

class PchratAnalyticsDataController extends Controller

{
    // ================== 1. VISIT INFO SUMMARY FUNCTIONS (/visit_info_summary) ==================
    // Controller Count: 3
    public function getNatureOfVisitRecords(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json(['error' => 'hf_id, start_date, and end_date are required.'], 400);
        }

        try {
            // Only fetching the single AR complaints column with multiple answers
            $records = DB::table('pch_risk_assessment_tool_form as f')
                ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                ->select(
                    'f.pch_profile_id',
                    'f.nature_of_visit', // your new column containing multiple values like "SC, MV, DH, BE"
                    'f.created_at'
                )
                ->where('p.facility_id_updated', '=', $hf_id)
                ->whereBetween('f.created_at', [$start_date, $end_date])
                ->get();

            return response()->json($records);
        } catch (Exception $e) {
            Log::error("Error fetching nature of visit records: " . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve nature of visit data.'], 500);
        }
    }
        public function getNatureOfVisitRiskProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            // PCHRAT Risk Assessment Form
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching pch risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getVisitInfoSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_form as f')
                ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                ->select(
                    'f.pch_profile_id',
                    'f.nature_of_visit',
                    'f.nature_of_visit_duration',
                    'f.type_of_consultation',
                    'f.created_at'
                )
                ->where('p.facility_id_updated', "=", $hf_id)
                ->whereBetween('f.created_at', [$start_date, $end_date])
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching visit info data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve visit info data.'
            ], 500);
        }
    }

    public function getVisitInfoSummaryProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getVisitInfoSummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->select('id', 'sex', 'age_bracket_id')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching pchrat profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END VISIT INFO SUMMARY FUNCTIONS ==================

    // ================== 2. VITAL SIGNS SUMMARY FUNCTIONS (/vital_signs_summary) ==================
    // Controller Count: 3
    public function getVitalSignsSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_form as f')
                ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                ->select(
                    'f.pch_profile_id',
                    'f.vit_bp_systolic',
                    'f.vit_bp_diastolic',
                    'f.vit_oxygen_saturation',
                    'f.vit_heart_rate_or_pulse_rate',
                    'f.vit_is_normal_rate',
                    'f.vit_is_regular_rhythm',
                    'f.vit_respiration_rate',
                    'f.vit_temperature',
                    'f.vit_weight',
                    'f.vit_height',
                    'f.vit_bmi',
                    'f.vit_chief_complaint',
                    'f.vit_history_of_present_illness_and_remarks',
                    'f.created_at'
                )
                ->where('p.facility_id_updated', "=", $hf_id)
                ->whereBetween('f.created_at', [$start_date, $end_date])
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching visit info data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve visit info data.'
            ], 500);
        }
    }

    public function getVitalSignsSummaryProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getVitalSignsSummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->select('id', 'sex', 'age_bracket_id')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching pchrat profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END VITAL SIGNS SUMMARY FUNCTIONS ==================

    // ================== 3. PHYSICAL EXAMINATION SUMMARY FUNCTIONS (/physical_exam_summary) ==================
    // Controller Count: 3
    public function getPhysicalExamSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_form as f')
                ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                ->select(
                    'f.pch_profile_id',
                    'f.pe_skin_extremities_description',
                    'f.pe_heent_description',
                    'f.pe_chest_description',
                    'f.pe_heart',
                    'f.pe_abdomen',
                    'f.pe_alert_type',
                    'f.pe_description',
                    'f.created_at'
                )
                ->where('p.facility_id_updated', "=", $hf_id)
                ->whereBetween('f.created_at', [$start_date, $end_date])
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching visit info data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve visit info data.'
            ], 500);
        }
    }

    public function getPhysicalExamSummaryProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getPhysicalExamSummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->select('id', 'sex', 'age_bracket_id')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching pchrat profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END PHYSICAL EXAM SUMMARY FUNCTIONS ==================

    // ================== 4. ANIMAL BITE SUMMARY FUNCTIONS (/animal_bite_summary) ==================
    // Controller Count: 3
    public function getAnimalBiteSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_form as f')
                ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                ->select(
                    'f.pch_profile_id',
                    'f.ab_anatomical_location',
                    'f.ab_anatomical_location_others_specify',
                    'f.ab_animal_type',
                    'f.ab_animal_type_others_specify',
                    'f.ab_description_of_event',
                    'f.ab_type_of_exposure',
                    'f.ab_wash_bite',
                    'f.ab_date_of_exposure',
                    'f.created_at'
                )
                ->where('p.facility_id_updated', "=", $hf_id)
                ->whereBetween('f.created_at', [$start_date, $end_date])
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching visit info data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve visit info data.'
            ], 500);
        }
    }

    public function getAnimalBiteSummaryProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getAnimalBiteSummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->select('id', 'sex', 'age_bracket_id')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching pchrat profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END ANIMAL BITE SUMMARY FUNCTIONS ==================

    // ================== 5. RISK ASSESSMENT SUMMARY FUNCTIONS (/risk_assessment_summary) ==================
    // Controller Count: 3
    public function getRiskAssessmentSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_form as f')
                ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                ->select(
                    'f.pch_profile_id',
                    'f.comorbidities',
                    'f.comorbidities_others',
                    'f.created_at'
                )
                ->where('p.facility_id_updated', "=", $hf_id)
                ->whereBetween('f.created_at', [$start_date, $end_date])
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching visit info data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve visit info data.'
            ], 500);
        }
    }

    public function getRiskAssessmentSummaryProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getRiskAssessmentSummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->select('id', 'sex', 'age_bracket_id')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching pchrat profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END RISK ASSESSMENT SUMMARY FUNCTIONS ==================

    // ================== 6. PERSONAL HISTORY SUMMARY FUNCTIONS (/risk_assessment_summary) ==================
    // Controller Count: 3
    public function getPersonalHistorySummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_form as f')
                ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                ->select(
                    'f.pch_profile_id',
                    'f.ph_immunization_record_child',
                    'f.ph_immunization_record_child_others_specify',
                    'f.ph_immunization_record_pregnant',
                    'f.ph_immunization_record_pregnant_others_specify',
                    'f.ph_immunization_record_adult_and_elderly',
                    'f.created_at'
                )
                ->where('p.facility_id_updated', "=", $hf_id)
                ->whereBetween('f.created_at', [$start_date, $end_date])
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching visit info data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve visit info data.'
            ], 500);
        }
    }

    public function getPersonalHistorySummaryProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getPersonalHistorySummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->select('id', 'sex', 'age_bracket_id')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching pchrat profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END PERSONAL HISTORY SUMMARY FUNCTIONS ==================

    // ================== 1. FAMILY HISTORY SUMMARY FUNCTIONS (/family_history_summary) ==================
    // Controller Count: 3
    public function getFamilyHistorySummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_form as f')
                ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                ->select(
                    'f.pch_profile_id',
                    'f.fmh_first_degree_relatives_with',
                    'f.fmh_first_degree_relatives_with_specify_others',
                    'f.created_at'
                )
                ->where('p.facility_id_updated', "=", $hf_id)
                ->whereBetween('f.created_at', [$start_date, $end_date])
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching family history data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve family history data.'
            ], 500);
        }
    }

    public function getFamilyHistorySummaryProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getFamilyHistorySummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->select('id', 'sex', 'age_bracket_id')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END FAMILY HISTORY SUMMARY FUNCTIONS ==================

    // ================== 2. SMOKING HISTORY (/smoking_history) ==================
    // Controller Count: 3
    public function getSmokingHistorySummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_form as f')
                ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                ->select(
                    'f.pch_profile_id',
                    'f.sh_smoking',
                    'f.sh_use_of_vape',
                    'f.sh_use_of_vape_age_started',
                    'f.created_at'
                )
                ->where('p.facility_id_updated', "=", $hf_id)
                ->whereBetween('f.created_at', [$start_date, $end_date])
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching smoking history data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve smoking history data.'
            ], 500);
        }
    }

    public function getSmokingHistorySummaryProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getSmokingHistorySummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->select('id', 'sex', 'age_bracket_id')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END SMOKING HISTORY SUMMARY FUNCTIONS ==================

    // ================== 3. SOCIAL HISTORY (/social_history) ==================
    // Controller Count: 3
    public function getSocialHistorySummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_form as f')
                ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                ->select(
                    'f.pch_profile_id',
                    'f.soch_illicit_drug_use',
                    'f.soch_illicit_drug_use_specify_illicit_drug_used',
                    'f.soch_sexual_activity_is_sexually_active',
                    'f.soch_sexual_activity_number_of_partners',
                    'f.soch_sexual_activity_with_protection',
                    'f.soch_sexual_activity_testing_done',
                    'f.created_at'
                )
                ->where('p.facility_id_updated', "=", $hf_id)
                ->whereBetween('f.created_at', [$start_date, $end_date])
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching social history data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve social history data.'
            ], 500);
        }
    }

    public function getSocialHistorySummaryProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getSocialHistorySummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->select('id', 'sex', 'age_bracket_id')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END SOCIAL HISTORY SUMMARY FUNCTIONS ==================

    // ================== 4. LIFESTYLE (/lifestyle_summary) ==================
    // Controller Count: 3
    public function getLifestyleSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_form as f')
                ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                ->select(
                    'f.pch_profile_id',
                    'f.excessive_alcohol_intake',
                    'f.dietary_fiber_intake_3_servings_of_vegetable_daily',
                    'f.dietary_fiber_intake_2_to_3_servings_of_fruits_daily',
                    'f.high_fat_or_high_salt_food_intake',
                    'f.physical_activity',
                    'f.created_at'
                )
                ->where('p.facility_id_updated', "=", $hf_id)
                ->whereBetween('f.created_at', [$start_date, $end_date])
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching lifestyle data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve lifestyle data.'
            ], 500);
        }
    }

    public function getLifestyleSummaryProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getLifestyleSummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->select('id', 'sex', 'age_bracket_id')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END LIFESTYLE SUMMARY FUNCTIONS ==================

    // ================== 5. QUESTIONNAIRE (/questionnaire_summary) ==================
    // Controller Count: 3
    public function getQuestionnaireSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_form as f')
                ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                ->select(
                    'f.pch_profile_id',
                    'f.pahas_or_tia_q1',
                    'f.pahas_or_tia_q2',
                    'f.pahas_or_tia_q3',
                    'f.pahas_or_tia_q4',
                    'f.pahas_or_tia_q5',
                    'f.pahas_or_tia_q6',
                    'f.pahas_or_tia_q7',
                    'f.pahas_or_tia_q8',
                    'f.created_at'
                )
                ->where('p.facility_id_updated', "=", $hf_id)
                ->whereBetween('f.created_at', [$start_date, $end_date])
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching social history data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve social history data.'
            ], 500);
        }
    }

    public function getQuestionnaireSummaryProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getQuestionnaireSummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            $results = DB::table('pch_risk_assessment_tool_profile')
                ->select('id', 'sex', 'age_bracket_id')
                ->where('facility_id_updated', "=", $hf_id)
                ->get();

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END QUESTIONNAIRE SUMMARY FUNCTIONS ==================
}
