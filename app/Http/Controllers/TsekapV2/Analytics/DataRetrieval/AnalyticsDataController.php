<?php

namespace App\Http\Controllers\TsekapV2\Analytics\DataRetrieval;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

class AnalyticsDataController extends Controller
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
    // Controller Count: 2
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
    // Controller Count: 3
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

    // ================== 7. CLINICAL COMPLAINTS SUMMARY FUNCTIONS (/clinical_complaints_summary) ==================
    // Controller Count: 3
    public function getClinicalComplaintsArRecords(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form_type');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json(['error' => 'hf_id, start_date, and end_date are required.'], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $records = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select(
                        'f.risk_profile_id',
                        'f.ar_chest_pain',
                        'f.ar_difficulty_breathing',
                        'f.ar_loss_of_consciousness',
                        'f.ar_slurred_speech',
                        'f.ar_facial_asymmetry',
                        'f.ar_weakness_numbness',
                        'f.ar_disoriented',
                        'f.ar_chest_retractions',
                        'f.ar_seizure_convulsion',
                        'f.ar_act_self_harm_suicide',
                        'f.ar_agitated_behavior',
                        'f.ar_eye_injury',
                        'f.ar_severe_injuries',
                        'f.created_at'
                    )
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->whereBetween('f.created_at', [$start_date, $end_date])
                    ->get();
            }

            // PCHRAT Form
            // if ($form_type === 'pch') {
            //     $records = DB::table('pch_risk_assessment_tool_form f')
            //         ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
            //         ->select(
            //             'f.pch_profile_id',
            //             'f.ar_chest_pain',
            //             'f.ar_difficulty_breathing',
            //             'f.ar_loss_of_consciousness',
            //             'f.ar_slurred_speech',
            //             'f.ar_facial_asymmetry',
            //             'f.ar_weakness_numbness',
            //             'f.ar_disoriented',
            //             'f.ar_chest_retractions',
            //             'f.ar_seizure_convulsion',
            //             'f.ar_act_self_harm_suicide',
            //             'f.ar_agitated_behavior',
            //             'f.ar_eye_injury',
            //             'f.ar_severe_injuries',
            //             'f.created_at'
            //         )
            //         ->where('p.facility_id_updated', "=", $hf_id)
            //         ->whereBetween('f.created_at', [$start_date, $end_date])
            //         ->get();
            // }

            return response()->json($records);
        } catch (Exception $e) {
            Log::error("Error fetching ARD records: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to retrieve ARD data.'], 500);
        }
    }

    public function getClinicalComplaintsRiskProfile(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form 
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated',  "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END CLINICAL COMPLAINTS SUMMARY FUNCTIONS ==================

    // ================== 8. TOBACCO SUMMARY FUNCTIONS (/tobacco_summary) ==================
    // Controller Count: 4
    public function getTobaccoSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form_type');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Asssessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select('f.risk_profile_id', 'f.rf_tobacco_use', 'f.created_at')
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->where('f.created_at', '>=', $start_date)
                    ->where('f.created_at', '<', $end_date)
                    ->get();
            }

            // // Philpen Risk Asssessment Form
            // if ($form_type === 'pch') {
            //     $results = DB::table('pch_risk_assessment_tool_form as f')
            //         ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
            //         ->select('f.pch_profile_id', 'f.sh_smoking', 'f.sh_use_of_vape', 'f.sh_use_of_vape_age_started', 'f.created_at')
            //         ->where('p.facility_id_updated', "=", $hf_id)
            //         ->where('f.created_at', '>=', $start_date)
            //         ->where('f.created_at', '<', $end_date)
            //         ->get();
            // }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching tobacco usage data: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch tobacco usage data.'], 500);
        }
    }

    public function getTobaccoSummaryPatientInfo(Request $request)
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
                $results = DB::table('risk_profile')
                    ->select('id', 'sex', 'age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->select('id', 'sex', 'age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching patient basic info: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getTobaccoSummaryRiskProfile(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END TOBACCO SUMMARY FUNCTIONS ==================

    // ================== 9. ALCOHOL INTAKE SUMMARY FUNCTIONS (/alcohol_intake_summary) ==================
    // Controller Count: 4
    public function getAlcoholIntakeSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Asessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select('f.risk_profile_id', 'f.rf_alcohol_intake', 'f.created_at')
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->where('f.created_at', '>=', $start_date)
                    ->where('f.created_at', '<', $end_date)
                    ->get();
            }

            // PCHRAT Form
            // if ($form_type === 'pch') {
            //     $results = DB::table('pch_risk_assessment_tool_form as f')
            //         ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
            //         ->select('f.pch_profile_id', 'f.rf_alcohol_intake', 'f.created_at')
            //         ->where('p.facility_id_updated', "=", $hf_id)
            //         ->where('f.created_at', '>=', $start_date)
            //         ->where('f.created_at', '<', $end_date)
            //         ->get();
            // }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching alcohol intake data: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch alcohol intake data.'], 500);
        }
    }

    public function getAlcoholIntakeSummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json([
                'error' => 'hf_id is required'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_profile')
                    ->select('id', 'sex', 'age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->select('id', 'sex', 'age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error retrieving patient info: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to fetch patient information.'
            ], 500);
        }
    }

    public function getAlcoholIntakeSummaryRiskProfile(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END ALCOHOL INTAKE SUMMARY FUNCTIONS ==================

    // ================== 10. PHYSICAL ACTIVITY SUMMARY FUNCTIONS (/physical_activity_summary) ==================
    // Controller Count: 4
    public function getPhysicalActivitySummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form_type');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form 
            if ($form_type === 'raf') {
                $results = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select('f.risk_profile_id', 'f.rf_physical_activity', 'f.created_at')
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->where('f.created_at', '>=', $start_date)
                    ->where('f.created_at', '<', $end_date)
                    ->get();
            }

            // PCHRAT Form 
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_form as f')
                    ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                    ->select('f.risk_profile_id', 'f.rf_physical_activity', 'f.created_at')
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->where('f.created_at', '>=', $start_date)
                    ->where('f.created_at', '<', $end_date)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching physical activity data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to fetch physical activity data.'
            ], 500);
        }
    }
    public function getPhysicalActivitySummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json([
                'error' => 'hf_id is required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_profile')
                    ->select('id', 'sex', 'age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->select('id', 'sex', 'age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching patient info: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve patient info.'
            ], 500);
        }
    }

    public function getPhysicalActivitySummaryRiskProfile(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form 
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END PHYSICAL ACTIVITY SUMMARY FUNCTIONS ==================

    // ================== 11. NUTRITION SUMMARY FUNCTIONS (/nutrition_summary) ==================
    // Controller Count: 4
    public function getNutritionSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select('f.risk_profile_id', 'f.rf_nutrition_dietary', 'f.created_at')
                    ->where('p.facility_id_updated', $hf_id)
                    ->where('f.created_at', '>=', $start_date)
                    ->where('f.created_at', '<', $end_date)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_form as f')
                    ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
                    ->select('f.pch_profile_id', 'f.rf_nutrition_dietary', 'f.created_at')
                    ->where('p.facility_id_updated', $hf_id)
                    ->where('f.created_at', '>=', $start_date)
                    ->where('f.created_at', '<', $end_date)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error retrieving nutrition data: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch nutrition dietary data.'], 500);
        }
    }

    public function getNutritionSummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json([
                'error' => 'hf_id is required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_profile')
                    ->select('id', 'sex', 'age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->select('id', 'sex', 'age_bracket_id')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching patient info: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve patient info.'
            ], 500);
        }
    }

    public function getNutritionSummaryRiskProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            // Philpen Risk Asessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form 
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END NUTRITION SUMMARY FUNCTIONS ==================

    // ================== 12. PREV MED HISTORY SUMMARY FUNCTIONS (/prev_med_history_summary) ==================
    // Controller Count: 4
    public function getPrevMedHistorySummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select(
                        'f.risk_profile_id',
                        'f.pmh_allergies',
                        'f.pmh_asthma',
                        'f.pmh_cancer',
                        'f.pmh_copd',
                        'f.pmh_diabetes',
                        'f.pmh_mn_and_s_disorder',
                        'f.pmh_heart_disease',
                        'f.pmh_hypertension',
                        'f.pmh_kidney_disorders',
                        'f.pmh_previous_surgical',
                        'f.pmh_thyroid_disorders',
                        'f.pmh_vision_problems',
                        'f.created_at'
                    )
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->where('f.created_at', '>=', $start_date)
                    ->where('f.created_at', '<=', $end_date)
                    ->get();
            }

            // PCHRAT Form
            // if ($form_type === 'pch') {
            //     $results = DB::table('pch_risk_assessment_tool_form as f')
            //         ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
            //         ->select(
            //             'f.pch_profile_id',
            //             'f.pmh_allergies',
            //             'f.pmh_asthma',
            //             'f.pmh_cancer',
            //             'f.pmh_copd',
            //             'f.pmh_diabetes',
            //             'f.pmh_mn_and_s_disorder',
            //             'f.pmh_heart_disease',
            //             'f.pmh_hypertension',
            //             'f.pmh_kidney_disorders',
            //             'f.pmh_previous_surgical',
            //             'f.pmh_thyroid_disorders',
            //             'f.pmh_vision_problems',
            //             'f.created_at'
            //         )
            //         ->where('p.facility_id_updated', "=", $hf_id)
            //         ->where('f.created_at', '>=', $start_date)
            //         ->where('f.created_at', '<=', $end_date)
            //         ->get();
            // }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching PMH data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve PMH data.'
            ], 500);
        }
    }

    public function getPrevMedHistorySummaryRiskProfile(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form 
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getPrevMedHistorySummaryPatientInfo(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END PREV MED HISTORY SUMMARY FUNCTIONS ==================

    // ================== 13. FAMILY HISTORY SUMMARY FUNCTIONS (/family_history_summary) ==================
    // Controller Count: 4
    public function getFamilyHistorySummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select(
                        'f.risk_profile_id',
                        'f.fmh_asthma',
                        'f.fmh_cancer',
                        'f.fmh_copd',
                        'f.fmh_diabetes_mellitus',
                        'f.fmh_mn_and_s_disorder',
                        'f.fmh_heart_disease',
                        'f.fmh_hypertension',
                        'f.fmh_kidney_disease',
                        'f.fmh_stroke',
                        'f.fmh_having_tuberculosis_5_years',
                        'f.fmh_first_degree_relative',
                        'f.created_at'
                    )
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->whereBetween('f.created_at', [$start_date, $end_date])
                    ->get();
            }

            // PCHRAT Form
            // if ($form_type === 'pch') {
            //     $results = DB::table('pch_risk_assessment_tool_form as f')
            //         ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
            //         ->select(
            //             'f.pch_profile_id',
            //             'f.fmh_asthma',
            //             'f.fmh_cancer',
            //             'f.fmh_copd',
            //             'f.fmh_diabetes_mellitus',
            //             'f.fmh_mn_and_s_disorder',
            //             'f.fmh_heart_disease',
            //             'f.fmh_hypertension',
            //             'f.fmh_kidney_disease',
            //             'f.fmh_stroke',
            //             'f.fmh_having_tuberculosis_5_years',
            //             'f.fmh_first_degree_relative',
            //             'f.created_at'
            //         )
            //         ->where('p.facility_id_updated', "=", $hf_id)
            //         ->whereBetween('f.created_at', [$start_date, $end_date])
            //         ->get();
            // }

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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getFamilyHistorySummaryPatientInfo(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END FAMILY HISTORY SUMMARY FUNCTIONS ==================

    // ================== 14. BP1 SUMMARY FUNCTIONS (/bp1_summary) ==================
    // Controller Count: 4
    public function getBp1SummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select(
                        'f.risk_profile_id',
                        'f.rs_systolic_t1',
                        'f.rs_diastolic_t1',
                        'f.created_at'
                    )
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->whereBetween('f.created_at', [$start_date, $end_date])
                    ->get();
            }

            // PCHRAT Form
            // if ($form_type === 'pch') {
            //     $results = DB::table('pch_risk_assessment_tool_form as f')
            //         ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
            //         ->select(
            //             'f.pch_profile_id',
            //             'f.rs_systolic_t1',
            //             'f.rs_diastolic_t1',
            //             'f.created_at'
            //         )
            //         ->where('p.facility_id_updated', "=", $hf_id)
            //         ->whereBetween('f.created_at', [$start_date, $end_date])
            //         ->get();
            // }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching blood pressure data: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve blood pressure data.'
            ], 500);
        }
    }

    public function getBp1SummaryRiskProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getBp1SummaryPatientInfo(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END BP1 HISTORY SUMMARY FUNCTIONS ==================

    // ================== 15. HYPERTENSION/BP2 SUMMARY FUNCTIONS (/hypertension_summary) ==================
    // Controller Count: 4
    public function getBp2SummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select(
                        'f.risk_profile_id',
                        'f.rs_systolic_t2',
                        'f.rs_diastolic_t2',
                        'f.created_at'
                    )
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->whereBetween('f.created_at', [$start_date, $end_date])
                    ->get();
            }

            // PCHRAT Form
            // if ($form_type === 'pch') {
            //     $results = DB::table('pch_risk_assessment_tool_form as f')
            //         ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
            //         ->select(
            //             'f.pch_profile_id',
            //             'f.rs_systolic_t2',
            //             'f.rs_diastolic_t2',
            //             'f.created_at'
            //         )
            //         ->where('p.facility_id_updated', "=", $hf_id)
            //         ->whereBetween('f.created_at', [$start_date, $end_date])
            //         ->get();
            // }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching blood pressure data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve blood pressure data.'
            ], 500);
        }
    }

    public function getBp2SummaryRiskProfile(Request $request)
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
            if ($form_type === 'raf') {
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getBp2SummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            if ($form_type === 'raf') {
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END HYPERTENSION SUMMARY FUNCTIONS ==================

    // ================== 16. DIABETES SUMMARY FUNCTIONS (/diabetes_summary) ==================
    // Controller Count: 4
    public function getDiabetesSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select(
                        'f.risk_profile_id',
                        'f.rs_blood_sugar_fbs',
                        'f.created_at'
                    )
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->whereBetween('f.created_at', [$start_date, $end_date])
                    ->get();
            }

            // PCHRAT Form
            // if ($form_type === 'pch') {
            //     $results = DB::table('pch_risk_assessment_tool_form as f')
            //         ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
            //         ->select(
            //             'f.pch_profile_id',
            //             'f.rs_blood_sugar_fbs',
            //             'f.created_at'
            //         )
            //         ->where('p.facility_id_updated', "=", $hf_id)
            //         ->whereBetween('f.created_at', [$start_date, $end_date])
            //         ->get();
            // }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching blood sugar (FBS) data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve blood sugar data.'
            ], 500);
        }
    }

    public function getDiabetesSummaryRiskProfile(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getDiabetesSummaryPatientInfo(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END DIABETES SUMMARY FUNCTIONS ==================

    // ================== 17. HYPERCHOLESTEROLEMIA SUMMARY FUNCTIONS (/hypercholesterolemia_summary) ==================
    // Controller Count: 4
    public function getHypercholesterolemiaSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select(
                        'f.risk_profile_id',
                        'f.rs_lipid_cholesterol',
                        'f.rs_lipid_ldl',
                        'f.rs_lipid_hdl',
                        'f.created_at'
                    )
                    ->where('p.facility_id_updated', "=", $hf_id)
                    ->whereBetween('f.created_at', [$start_date, $end_date])
                    ->get();
            }

            // if ($form_type === 'pch') {
            //     $results = DB::table('pch_risk_assessment_tool_form as f')
            //         ->join('pch_risk_assessment_profile as p', 'f.pch_profile_id', '=', 'p.id')
            //         ->select(
            //             'f.pch_profile_id',
            //             'f.rs_lipid_cholesterol',
            //             'f.rs_lipid_ldl',
            //             'f.rs_lipid_hdl',
            //             'f.created_at'
            //         )
            //         ->where('p.facility_id_updated', "=", $hf_id)
            //         ->whereBetween('f.created_at', [$start_date, $end_date])
            //         ->get();
            // }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching lipid panel data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve lipid panel data.'
            ], 500);
        }
    }

    public function getHypercholesterolemiaSummaryRiskProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getHypercholesterolemiaSummaryPatientInfo(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END HYPERCHOLESTEROLEMIA SUMMARY FUNCTIONS ==================

    // ================== 18. RESPIRATORY SUMMARY FUNCTIONS (/respiratory_summary) ==================
    // Controller Count: 4
    public function getRespiratorySummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select(
                        'f.risk_profile_id',
                        'f.rs_chronic_respiratory_disease',
                        'f.created_at'
                    )
                    ->where('p.facility_id_updated', $hf_id)
                    ->whereBetween('f.created_at', [$start_date, $end_date])
                    ->get();
            }

            // if ($form_type === 'pch') {
            //     $results = DB::table('pch_risk_assessment_tool_form as f')
            //         ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
            //         ->select(
            //             'f.pch_profile_id',
            //             'f.rs_chronic_respiratory_disease',
            //             'f.created_at'
            //         )
            //         ->where('p.facility_id_updated', $hf_id)
            //         ->whereBetween('f.created_at', [$start_date, $end_date])
            //         ->get();
            // }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching respiratory screening data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve respiratory screening data.'
            ], 500);
        }
    }

    public function getRespiratorySummaryRiskProfile(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getRespiratorySummaryPatientInfo(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END RESPIRATORY SUMMARY FUNCTIONS ==================

    // ================== 19. PROBABLE SUMMARY FUNCTIONS (/probable_summary) ==================
    // Controller Count: 4
    public function getProbableSummaryData(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if (!$hf_id || !$start_date || !$end_date) {
            return response()->json([
                'error' => 'hf_id, start_date, and end_date are required.'
            ], 400);
        }

        if (!in_array($form_type, ['pch', 'raf'])) {
            return response()->json(['error' => 'Form type unsupported.'], 400);
        }

        try {

            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_form as f')
                    ->join('risk_profile as p', 'f.risk_profile_id', '=', 'p.id')
                    ->select(
                        'f.risk_profile_id',
                        'f.rs_if_yes_any_symptoms',
                        'f.created_at'
                    )
                    ->where('p.facility_id_updated', $hf_id)
                    ->whereBetween('f.created_at', [$start_date, $end_date])
                    ->get();
            }

            // PCHRAT Form
            // if ($form_type === 'pch') {
            //     $results = DB::table('pch_risk_assessment_tool_form as f')
            //         ->join('pch_risk_assessment_tool_profile as p', 'f.pch_profile_id', '=', 'p.id')
            //         ->select(
            //             'f.pch_profile_id',
            //             'f.rs_if_yes_any_symptoms',
            //             'f.created_at'
            //         )
            //         ->where('p.facility_id_updated', "=", $hf_id)
            //         ->whereBetween('f.created_at', [$start_date, $end_date])
            //         ->get();
            // }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching symptom screening data: " . $e->getMessage() . ".");
            return response()->json([
                'error' => 'Failed to retrieve symptom screening data.'
            ], 500);
        }
    }

    public function getProbableSummaryRiskProfile(Request $request)
    {
        $hf_id = $request->query('hf_id');
        $form_type = $request->query('form');

        if (!$hf_id) {
            return response()->json(['error' => 'hf_id is required.'], 400);
        }

        try {
            // Philpen Risk Assessment Form
            if ($form_type === 'raf') {
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }

    public function getProbableSummaryPatientInfo(Request $request)
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
                $results = DB::table('risk_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            // PCHRAT Form
            if ($form_type === 'pch') {
                $results = DB::table('pch_risk_assessment_tool_profile')
                    ->where('facility_id_updated', "=", $hf_id)
                    ->get();
            }

            return response()->json($results);
        } catch (Exception $e) {
            Log::error("Error fetching risk profiles: " . $e->getMessage() . ".");
            return response()->json(['error' => 'Failed to fetch data.'], 500);
        }
    }
    // ================== END PROBABLE HISTORY SUMMARY FUNCTIONS ==================
}
