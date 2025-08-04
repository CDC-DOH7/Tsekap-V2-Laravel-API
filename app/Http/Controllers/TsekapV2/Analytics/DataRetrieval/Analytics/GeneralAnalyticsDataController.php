<?php

namespace App\Http\Controllers\TsekapV2\Analytics\DataRetrieval\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

class GeneralAnalyticsDataController extends Controller
{
    // Mapping for requests through string
    // 1. pch - PCHRAT forms
    // 2. raf - Philpen Risk Assessment Form

    // ###########################################################################################
    // ================== GENERAL CONTROLLERS (/age_brackets) ==================
    public function getAgeBrackets(Request $request)
    {
        try {
            $ageBrackets = DB::table('new_age_brackets')->get();
            return response()->json($ageBrackets);
        } catch (Exception $e) {
            Log::error("Error fetching age brackets: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch age brackets.'], 500);
        }
    }

    // ###########################################################################################
    // ================== 1. PATIENT SUMMARY FUNCTIONS (/patient_summary) ==================
    // Controller Count: 1
    public function getPatientSummary(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $patients = DB::table('risk_profile')
                    ->where('facility_id_updated', $hf_id)
                    ->get();
            }

            // PCHRAT Form 
            if ($form_type === 'pch') {
                $patients = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', $hf_id)
                    ->get();
            }

            return response()->json($patients);
        } catch (Exception $e) {
            Log::error("Error retrieving patient summary: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Error retrieving patient summary data.'], 500);
        }
    }
    // ================== END PATIENT SUMMARY FUNCTIONS ==================

    // ================== 2. AGE GROUP SUMMARY FUNCTIONS  (/age_group_summary) ==================
    // Controller Count: 1
    public function getAgeGroupSummary(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_profile as p')
                    ->join('new_age_brackets as a', 'p.age_bracket_id', '=', 'a.id')
                    ->select('a.id', 'a.description', 'p.age_bracket_id', 'p.sex', 'p.age')
                    ->whereNotNull('p.age_bracket_id')
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile as p')
                    ->join('new_age_brackets as a', 'p.age_bracket_id', '=', 'a.id')
                    ->select('a.id', 'a.description', 'p.age_bracket_id', 'p.sex', 'p.age')
                    ->whereNotNull('p.age_bracket_id')
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching age bracket summary: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Error fetching data.'], 500);
        }
    }
    // ================== END AGE GROUP SUMMARY FUNCTIONS ==================

    // ================== 3. AGE RANGE SUMMARY FUNCTIONS (/age_range_summary) ==================
    // Controller Count: 1
    public function getAgeRangeSummary(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {

            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_profile as p')
                    ->join('new_age_brackets as a', 'p.age_bracket_id', '=', 'a.id')
                    ->select('a.id', 'a.description', 'p.age_bracket_id', 'p.sex', 'p.age')
                    ->whereNotNull('p.age_bracket_id')
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile as p')
                    ->join('new_age_brackets as a', 'p.age_bracket_id', '=', 'a.id')
                    ->select('a.id', 'a.description', 'p.age_bracket_id', 'p.sex', 'p.age')
                    ->whereNotNull('p.age_bracket_id')
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching age bracket summary: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Error fetching data.'], 500);
        }
    }
    // ================== END AGE RANGE SUMMARY FUNCTIONS ==================

    // ================== 4. MONTH YEAR SUMMARY FUNCTIONS (/month_year_summary) ==================
    // Controller Count: 1
    public function getMonthYearSummaryPatientData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $patients = DB::table('risk_profile')
                    ->select('profile_id', 'sex', 'age', 'age_bracket_id', 'created_at')
                    ->whereNotNull('age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $patients = DB::table('pch_risk_assessment_tool_profile')
                    ->select('profile_id', 'sex', 'age', 'age_bracket_id', 'created_at')
                    ->whereNotNull('age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($patients);
        } catch (Exception $e) {
            Log::error("Error fetching patient data: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to retrieve data.'], 500);
        }
    }
    // ================== END MONTH YEAR SUMMARY FUNCTIONS ==================

    // ================== 5. MORBIDITY SUMMARY FUNCTIONS (/morbidity_summary) ==================
    // Controller Count: 1
    public function getMorbiditySummaryPatientData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $patients = DB::table('risk_profile')
                    ->select('profile_id', 'sex', 'age', 'age_bracket_id', 'created_at')
                    ->whereNotNull('age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $patients = DB::table('pch_risk_assessment_tool_profile')
                    ->select('profile_id', 'sex', 'age', 'age_bracket_id', 'created_at')
                    ->whereNotNull('age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($patients);
        } catch (Exception $e) {
            Log::error("Error fetching patient data: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to retrieve data.'], 500);
        }
    }
    // ================== END MORBIDITY SUMMARY FUNCTIONS ==================

    // ================== 6. MONTHLY SUMMARY FUNCTIONS (/monthly_summary) ==================
    // Controller Count: 2
    public function getMonthlySummaryPatientData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $patients = DB::table('risk_profile')
                    ->select('profile_id', 'sex', 'age', 'age_bracket_id', 'created_at')
                    ->whereNotNull('age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form 
            if ($form_type === 'pch') {
                $patients = DB::table('pch_risk_assessment_tool_profile')
                    ->select('profile_id', 'sex', 'age', 'age_bracket_id', 'created_at')
                    ->whereNotNull('age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($patients);
        } catch (Exception $e) {
            Log::error("Error fetching patient data: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to retrieve data.'], 500);
        }
    }

    public function getMonthlySummaryRiskProfileByFacility(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $profiles = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $profiles = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($profiles);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to retrieve data.'], 500);
        }
    }
    // ================== END MONTHLY SUMMARY FUNCTIONS ==================
}
