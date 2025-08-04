<?php

namespace App\Http\Controllers\TsekapV2\Analytics\DataRetrieval\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

class PchratAnalyticsDataController extends Controller
{
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

    public function getFamilyHistorySummaryRiskProfile(Request $request)
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

    public function getSmokingHistorySummaryRiskProfile(Request $request)
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

    public function getSocialHistorySummaryRiskProfile(Request $request)
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

    public function getLifestyleSummaryRiskProfile(Request $request)
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

    public function getQuestionnaireSummaryRiskProfile(Request $request)
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
