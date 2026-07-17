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
            'fields.status' => 'nullable|string',
            'fields.pat_facility_no' => 'nullable|string',
            'fields.date_report' => 'nullable|date',
            'fields.time_report' => 'nullable|date_format:H:i',

            'fields.reg_no' => 'nullable|string',
            'fields.tempreg_no' => 'nullable|string',
            'fields.hosp_no' => 'nullable|string',
            'fields.hosp_reg_no' => 'nullable|string',
            'fields.hosp_cas_no' => 'nullable|string',

            'fields.ptype_code' => 'nullable|string',
            'fields.pat_sex' => 'nullable|string|max:10',
            'fields.pat_date_of_birth' => 'nullable|date',

            'fields.age_years' => 'nullable|integer',
            'fields.age_months' => 'nullable|integer',
            'fields.age_days' => 'nullable|integer',

            'fields.pat_current_address_region' => 'nullable|string',
            'fields.pat_current_address_province' => 'nullable|string',
            'fields.pat_current_address_city' => 'nullable|string',

            'fields.temp_regcode' => 'nullable|string',
            'fields.temp_provcode' => 'nullable|string',
            'fields.temp_citycode' => 'nullable|string',
            'fields.pat_phil_health_no' => 'nullable|string',

            'fields.plc_provcode' => 'nullable|string',
            'fields.plc_regcode' => 'nullable|string',
            'fields.plc_ctycode' => 'nullable|string',
            'fields.inj_date' => 'nullable|date',
            'fields.inj_time' => 'nullable|date_format:H:i',

            'fields.encounter_date' => 'nullable|date',
            'fields.encounter_time' => 'nullable|date_format:H:i',
            'fields.inj_intent_code' => 'nullable|string',
            'fields.vawcyn' => 'nullable|string',

            'fields.first_aid_code' => 'nullable|string',
            'fields.firstaid_others' => 'nullable|string',
            'fields.firstaid_others2' => 'nullable|string',

            'fields.mult_inj' => 'nullable|string|max:10',
            'fields.noi_abrasion' => 'nullable|string|max:10',
            'fields.noi_abradtl' => 'nullable|string',
            'fields.noi_avulsion' => 'nullable|string|max:10',
            'fields.noi_avuldtl' => 'nullable|string',
            'fields.noi_burn_r' => 'nullable|string|max:10',
            'fields.noi_burndtl' => 'nullable|string',
            'fields.noi_concussion' => 'nullable|string|max:10',
            'fields.noi_concussiondtl' => 'nullable|string',
            'fields.noi_contusion' => 'nullable|string|max:10',
            'fields.noi_contudtl' => 'nullable|string',
            'fields.noi_frac_clo' => 'nullable|string|max:10',
            'fields.noi_frcldtl' => 'nullable|string',
            'fields.noi_frac_ope' => 'nullable|string|max:10',
            'fields.noi_fropdtl' => 'nullable|string',
            'fields.noi_owound' => 'nullable|string|max:10',
            'fields.noi_owoudtl' => 'nullable|string',
            'fields.noi_amp' => 'nullable|string|max:10',
            'fields.noi_ampdtl' => 'nullable|string',
            'fields.noi_others' => 'nullable|string|max:10',
            'fields.noi_otherinj' => 'nullable|string',
            'fields.ext_bite' => 'nullable|string|max:10',
            'fields.ext_bite_sp' => 'nullable|string',
            'fields.ext_burn_r' => 'nullable|string|max:10',
            'fields.ref_burn_code' => 'nullable|string',
            'fields.ext_burn_sp' => 'nullable|string',
            'fields.ext_chem' => 'nullable|string|max:10',
            'fields.ext_chem_sp' => 'nullable|string',
            'fields.ext_sharp' => 'nullable|string|max:10',
            'fields.ext_sharp_sp' => 'nullable|string',
            'fields.ext_drown_r' => 'nullable|string|max:10',
            'fields.ref_drowning_code' => 'nullable|string',
            'fields.ext_drown_sp' => 'nullable|string',
            'fields.ext_expo_nature_r' => 'nullable|string|max:10',
            'fields.ref_expnature_code' => 'nullable|string',
            'fields.ext_expo_nature_sp' => 'nullable|string',
            'fields.ext_fall' => 'nullable|string|max:10',
            'fields.ext_falldtl' => 'nullable|string',
            'fields.ext_firecracker_r' => 'nullable|string|max:10',
            'fields.firecracker_code' => 'nullable|string',
            'fields.ext_firecracker_sp' => 'nullable|string',
            'fields.ext_sexual' => 'nullable|string|max:10',
            'fields.ext_gun' => 'nullable|string|max:10',
            'fields.ext_gun_sp' => 'nullable|string',
            'fields.ext_hang' => 'nullable|string|max:10',
            'fields.ext_maul' => 'nullable|string|max:10',
            'fields.ext_transport' => 'nullable|string|max:10',
            'fields.vehicle_type_id' => 'nullable|string',
            'fields.ref_veh_acctype_code' => 'nullable|string',
            'fields.vehicle_code' => 'nullable|string',
            'fields.pat_veh_sp' => 'nullable|string',
            'fields.etc_veh' => 'nullable|string',
            'fields.etc_veh_sp' => 'nullable|string',
            'fields.position_code' => 'nullable|string',
            'fields.pos_pat_sp' => 'nullable|string',
            'fields.ext_other' => 'nullable|string|max:10',
            'fields.ext_other_sp' => 'nullable|string',
            'fields.place_occ_code' => 'nullable|string',
            'fields.poc_wp_spec' => 'nullable|string',
            'fields.poc_etc_spec' => 'nullable|string',
            'fields.activity_code' => 'nullable|string',
            'fields.act_etc_spec' => 'nullable|string',
            'fields.risk_alcliq' => 'nullable|string|max:10',
            'fields.risk_none' => 'nullable|string|max:10',
            'fields.risk_sleep' => 'nullable|string|max:10',
            'fields.risk_smoke' => 'nullable|string|max:10',
            'fields.risk_mobpho' => 'nullable|string|max:10',
            'fields.risk_other' => 'nullable|string|max:10',
            'fields.risk_etc_spec' => 'nullable|string',
            'fields.safe_none' => 'nullable|string|max:10',
            'fields.safe_unkn' => 'nullable|string|max:10',
            'fields.safe_airbag' => 'nullable|string|max:10',
            'fields.safe_helmet' => 'nullable|string|max:10',
            'fields.safe_cseat' => 'nullable|string|max:10',
            'fields.safe_sbelt' => 'nullable|string|max:10',
            'fields.safe_drown' => 'nullable|string|max:10',
            'fields.safe_other' => 'nullable|string|max:10',
            'fields.safe_other_sp' => 'nullable|string',
            'fields.trans_ref' => 'nullable|string',
            'fields.trans_ref2' => 'nullable|string',
            'fields.ref_physician' => 'nullable|string',
            'fields.ref_hosp_code' => 'nullable|string',
            'fields.ref_hosp_code_sp' => 'nullable|string',
            'fields.status_code' => 'nullable|string',
            'fields.mode_transport_code' => 'nullable|string',
            'fields.stat_reachdtl' => 'nullable|string',
            'fields.diagnosis' => 'nullable|string',
            'fields.icd_10_external_er' => 'nullable|string',
            'fields.icd_10_nature_er' => 'nullable|string',
            'fields.disposition_code' => 'nullable|string',
            'fields.disp_er_sp' => 'nullable|string',
            'fields.disp_er_sp_oth' => 'nullable|string',
            'fields.outcome_code' => 'nullable|string',
            'fields.complete_diagnosis' => 'nullable|string',
            'fields.disp_inpat' => 'nullable|string',
            'fields.disp_inpat_oth' => 'nullable|string',
            'fields.disp_inpat_sp' => 'nullable|string',
            'fields.disp_inpat_sp2' => 'nullable|string',
            'fields.icd10_nature_inpatient' => 'nullable|string',
            'fields.outcome_inpat' => 'nullable|string',
            'fields.icd_10_ext_inpatient' => 'nullable|string',
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
            'fields' => 'nullable|array',
            'fields.id' => 'nullable|integer',
            'fields.pno' => 'sometimes|integer',
            'fields.status' => 'sometimes|string',
            'fields.pat_facility_no' => 'sometimes|string',
            'fields.date_report' => 'sometimes|date',
            'fields.time_report' => 'sometimes|date_format:H:i',

            'fields.reg_no' => 'sometimes|string',
            'fields.tempreg_no' => 'sometimes|string',
            'fields.hosp_no' => 'sometimes|string',
            'fields.hosp_reg_no' => 'sometimes|string',
            'fields.hosp_cas_no' => 'sometimes|string',

            'fields.ptype_code' => 'sometimes|string',
            'fields.pat_sex' => 'sometimes|string|max:10',
            'fields.pat_date_of_birth' => 'sometimes|date',

            'fields.age_years' => 'sometimes|integer',
            'fields.age_months' => 'sometimes|integer',
            'fields.age_days' => 'sometimes|integer',

            'fields.pat_current_address_region' => 'sometimes|string',
            'fields.pat_current_address_province' => 'sometimes|string',
            'fields.pat_current_address_city' => 'sometimes|string',

            'fields.temp_regcode' => 'sometimes|string',
            'fields.temp_provcode' => 'sometimes|string',
            'fields.temp_citycode' => 'sometimes|string',
            'fields.pat_phil_health_no' => 'sometimes|string',

            'fields.plc_provcode' => 'sometimes|string',
            'fields.plc_regcode' => 'sometimes|string',
            'fields.plc_ctycode' => 'sometimes|string',
            'fields.inj_date' => 'sometimes|date',
            'fields.inj_time' => 'sometimes|date_format:H:i',
            'fields.encounter_date' => 'sometimes|date',
            'fields.encounter_time' => 'sometimes|date_format:H:i',
            'fields.inj_intent_code' => 'sometimes|string',
            'fields.vawcyn' => 'sometimes|string',
            'fields.first_aid_code' => 'sometimes|string',
            'fields.firstaid_others' => 'sometimes|string',
            'fields.firstaid_others2' => 'sometimes|string',
            'fields.mult_inj' => 'sometimes|string|max:10',
            'fields.noi_abrasion' => 'sometimes|string|max:10',
            'fields.noi_abradtl' => 'sometimes|string',
            'fields.noi_avulsion' => 'sometimes|string|max:10',
            'fields.noi_avuldtl' => 'sometimes|string',
            'fields.noi_burn_r' => 'sometimes|string|max:10',
            'fields.noi_burndtl' => 'sometimes|string',
            'fields.noi_concussion' => 'sometimes|string|max:10',
            'fields.noi_concussiondtl' => 'sometimes|string',
            'fields.noi_contusion' => 'sometimes|string|max:10',
            'fields.noi_contudtl' => 'sometimes|string',
            'fields.noi_frac_clo' => 'sometimes|string|max:10',
            'fields.noi_frcldtl' => 'sometimes|string',
            'fields.noi_frac_ope' => 'sometimes|string|max:10',
            'fields.noi_fropdtl' => 'sometimes|string',
            'fields.noi_owound' => 'sometimes|string|max:10',
            'fields.noi_owoudtl' => 'sometimes|string',
            'fields.noi_amp' => 'sometimes|string|max:10',
            'fields.noi_ampdtl' => 'sometimes|string',
            'fields.noi_others' => 'sometimes|string|max:10',
            'fields.noi_otherinj' => 'sometimes|string',
            'fields.ext_bite' => 'sometimes|string|max:10',
            'fields.ext_bite_sp' => 'sometimes|string',
            'fields.ext_burn_r' => 'sometimes|string|max:10',
            'fields.ref_burn_code' => 'sometimes|string',
            'fields.ext_burn_sp' => 'sometimes|string',
            'fields.ext_chem' => 'sometimes|string|max:10',
            'fields.ext_chem_sp' => 'sometimes|string',
            'fields.ext_sharp' => 'sometimes|string|max:10',
            'fields.ext_sharp_sp' => 'sometimes|string',
            'fields.ext_drown_r' => 'sometimes|string|max:10',
            'fields.ref_drowning_code' => 'sometimes|string',
            'fields.ext_drown_sp' => 'sometimes|string',
            'fields.ext_expo_nature_r' => 'sometimes|string|max:10',
            'fields.ref_expnature_code' => 'sometimes|string',
            'fields.ext_expo_nature_sp' => 'sometimes|string',
            'fields.ext_fall' => 'sometimes|string|max:10',
            'fields.ext_falldtl' => 'sometimes|string',
            'fields.ext_firecracker_r' => 'sometimes|string|max:10',
            'fields.firecracker_code' => 'sometimes|string',
            'fields.ext_firecracker_sp' => 'sometimes|string',
            'fields.ext_sexual' => 'sometimes|string|max:10',
            'fields.ext_gun' => 'sometimes|string|max:10',
            'fields.ext_gun_sp' => 'sometimes|string',
            'fields.ext_hang' => 'sometimes|string|max:10',
            'fields.ext_maul' => 'sometimes|string|max:10',
            'fields.ext_transport' => 'sometimes|string|max:10',
            'fields.vehicle_type_id' => 'sometimes|string',
            'fields.ref_veh_acctype_code' => 'sometimes|string',
            'fields.vehicle_code' => 'sometimes|string',
            'fields.pat_veh_sp' => 'sometimes|string',
            'fields.etc_veh' => 'sometimes|string',
            'fields.etc_veh_sp' => 'sometimes|string',
            'fields.position_code' => 'sometimes|string',
            'fields.pos_pat_sp' => 'sometimes|string',
            'fields.ext_other' => 'sometimes|string|max:10',
            'fields.ext_other_sp' => 'sometimes|string',
            'fields.place_occ_code' => 'sometimes|string',
            'fields.poc_wp_spec' => 'sometimes|string',
            'fields.poc_etc_spec' => 'sometimes|string',
            'fields.activity_code' => 'sometimes|string',
            'fields.act_etc_spec' => 'sometimes|string',
            'fields.risk_alcliq' => 'sometimes|string|max:10',
            'fields.risk_none' => 'sometimes|string|max:10',
            'fields.risk_sleep' => 'sometimes|string|max:10',
            'fields.risk_smoke' => 'sometimes|string|max:10',
            'fields.risk_mobpho' => 'sometimes|string|max:10',
            'fields.risk_other' => 'sometimes|string|max:10',
            'fields.risk_etc_spec' => 'sometimes|string',
            'fields.safe_none' => 'sometimes|string|max:10',
            'fields.safe_unkn' => 'sometimes|string|max:10',
            'fields.safe_airbag' => 'sometimes|string|max:10',
            'fields.safe_helmet' => 'sometimes|string|max:10',
            'fields.safe_cseat' => 'sometimes|string|max:10',
            'fields.safe_sbelt' => 'sometimes|string|max:10',
            'fields.safe_drown' => 'sometimes|string|max:10',
            'fields.safe_other' => 'sometimes|string|max:10',
            'fields.safe_other_sp' => 'sometimes|string',
            'fields.trans_ref' => 'sometimes|string',
            'fields.trans_ref2' => 'sometimes|string',
            'fields.ref_physician' => 'sometimes|string',
            'fields.ref_hosp_code' => 'sometimes|string',
            'fields.ref_hosp_code_sp' => 'sometimes|string',
            'fields.status_code' => 'sometimes|string',
            'fields.mode_transport_code' => 'sometimes|string',
            'fields.stat_reachdtl' => 'sometimes|string',
            'fields.diagnosis' => 'sometimes|string',
            'fields.icd_10_external_er' => 'sometimes|string',
            'fields.icd_10_nature_er' => 'sometimes|string',
            'fields.disposition_code' => 'sometimes|string',
            'fields.disp_er_sp' => 'sometimes|string',
            'fields.disp_er_sp_oth' => 'sometimes|string',
            'fields.outcome_code' => 'sometimes|string',
            'fields.complete_diagnosis' => 'sometimes|string',
            'fields.disp_inpat' => 'sometimes|string',
            'fields.disp_inpat_oth' => 'sometimes|string',
            'fields.disp_inpat_sp' => 'sometimes|string',
            'fields.disp_inpat_sp2' => 'sometimes|string',
            'fields.icd10_nature_inpatient' => 'sometimes|string',
            'fields.outcome_inpat' => 'sometimes|string',
            'fields.icd_10_ext_inpatient' => 'sometimes|string',
            'fields.comments' => 'sometimes|string',
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
            'fields.id' => 'nullable|integer',
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
