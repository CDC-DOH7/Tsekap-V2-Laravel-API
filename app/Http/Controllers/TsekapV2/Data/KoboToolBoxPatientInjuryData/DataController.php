<?php

namespace App\Http\Controllers\TsekapV2\Data\KoboToolBoxPatientInjuryData;

use App\Http\Controllers\Controller;
use App\Models\TsekapV2\Data\KoboToolBoxPatientInjuryData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DataController extends Controller
{
    public function retrieveKoboToolBoxPatientInjuryDataWithoutFacility(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filter' => 'nullable|string|max:50',
            'keyword' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        $filter = $request->query('filter', 'name_of_reporting_facility');
        $keyword = trim((string) $request->query('keyword', ''));

        $query = KoboToolBoxPatientInjuryData::query();

        if ($keyword !== '') {
            $query->where(function ($q) use ($filter, $keyword) {
                $columns = [
                    'id' => 'id',
                    'name_of_reporting_facility' => 'name_of_reporting_facility',
                    'hospital_case_no' => 'hospital_case_no',
                    'last_name' => 'last_name',
                    'first_name' => 'first_name',
                    'middle_name' => 'middle_name',
                    'sex' => 'sex',
                    'date_of_birth' => 'date_of_birth',
                    'date_and_time_of_injury' => 'date_and_time_of_injury',
                    'date_and_time_of_consultation' => 'date_and_time_of_consultation',
                    'status' => 'status',
                ];

                if ($filter === 'date_of_birth' || $filter === 'date_and_time_of_injury' || $filter === 'date_and_time_of_consultation') {
                    $parsedDate = date('Y-m-d', strtotime($keyword));
                    $q->where($columns[$filter], 'like', "%{$parsedDate}%");
                } elseif (isset($columns[$filter])) {
                    $q->where($columns[$filter], 'like', "%{$keyword}%");
                } else {
                    $q->where(function ($inner) use ($keyword) {
                        $inner->where('name_of_reporting_facility', 'like', "%{$keyword}%")
                            ->orWhere('hospital_case_no', 'like', "%{$keyword}%")
                            ->orWhere('last_name', 'like', "%{$keyword}%")
                            ->orWhere('first_name', 'like', "%{$keyword}%")
                            ->orWhere('middle_name', 'like', "%{$keyword}%")
                            ->orWhere('sex', 'like', "%{$keyword}%")
                            ->orWhere('status', 'like', "%{$keyword}%")
                            ->orWhere('id', '=', $keyword)
                            ->orWhere('date_of_birth', 'like', "%{$keyword}%")
                            ->orWhere('date_and_time_of_injury', 'like', "%{$keyword}%")
                            ->orWhere('date_and_time_of_consultation', 'like', "%{$keyword}%");
                    });
                }
            });
        }

        $results = $query->orderByDesc('created_at')->simplePaginate(30);

        return response()->json($results, 200);
    }

    public function addKoboToolBoxPatientInjuryData(Request $request): JsonResponse
    {
        $fields = $request->input('fields', []);

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.name_of_reporting_facility' => 'required|string',
            'fields.start' => 'nullable|date',
            'fields.end' => 'nullable|date',
            'fields.type_of_dru' => 'nullable|string',
            'fields.address_of_dru_province_huc' => 'nullable|string',
            'fields.type_of_patient' => 'nullable|string',
            'fields.hospital_case_no' => 'nullable|string',
            'fields.last_name' => 'nullable|string',
            'fields.first_name' => 'nullable|string',
            'fields.middle_name' => 'nullable|string',
            'fields.sex' => 'nullable|string',
            'fields.date_of_birth' => 'nullable|date',
            'fields.age' => 'nullable|integer',
            'fields.age_in_days_months_years' => 'nullable|string',
            'fields.permanent_province' => 'nullable|string',
            'fields.permanent_muncity' => 'nullable|string',
            'fields.permanent_barangay' => 'nullable|string',
            'fields.temporary_province' => 'nullable|string',
            'fields.temporary_muncity' => 'nullable|string',
            'fields.temporary_barangay' => 'nullable|string',
            'fields.philhealth_number' => 'nullable|string',
            'fields.place_of_injury_province' => 'nullable|string',
            'fields.place_of_injury_muncity' => 'nullable|string',
            'fields.place_of_injury_barangay' => 'nullable|string',
            'fields.place_of_injury_sitio_purok_street' => 'nullable|string',
            'fields.date_and_time_of_injury' => 'nullable|date',
            'fields.date_and_time_of_consultation' => 'nullable|date',
            'fields.injury_intent' => 'nullable|string',
            'fields.first_aid_given' => 'nullable|string',
            'fields.what_first_aid_was_given' => 'nullable|string',
            'fields.who_gave_the_first_aid' => 'nullable|string',
            'fields.multiple_injury_ies' => 'nullable|string',
            'fields.noi_all' => 'nullable|string',
            'fields.noi_abrasion' => 'nullable|string',
            'fields.noi_avulsion' => 'nullable|string',
            'fields.noi_burn' => 'nullable|string',
            'fields.noi_concussion' => 'nullable|string',
            'fields.noi_contusion' => 'nullable|string',
            'fields.noi_fracture' => 'nullable|string',
            'fields.noi_open_wound' => 'nullable|string',
            'fields.noi_trauma_amp' => 'nullable|string',
            'fields.noi_others' => 'nullable|string',
            'fields.abrasion_details' => 'nullable|string',
            'fields.avulsion_details' => 'nullable|string',
            'fields.burn_details' => 'nullable|string',
            'fields.burn_site' => 'nullable|string',
            'fields.concussion_details' => 'nullable|string',
            'fields.contusion_details' => 'nullable|string',
            'fields.fracture_details' => 'nullable|string',
            'fields.fracture_type_details' => 'nullable|string',
            'fields.open_wound_details' => 'nullable|string',
            'fields.traumatic_amputation_details' => 'nullable|string',
            'fields.noi_other_details' => 'nullable|string',
            'fields.ext_causes' => 'nullable|string',
            'fields.ext_bites_stings' => 'nullable|string',
            'fields.ext_burns' => 'nullable|string',
            'fields.ext_chemical_subs' => 'nullable|string',
            'fields.ext_sharp_objects' => 'nullable|string',
            'fields.ext_drowning' => 'nullable|string',
            'fields.ext_expo_nature' => 'nullable|string',
            'fields.ext_fall' => 'nullable|string',
            'fields.ext_firecracker' => 'nullable|string',
            'fields.ext_sexual_assault_abuse_rape_alleged' => 'nullable|string',
            'fields.ext_gunshot' => 'nullable|string',
            'fields.ext_hanging_strangulation' => 'nullable|string',
            'fields.ext_mauling_assault' => 'nullable|string',
            'fields.ext_transport_vehicular_accident' => 'nullable|string',
            'fields.ext_others' => 'nullable|string',
            'fields.bite_sting_details' => 'nullable|string',
            'fields.burns_details' => 'nullable|string',
            'fields.chemical_subs_details' => 'nullable|string',
            'fields.contact_with_sharp_objects_details' => 'nullable|string',
            'fields.drowning_details' => 'nullable|string',
            'fields.expo_nature_details' => 'nullable|string',
            'fields.fall_details' => 'nullable|string',
            'fields.firecracker_details' => 'nullable|string',
            'fields.gunshot_details' => 'nullable|string',
            'fields.hanging_strangulation_details' => 'nullable|string',
            'fields.mauling_assault_details' => 'nullable|string',
            'fields.transport_vehicular_accident_details' => 'nullable|string',
            'fields.ext_coi_other_details' => 'nullable|string',
            'fields.for_transport_vehicular_accidents_only' => 'nullable|string',
            'fields.vehicular_accident_type' => 'nullable|string',
            'fields.vehicles_involved_patients_vehicle' => 'nullable|string',
            'fields.vehicles_involved_patients_vehicle_others_details' => 'nullable|string',
            'fields.vehicles_involved_other_vehicle_object_involved' => 'nullable|string',
            'fields.vehicles_involved_other_vehicle_object_involved_others_details' => 'nullable|string',
            'fields.position_of_patient' => 'nullable|string',
            'fields.position_of_patient_others_details' => 'nullable|string',
            'fields.place_of_occurrence' => 'nullable|string',
            'fields.workplace_specify' => 'nullable|string',
            'fields.place_of_occurence_others_details' => 'nullable|string',
            'fields.activity' => 'nullable|string',
            'fields.activity_details' => 'nullable|string',
            'fields.other_risk_all' => 'nullable|string',
            'fields.other_risk_alcohol_liquor' => 'nullable|string',
            'fields.other_risk_mobile_phone' => 'nullable|string',
            'fields.other_risk_sleepy' => 'nullable|string',
            'fields.other_risk_smoking' => 'nullable|string',
            'fields.other_risk_others' => 'nullable|string',
            'fields.other_risk_other_details' => 'nullable|string',
            'fields.safety_all' => 'nullable|string',
            'fields.safety_none' => 'nullable|string',
            'fields.safety_childseat' => 'nullable|string',
            'fields.safety_airbag' => 'nullable|string',
            'fields.safety_lifevest' => 'nullable|string',
            'fields.safety_helmet' => 'nullable|string',
            'fields.safety_seatbelt' => 'nullable|string',
            'fields.safety_unknown' => 'nullable|string',
            'fields.safety_others' => 'nullable|string',
            'fields.safety_others_details' => 'nullable|string',
            'fields.type_of_patient_hospital_facility_data' => 'nullable|string',
            'fields.transferred_hosp_op' => 'nullable|string',
            'fields.referred_hosp_op' => 'nullable|string',
            'fields.orig_physician_op' => 'nullable|string',
            'fields.status_reach_op' => 'nullable|string',
            'fields.if_alive_op' => 'nullable|string',
            'fields.mot_to_the_hospital_facility_op' => 'nullable|string',
            'fields.mot_to_the_hospital_others_details_op' => 'nullable|string',
            'fields.initial_impression_op' => 'nullable|string',
            'fields.icd_10_nature_op' => 'nullable|string',
            'fields.icd_10_ext_op' => 'nullable|string',
            'fields.disposition_op' => 'nullable|string',
            'fields.specify_facility_transferred_to_op' => 'nullable|string',
            'fields.outcome_op' => 'nullable|string',
            'fields.initial_admitting_final_diagnosis_ip' => 'nullable|string',
            'fields.disposition_ip' => 'nullable|string',
            'fields.facility_transfered_to_ip' => 'nullable|string',
            'fields.disposition_others_details_ip' => 'nullable|string',
            'fields.outcome_ip' => 'nullable|string',
            'fields.icd_10_nature_ip' => 'nullable|string',
            'fields.icd_10_ext_ip' => 'nullable|string',
            'fields.name_of_encoder' => 'nullable|string',
            'fields.designation_of_encoder' => 'nullable|string',
            'fields.contact_number_of_encoder' => 'nullable|string',
            'fields.address_of_dru' => 'nullable|string',
            'fields._id' => 'nullable|integer',
            'fields.uuid' => 'nullable|string',
            'fields.submission_time' => 'nullable|date',
            'fields.validation_status' => 'nullable|string',
            'fields.notes' => 'nullable|string',
            'fields.status' => 'nullable|string',
            'fields.submitted_by' => 'nullable|string',
            'fields.version' => 'nullable|string',
            'fields.tags' => 'nullable|string',
            'fields.meta_root_uuid' => 'nullable|string',
            'fields.index' => 'nullable|integer',
        ]);

        // --- allow duplicates
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $duplicate = KoboToolBoxPatientInjuryData::query()
            ->where('index', $fields['index'])
            ->where('meta_root_uuid', $fields['meta_root_uuid'])
            ->where('name_of_reporting_facility', $fields['name_of_reporting_facility'])
            ->where('date_and_time_of_injury', $fields['date_and_time_of_injury'] ?? null)
            ->where('hospital_case_no', $fields['hospital_case_no'] ?? null)
            ->exists();

        if ($duplicate) {
            return response()->json(['error' => 'Duplicate KoboToolBox entry detected.'], 409);
        }

        try {
            $entry = new KoboToolBoxPatientInjuryData();
            $entry->fill($fields);
            $entry->save();

            return response()->json([
                'message' => 'KoboToolBox entry successfully saved.',
                'id' => $entry->getAttribute('id'),
            ], 200);
        } catch (Exception $e) {
            Log::error('An error occurred while adding KoboToolBox patient injury data: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    public function updateKoboToolBoxPatientInjuryData(Request $request): JsonResponse
    {
        $fields = $request->input('fields', []);

        $validator = Validator::make($request->all(), [
            'fields' => 'nullable|array',
            'fields.id' => 'nullable|integer',
            'fields.name_of_reporting_facility' => 'sometimes|string',
            'fields.start' => 'sometimes|date',
            'fields.end' => 'sometimes|date',
            'fields.type_of_dru' => 'sometimes|string',
            'fields.address_of_dru_province_huc' => 'sometimes|string',
            'fields.type_of_patient' => 'sometimes|string',
            'fields.hospital_case_no' => 'sometimes|string',
            'fields.last_name' => 'sometimes|string',
            'fields.first_name' => 'sometimes|string',
            'fields.middle_name' => 'sometimes|string',
            'fields.sex' => 'sometimes|string',
            'fields.date_of_birth' => 'sometimes|date',
            'fields.age' => 'sometimes|integer',
            'fields.age_in_days_months_years' => 'sometimes|string',
            'fields.permanent_province' => 'sometimes|string',
            'fields.permanent_muncity' => 'sometimes|string',
            'fields.permanent_barangay' => 'sometimes|string',
            'fields.temporary_province' => 'sometimes|string',
            'fields.temporary_muncity' => 'sometimes|string',
            'fields.temporary_barangay' => 'sometimes|string',
            'fields.philhealth_number' => 'sometimes|string',
            'fields.place_of_injury_province' => 'sometimes|string',
            'fields.place_of_injury_muncity' => 'sometimes|string',
            'fields.place_of_injury_barangay' => 'sometimes|string',
            'fields.place_of_injury_sitio_purok_street' => 'sometimes|string',
            'fields.date_and_time_of_injury' => 'sometimes|date',
            'fields.date_and_time_of_consultation' => 'sometimes|date',
            'fields.injury_intent' => 'sometimes|string',
            'fields.first_aid_given' => 'sometimes|string',
            'fields.what_first_aid_was_given' => 'sometimes|string',
            'fields.who_gave_the_first_aid' => 'sometimes|string',
            'fields.multiple_injury_ies' => 'sometimes|string',
            'fields.noi_all' => 'sometimes|string',
            'fields.noi_abrasion' => 'sometimes|string',
            'fields.noi_avulsion' => 'sometimes|string',
            'fields.noi_burn' => 'sometimes|string',
            'fields.noi_concussion' => 'sometimes|string',
            'fields.noi_contusion' => 'sometimes|string',
            'fields.noi_fracture' => 'sometimes|string',
            'fields.noi_open_wound' => 'sometimes|string',
            'fields.noi_trauma_amp' => 'sometimes|string',
            'fields.noi_others' => 'sometimes|string',
            'fields.abrasion_details' => 'sometimes|string',
            'fields.avulsion_details' => 'sometimes|string',
            'fields.burn_details' => 'sometimes|string',
            'fields.burn_site' => 'sometimes|string',
            'fields.concussion_details' => 'sometimes|string',
            'fields.contusion_details' => 'sometimes|string',
            'fields.fracture_details' => 'sometimes|string',
            'fields.fracture_type_details' => 'sometimes|string',
            'fields.open_wound_details' => 'sometimes|string',
            'fields.traumatic_amputation_details' => 'sometimes|string',
            'fields.noi_other_details' => 'sometimes|string',
            'fields.ext_causes' => 'sometimes|string',
            'fields.ext_bites_stings' => 'sometimes|string',
            'fields.ext_burns' => 'sometimes|string',
            'fields.ext_chemical_subs' => 'sometimes|string',
            'fields.ext_sharp_objects' => 'sometimes|string',
            'fields.ext_drowning' => 'sometimes|string',
            'fields.ext_expo_nature' => 'sometimes|string',
            'fields.ext_fall' => 'sometimes|string',
            'fields.ext_firecracker' => 'sometimes|string',
            'fields.ext_sexual_assault_abuse_rape_alleged' => 'sometimes|string',
            'fields.ext_gunshot' => 'sometimes|string',
            'fields.ext_hanging_strangulation' => 'sometimes|string',
            'fields.ext_mauling_assault' => 'sometimes|string',
            'fields.ext_transport_vehicular_accident' => 'sometimes|string',
            'fields.ext_others' => 'sometimes|string',
            'fields.bite_sting_details' => 'sometimes|string',
            'fields.burns_details' => 'sometimes|string',
            'fields.chemical_subs_details' => 'sometimes|string',
            'fields.contact_with_sharp_objects_details' => 'sometimes|string',
            'fields.drowning_details' => 'sometimes|string',
            'fields.expo_nature_details' => 'sometimes|string',
            'fields.fall_details' => 'sometimes|string',
            'fields.firecracker_details' => 'sometimes|string',
            'fields.gunshot_details' => 'sometimes|string',
            'fields.hanging_strangulation_details' => 'sometimes|string',
            'fields.mauling_assault_details' => 'sometimes|string',
            'fields.transport_vehicular_accident_details' => 'sometimes|string',
            'fields.ext_coi_other_details' => 'sometimes|string',
            'fields.for_transport_vehicular_accidents_only' => 'sometimes|string',
            'fields.vehicular_accident_type' => 'sometimes|string',
            'fields.vehicles_involved_patients_vehicle' => 'sometimes|string',
            'fields.vehicles_involved_patients_vehicle_others_details' => 'sometimes|string',
            'fields.vehicles_involved_other_vehicle_object_involved' => 'sometimes|string',
            'fields.vehicles_involved_other_vehicle_object_involved_others_details' => 'sometimes|string',
            'fields.position_of_patient' => 'sometimes|string',
            'fields.position_of_patient_others_details' => 'sometimes|string',
            'fields.place_of_occurrence' => 'sometimes|string',
            'fields.workplace_specify' => 'sometimes|string',
            'fields.place_of_occurence_others_details' => 'sometimes|string',
            'fields.activity' => 'sometimes|string',
            'fields.activity_details' => 'sometimes|string',
            'fields.other_risk_all' => 'sometimes|string',
            'fields.other_risk_alcohol_liquor' => 'sometimes|string',
            'fields.other_risk_mobile_phone' => 'sometimes|string',
            'fields.other_risk_sleepy' => 'sometimes|string',
            'fields.other_risk_smoking' => 'sometimes|string',
            'fields.other_risk_others' => 'sometimes|string',
            'fields.other_risk_other_details' => 'sometimes|string',
            'fields.safety_all' => 'sometimes|string',
            'fields.safety_none' => 'sometimes|string',
            'fields.safety_childseat' => 'sometimes|string',
            'fields.safety_airbag' => 'sometimes|string',
            'fields.safety_lifevest' => 'sometimes|string',
            'fields.safety_helmet' => 'sometimes|string',
            'fields.safety_seatbelt' => 'sometimes|string',
            'fields.safety_unknown' => 'sometimes|string',
            'fields.safety_others' => 'sometimes|string',
            'fields.safety_others_details' => 'sometimes|string',
            'fields.type_of_patient_hospital_facility_data' => 'sometimes|string',
            'fields.transferred_hosp_op' => 'sometimes|string',
            'fields.referred_hosp_op' => 'sometimes|string',
            'fields.orig_physician_op' => 'sometimes|string',
            'fields.status_reach_op' => 'sometimes|string',
            'fields.if_alive_op' => 'sometimes|string',
            'fields.mot_to_the_hospital_facility_op' => 'sometimes|string',
            'fields.mot_to_the_hospital_others_details_op' => 'sometimes|string',
            'fields.initial_impression_op' => 'sometimes|string',
            'fields.icd_10_nature_op' => 'sometimes|string',
            'fields.icd_10_ext_op' => 'sometimes|string',
            'fields.disposition_op' => 'sometimes|string',
            'fields.specify_facility_transferred_to_op' => 'sometimes|string',
            'fields.outcome_op' => 'sometimes|string',
            'fields.initial_admitting_final_diagnosis_ip' => 'sometimes|string',
            'fields.disposition_ip' => 'sometimes|string',
            'fields.facility_transfered_to_ip' => 'sometimes|string',
            'fields.disposition_others_details_ip' => 'sometimes|string',
            'fields.outcome_ip' => 'sometimes|string',
            'fields.icd_10_nature_ip' => 'sometimes|string',
            'fields.icd_10_ext_ip' => 'sometimes|string',
            'fields.name_of_encoder' => 'sometimes|string',
            'fields.designation_of_encoder' => 'sometimes|string',
            'fields.contact_number_of_encoder' => 'sometimes|string',
            'fields.address_of_dru' => 'sometimes|string',
            'fields._id' => 'sometimes|integer',
            'fields.uuid' => 'sometimes|string',
            'fields.submission_time' => 'sometimes|date',
            'fields.validation_status' => 'sometimes|string',
            'fields.notes' => 'sometimes|string',
            'fields.status' => 'sometimes|string',
            'fields.submitted_by' => 'sometimes|string',
            'fields.version' => 'sometimes|string',
            'fields.tags' => 'sometimes|string',
            'fields.meta_root_uuid' => 'sometimes|string',
            'fields.index' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $entry = KoboToolBoxPatientInjuryData::find($fields['id']);

        if (!$entry) {
            return response()->json(['error' => 'KoboToolBox entry not found.'], 404);
        }

        try {
            $entry->fill($fields);
            $entry->save();

            return response()->json(['message' => 'KoboToolBox entry successfully updated.'], 200);
        } catch (Exception $e) {
            Log::error('Error updating KoboToolBox patient injury data: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    public function deleteKoboToolBoxPatientInjuryData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fields.id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $id = $request->input('fields.id');

        try {
            $entry = KoboToolBoxPatientInjuryData::find($id);

            if (!$entry) {
                return response()->json(['error' => 'KoboToolBox entry not found.'], 404);
            }

            $entry->delete();

            return response()->json(['message' => 'KoboToolBox entry successfully deleted.'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting KoboToolBox patient injury data: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }
}
