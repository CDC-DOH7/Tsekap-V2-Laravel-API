<?php

namespace App\Http\Controllers\TsekapV2\Data\OneissPatientInjuryData;

use App\Http\Controllers\Controller;
use App\Models\TsekapV2\Data\OneissPatientInjuryData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DataController extends Controller
{
    public function retrieveOneissPatientInjuryDataWithoutFacility(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filter' => 'nullable|string|max:50',
            'keyword' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        $filter = $request->query('filter', 'pno');
        $keyword = trim((string) $request->query('keyword', ''));

        $query = OneissPatientInjuryData::query();

        if ($keyword !== '') {
            $query->where(function ($q) use ($filter, $keyword) {
                $columns = [
                    'id' => 'id',
                    'pno' => 'pno',
                    'status' => 'status',
                    'date_report' => 'date_report',
                    'dob' => 'pat_date_of_birth',
                    'facility' => 'pat_facility_no',
                ];

                if ($filter === 'dob') {
                    $parsedDate = date('Y-m-d', strtotime($keyword));
                    $q->where('pat_date_of_birth', $parsedDate);
                } elseif (isset($columns[$filter])) {
                    $q->where($columns[$filter], 'like', "%{$keyword}%");
                } else {
                    $q->where(function ($inner) use ($keyword) {
                        $inner->where('pno', 'like', "%{$keyword}%")
                            ->orWhere('status', 'like', "%{$keyword}%")
                            ->orWhere('pat_facility_no', 'like', "%{$keyword}%")
                            ->orWhere('pat_date_of_birth', 'like', "%{$keyword}%")
                            ->orWhere('id', '=', $keyword)
                            ->orWhere('date_report', 'like', "%{$keyword}%");
                    });
                }
            });
        }

        $results = $query->orderByDesc('created_at')->simplePaginate(30);

        return response()->json($results, 200);
    }

    public function addOneissPatientInjuryData(Request $request): JsonResponse
    {
        $fields = $request->input('fields', []);

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.pno' => 'required|integer',
            'fields.status' => 'required|string|max:50',
            'fields.pat_facility_no' => 'required|string|max:50',
            'fields.date_report' => 'required|date',
            'fields.time_report' => 'required|date_format:H:i',

            'fields.reg_no' => 'nullable|string|max:50',
            'fields.tempreg_no' => 'nullable|string|max:50',
            'fields.hosp_no' => 'nullable|string|max:50',
            'fields.hosp_reg_no' => 'nullable|string|max:50',
            'fields.hosp_cas_no' => 'nullable|string|max:50',

            'fields.ptype_code' => 'required|string|max:50',
            'fields.pat_sex' => 'required|string|max:10',
            'fields.pat_date_of_birth' => 'required|date',

            'fields.age_years' => 'required|integer',
            'fields.age_months' => 'nullable|integer',
            'fields.age_days' => 'nullable|integer',

            'fields.pat_current_address_region' => 'required|string|max:50',
            'fields.pat_current_address_province' => 'required|string|max:50',
            'fields.pat_current_address_city' => 'required|string|max:50',

            'fields.temp_regcode' => 'nullable|string|max:50',
            'fields.temp_provcode' => 'nullable|string|max:50',
            'fields.temp_citycode' => 'nullable|string|max:50',
            'fields.pat_phil_health_no' => 'nullable|string|max:50',

            'fields.plc_provcode' => 'required|string|max:50',
            'fields.plc_regcode' => 'required|string|max:50',
            'fields.plc_ctycode' => 'required|string|max:50',
            'fields.inj_date' => 'required|date',
            'fields.inj_time' => 'required|date_format:H:i',

            'fields.encounter_date' => 'required|date',
            'fields.encounter_time' => 'required|date_format:H:i',
            'fields.inj_intent_code' => 'required|string|max:50',
            'fields.vawcyn' => 'nullable|string|max:10',

            'fields.first_aid_code' => 'required|string|max:50',
            'fields.firstaid_others' => 'nullable|string|max:100',
            'fields.firstaid_others2' => 'nullable|string|max:100',

            'fields.mult_inj' => 'required|string|max:10',
            'fields.noi_abrasion' => 'required|string|max:10',
            'fields.noi_abradtl' => 'required|string|max:100',
            'fields.noi_avulsion' => 'nullable|string|max:10',
            'fields.noi_avuldtl' => 'nullable|string|max:100',
            'fields.noi_burn_r' => 'required|string|max:10',
            'fields.noi_burndtl' => 'nullable|string|max:100',
            'fields.noi_concussion' => 'required|string|max:10',
            'fields.noi_concussiondtl' => 'nullable|string|max:100',
            'fields.noi_contusion' => 'required|string|max:10',
            'fields.noi_contudtl' => 'nullable|string|max:100',
            'fields.noi_frac_clo' => 'required|string|max:10',
            'fields.noi_frcldtl' => 'nullable|string|max:100',
            'fields.noi_frac_ope' => 'required|string|max:10',
            'fields.noi_fropdtl' => 'nullable|string|max:100',
            'fields.noi_owound' => 'required|string|max:10',
            'fields.noi_owoudtl' => 'nullable|string|max:100',
            'fields.noi_amp' => 'required|string|max:10',
            'fields.noi_ampdtl' => 'nullable|string|max:100',
            'fields.noi_others' => 'nullable|string|max:10',
            'fields.noi_otherinj' => 'nullable|string|max:100',
            'fields.ext_bite' => 'required|string|max:10',
            'fields.ext_bite_sp' => 'nullable|string|max:100',
            'fields.ext_burn_r' => 'required|string|max:10',
            'fields.ref_burn_code' => 'required|string|max:50',
            'fields.ext_burn_sp' => 'nullable|string|max:100',
            'fields.ext_chem' => 'required|string|max:10',
            'fields.ext_chem_sp' => 'nullable|string|max:100',
            'fields.ext_sharp' => 'required|string|max:10',
            'fields.ext_sharp_sp' => 'nullable|string|max:100',
            'fields.ext_drown_r' => 'required|string|max:10',
            'fields.ref_drowning_code' => 'nullable|string|max:50',
            'fields.ext_drown_sp' => 'nullable|string|max:100',
            'fields.ext_expo_nature_r' => 'required|string|max:10',
            'fields.ref_expnature_code' => 'nullable|string|max:50',
            'fields.ext_expo_nature_sp' => 'nullable|string|max:100',
            'fields.ext_fall' => 'required|string|max:10',
            'fields.ext_falldtl' => 'nullable|string|max:100',
            'fields.ext_firecracker_r' => 'required|string|max:10',
            'fields.firecracker_code' => 'nullable|string|max:50',
            'fields.ext_firecracker_sp' => 'nullable|string|max:100',
            'fields.ext_sexual' => 'required|string|max:10',
            'fields.ext_gun' => 'required|string|max:10',
            'fields.ext_gun_sp' => 'nullable|string|max:100',
            'fields.ext_hang' => 'required|string|max:10',
            'fields.ext_maul' => 'required|string|max:10',
            'fields.ext_transport' => 'required|string|max:10',
            'fields.vehicle_type_id' => 'nullable|string|max:50',
            'fields.ref_veh_acctype_code' => 'nullable|string|max:50',
            'fields.vehicle_code' => 'nullable|string|max:50',
            'fields.pat_veh_sp' => 'nullable|string|max:100',
            'fields.etc_veh' => 'nullable|string|max:100',
            'fields.etc_veh_sp' => 'nullable|string|max:100',
            'fields.position_code' => 'nullable|string|max:50',
            'fields.pos_pat_sp' => 'nullable|string|max:100',
            'fields.ext_other' => 'nullable|string|max:10',
            'fields.ext_other_sp' => 'nullable|string|max:100',
            'fields.place_occ_code' => 'nullable|string|max:50',
            'fields.poc_wp_spec' => 'nullable|string|max:100',
            'fields.poc_etc_spec' => 'nullable|string|max:100',
            'fields.activity_code' => 'nullable|string|max:50',
            'fields.act_etc_spec' => 'nullable|string|max:100',
            'fields.risk_alcliq' => 'required|string|max:10',
            'fields.risk_none' => 'required|string|max:10',
            'fields.risk_sleep' => 'required|string|max:10',
            'fields.risk_smoke' => 'required|string|max:10',
            'fields.risk_mobpho' => 'required|string|max:10',
            'fields.risk_other' => 'required|string|max:10',
            'fields.risk_etc_spec' => 'nullable|string|max:100',
            'fields.safe_none' => 'required|string|max:10',
            'fields.safe_unkn' => 'required|string|max:10',
            'fields.safe_airbag' => 'required|string|max:10',
            'fields.safe_helmet' => 'required|string|max:10',
            'fields.safe_cseat' => 'required|string|max:10',
            'fields.safe_sbelt' => 'required|string|max:10',
            'fields.safe_drown' => 'required|string|max:10',
            'fields.safe_other' => 'required|string|max:10',
            'fields.safe_other_sp' => 'nullable|string|max:100',
            'fields.trans_ref' => 'required|string|max:50',
            'fields.trans_ref2' => 'required|string|max:50',
            'fields.ref_physician' => 'nullable|string|max:50',
            'fields.ref_hosp_code' => 'nullable|string|max:50',
            'fields.ref_hosp_code_sp' => 'nullable|string|max:100',
            'fields.status_code' => 'nullable|string|max:100',
            'fields.mode_transport_code' => 'nullable|string|max:100',
            'fields.stat_reachdtl' => 'nullable|string|max:100',
            'fields.diagnosis' => 'required|string|max:100',
            'fields.icd_10_external_er' => 'nullable|string|max:10',
            'fields.icd_10_nature_er' => 'nullable|string|max:10',
            'fields.disposition_code' => 'required|string|max:50',
            'fields.disp_er_sp' => 'nullable|string|max:100',
            'fields.disp_er_sp_oth' => 'nullable|string|max:100',
            'fields.outcome_code' => 'nullable|string|max:10',
            'fields.complete_diagnosis' => 'nullable|string|max:100',
            'fields.disp_inpat' => 'nullable|string|max:10',
            'fields.disp_inpat_oth' => 'nullable|string|max:100',
            'fields.disp_inpat_sp' => 'nullable|string|max:100',
            'fields.disp_inpat_sp2' => 'nullable|string|max:100',
            'fields.icd10_nature_inpatient' => 'nullable|string|max:10',
            'fields.outcome_inpat' => 'nullable|string|max:10',
            'fields.icd_10_ext_inpatient' => 'nullable|string|max:10',
            'fields.comments' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $duplicate = OneissPatientInjuryData::query()
            ->where('pno', $fields['pno'])
            ->where('date_report', $fields['date_report'])
            ->where('pat_facility_no', $fields['pat_facility_no'])
            ->exists();

        if ($duplicate) {
            return response()->json(['error' => 'Duplicate ONEISS entry detected.'], 409);
        }

        try {
            $entry = new OneissPatientInjuryData();
            $entry->fill($fields);
            $entry->save();

            return response()->json([
                'message' => 'ONEISS entry successfully saved.',
                'id' => $entry->getAttribute('id'),
            ], 200);
        } catch (Exception $e) {
            Log::error('An error occurred while adding ONEISS patient injury data: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    public function updateOneissPatientInjuryData(Request $request): JsonResponse
    {
        $fields = $request->input('fields', []);

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.id' => 'required|integer',
            'fields.pno' => 'sometimes|integer',
            'fields.status' => 'sometimes|string|max:50',
            'fields.pat_facility_no' => 'sometimes|string|max:50',
            'fields.date_report' => 'sometimes|date',
            'fields.time_report' => 'sometimes|date_format:H:i',
            'fields.ptype_code' => 'sometimes|string|max:50',
            'fields.pat_sex' => 'sometimes|string|max:10',
            'fields.pat_date_of_birth' => 'sometimes|date',
            'fields.age_years' => 'sometimes|integer',
            'fields.pat_current_address_region' => 'sometimes|string|max:100',
            'fields.plc_provcode' => 'sometimes|string|max:50',
            'fields.plc_regcode' => 'sometimes|string|max:50',
            'fields.plc_ctycode' => 'sometimes|string|max:50',
            'fields.inj_date' => 'sometimes|date',
            'fields.inj_time' => 'sometimes|date_format:H:i',
            'fields.encounter_date' => 'sometimes|date',
            'fields.encounter_time' => 'sometimes|date_format:H:i',
            'fields.inj_intent_code' => 'sometimes|string|max:50',
            'fields.vawcyn' => 'sometimes|nullable|string|max:10',
            'fields.first_aid_code' => 'sometimes|string|max:50',
            'fields.firstaid_others' => 'sometimes|nullable|string|max:100',
            'fields.firstaid_others2' => 'sometimes|nullable|string|max:100',
            'fields.mult_inj' => 'sometimes|string|max:10',
            'fields.noi_abrasion' => 'sometimes|nullable|string|max:10',
            'fields.noi_abradtl' => 'sometimes|nullable|string|max:100',
            'fields.noi_avulsion' => 'sometimes|nullable|string|max:10',
            'fields.noi_avuldtl' => 'sometimes|nullable|string|max:100',
            'fields.noi_burn_r' => 'sometimes|nullable|string|max:10',
            'fields.noi_burndtl' => 'sometimes|nullable|string|max:100',
            'fields.noi_concussion' => 'sometimes|nullable|string|max:10',
            'fields.noi_concussiondtl' => 'sometimes|nullable|string|max:100',
            'fields.noi_contusion' => 'sometimes|nullable|string|max:10',
            'fields.noi_contudtl' => 'sometimes|nullable|string|max:100',
            'fields.noi_frac_clo' => 'sometimes|nullable|string|max:10',
            'fields.noi_frcldtl' => 'sometimes|nullable|string|max:100',
            'fields.noi_frac_ope' => 'sometimes|nullable|string|max:10',
            'fields.noi_fropdtl' => 'sometimes|nullable|string|max:100',
            'fields.noi_owound' => 'sometimes|nullable|string|max:10',
            'fields.noi_owoudtl' => 'sometimes|nullable|string|max:100',
            'fields.noi_amp' => 'sometimes|nullable|string|max:10',
            'fields.noi_ampdtl' => 'sometimes|nullable|string|max:100',
            'fields.noi_others' => 'sometimes|nullable|string|max:10',
            'fields.noi_otherinj' => 'sometimes|nullable|string|max:100',
            'fields.ext_bite' => 'sometimes|nullable|string|max:10',
            'fields.ext_bite_sp' => 'sometimes|nullable|string|max:100',
            'fields.ext_burn_r' => 'sometimes|nullable|string|max:10',
            'fields.ref_burn_code' => 'sometimes|nullable|string|max:50',
            'fields.ext_burn_sp' => 'sometimes|nullable|string|max:100',
            'fields.ext_chem' => 'sometimes|nullable|string|max:10',
            'fields.ext_chem_sp' => 'sometimes|nullable|string|max:100',
            'fields.ext_sharp' => 'sometimes|nullable|string|max:10',
            'fields.ext_sharp_sp' => 'sometimes|nullable|string|max:100',
            'fields.ext_drown_r' => 'sometimes|nullable|string|max:10',
            'fields.ref_drowning_code' => 'sometimes|nullable|string|max:50',
            'fields.ext_drown_sp' => 'sometimes|nullable|string|max:100',
            'fields.ext_expo_nature_r' => 'sometimes|nullable|string|max:10',
            'fields.ref_expnature_code' => 'sometimes|nullable|string|max:50',
            'fields.ext_expo_nature_sp' => 'sometimes|nullable|string|max:100',
            'fields.ext_fall' => 'sometimes|nullable|string|max:10',
            'fields.ext_falldtl' => 'sometimes|nullable|string|max:100',
            'fields.ext_firecracker_r' => 'sometimes|nullable|string|max:10',
            'fields.firecracker_code' => 'sometimes|nullable|string|max:50',
            'fields.ext_firecracker_sp' => 'sometimes|nullable|string|max:100',
            'fields.ext_sexual' => 'sometimes|nullable|string|max:10',
            'fields.ext_gun' => 'sometimes|nullable|string|max:10',
            'fields.ext_gun_sp' => 'sometimes|nullable|string|max:100',
            'fields.ext_hang' => 'sometimes|nullable|string|max:10',
            'fields.ext_maul' => 'sometimes|nullable|string|max:10',
            'fields.ext_transport' => 'sometimes|nullable|string|max:10',
            'fields.vehicle_type_id' => 'sometimes|nullable|string|max:50',
            'fields.ref_veh_acctype_code' => 'sometimes|nullable|string|max:50',
            'fields.vehicle_code' => 'sometimes|nullable|string|max:50',
            'fields.pat_veh_sp' => 'sometimes|nullable|string|max:100',
            'fields.etc_veh' => 'sometimes|nullable|string|max:100',
            'fields.etc_veh_sp' => 'sometimes|nullable|string|max:100',
            'fields.position_code' => 'sometimes|nullable|string|max:50',
            'fields.pos_pat_sp' => 'sometimes|string|max:100',
            'fields.ext_other' => 'sometimes|nullable|string|max:10',
            'fields.ext_other_sp' => 'sometimes|nullable|string|max:100',
            'fields.place_occ_code' => 'sometimes|nullable|string|max:50',
            'fields.poc_wp_spec' => 'sometimes|nullable|string|max:100',
            'fields.poc_etc_spec' => 'sometimes|nullable|string|max:100',
            'fields.activity_code' => 'sometimes|nullable|string|max:50',
            'fields.act_etc_spec' => 'sometimes|nullable|string|max:100',
            'fields.risk_alcliq' => 'sometimes|nullable|string|max:10',
            'fields.risk_none' => 'sometimes|nullable|string|max:10',
            'fields.risk_sleep' => 'sometimes|nullable|string|max:10',
            'fields.risk_smoke' => 'sometimes|nullable|string|max:10',
            'fields.risk_mobpho' => 'sometimes|nullable|string|max:10',
            'fields.risk_other' => 'sometimes|nullable|string|max:10',
            'fields.risk_etc_spec' => 'sometimes|nullable|string|max:100',
            'fields.safe_none' => 'sometimes|nullable|string|max:10',
            'fields.safe_unkn' => 'sometimes|nullable|string|max:10',
            'fields.safe_airbag' => 'sometimes|nullable|string|max:10',
            'fields.safe_helmet' => 'sometimes|nullable|string|max:10',
            'fields.safe_cseat' => 'sometimes|nullable|string|max:10',
            'fields.safe_sbelt' => 'sometimes|nullable|string|max:10',
            'fields.safe_drown' => 'sometimes|nullable|string|max:10',
            'fields.safe_other' => 'sometimes|nullable|string|max:10',
            'fields.safe_other_sp' => 'sometimes|nullable|string|max:100',
            'fields.trans_ref' => 'sometimes|nullable|string|max:50',
            'fields.trans_ref2' => 'sometimes|nullable|string|max:50',
            'fields.ref_physician' => 'sometimes|nullable|string|max:50',
            'fields.ref_hosp_code' => 'sometimes|nullable|string|max:50',
            'fields.ref_hosp_code_sp' => 'sometimes|nullable|string|max:100',
            'fields.status_code' => 'sometimes|nullable|string|max:10',
            'fields.mode_transport_code' => 'sometimes|nullable|string|max:10',
            'fields.stat_reachdtl' => 'sometimes|nullable|string|max:100',
            'fields.diagnosis' => 'sometimes|nullable|string|max:100',
            'fields.icd_10_external_er' => 'sometimes|nullable|string|max:10',
            'fields.icd_10_nature_er' => 'sometimes|nullable|string|max:10',
            'fields.disposition_code' => 'sometimes|nullable|string|max:10',
            'fields.disp_er_sp' => 'sometimes|nullable|string|max:100',
            'fields.disp_er_sp_oth' => 'sometimes|nullable|string|max:100',
            'fields.outcome_code' => 'sometimes|nullable|string|max:10',
            'fields.complete_diagnosis' => 'sometimes|nullable|string|max:100',
            'fields.disp_inpat' => 'sometimes|nullable|string|max:10',
            'fields.disp_inpat_oth' => 'sometimes|nullable|string|max:100',
            'fields.disp_inpat_sp' => 'sometimes|nullable|string|max:100',
            'fields.disp_inpat_sp2' => 'sometimes|nullable|string|max:100',
            'fields.icd10_nature_inpatient' => 'sometimes|nullable|string|max:10',
            'fields.outcome_inpat' => 'sometimes|nullable|string|max:10',
            'fields.icd_10_ext_inpatient' => 'sometimes|nullable|string|max:10',
            'fields.comments' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $entry = OneissPatientInjuryData::find($fields['id']);

        if (!$entry) {
            return response()->json(['error' => 'ONEISS entry not found.'], 404);
        }

        try {
            $entry->fill($fields);
            $entry->save();

            return response()->json(['message' => 'ONEISS entry successfully updated.'], 200);
        } catch (Exception $e) {
            Log::error('Error updating ONEISS patient injury data: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    public function deleteOneissPatientInjuryData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fields.id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $id = $request->input('fields.id');

        try {
            $entry = OneissPatientInjuryData::find($id);

            if (!$entry) {
                return response()->json(['error' => 'ONEISS entry not found.'], 404);
            }

            $entry->delete();

            return response()->json(['message' => 'ONEISS entry successfully deleted.'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting ONEISS patient injury data: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }
}
