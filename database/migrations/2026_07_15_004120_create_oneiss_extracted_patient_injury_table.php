<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::create('oneiss_extracted_patient_injury', function (Blueprint $table) {
                $table->increments('id');

                // Oneiss Data
                $table->unsignedInteger('pno')->index(); // pno -> NULLABLE
                $table->text('status')->nullable(); // status -> NULLABLE
                $table->text('pat_facility_no')->nullable(); // pat_facility_no -> NULLABLE
                $table->date('date_report')->nullable(); // date_report -> NULLABLE
                $table->time('time_report')->nullable(); // time_report -> NULLABLE
                $table->text('reg_no')->nullable(); // reg_no -> NULLABLE
                $table->text('tempreg_no')->nullable(); // tempreg_no -> NULLABLE
                $table->text('hosp_no')->nullable(); // hosp_no -> NULLABLE
                $table->text('hosp_reg_no')->nullable(); // hosp_reg_no -> NULLABLE
                $table->text('hosp_cas_no')->nullable(); // hosp_cas_no -> NULLABLE
                $table->text('ptype_code')->nullable(); // ptype_code -> NULLABLE
                $table->string('pat_sex', 10)->nullable(); // pat_sex -> NULLABLE
                $table->date('pat_date_of_birth')->nullable(); // pat_date_of_birth -> NULLABLE
                $table->integer('age_years')->nullable(); // age_years -> NULLABLE
                $table->integer('age_months')->nullable(); // age_months -> NULLABLE
                $table->integer('age_days')->nullable(); // age_days -> NULLABLE
                $table->text('pat_current_address_region')->nullable(); // pat_current_address_region -> NULLABLE
                $table->text('pat_current_address_province')->nullable(); // pat_current_address_province -> NULLABLE
                $table->text('pat_current_address_city')->nullable(); // pat_current_address_city -> NULLABLE
                $table->text('temp_regcode')->nullable(); // temp_regcode -> NULLABLE
                $table->text('temp_provcode')->nullable(); // temp_provcode -> NULLABLE
                $table->text('temp_citycode')->nullable(); // temp_citycode -> NULLABLE
                $table->text('pat_phil_health_no')->nullable(); // pat_phil_health_no -> NULLABLE
                $table->text('plc_provcode')->nullable(); // plc_provcode -> NULLABLE
                $table->text('plc_regcode')->nullable(); // plc_regcode -> NULLABLE
                $table->text('plc_ctycode')->nullable(); // plc_ctycode -> NULLABLE
                $table->date('inj_date')->nullable(); // inj_date -> NULLABLE
                $table->time('inj_time')->nullable(); // inj_time -> NULLABLE
                $table->date('encounter_date')->nullable(); // encounter_date -> NULLABLE
                $table->time('encounter_time')->nullable(); // encounter_time -> NULLABLE
                $table->text('inj_intent_code')->nullable(); // inj_intent_code -> NULLABLE
                $table->text('vawcyn')->nullable(); // vawcyn -> NULLABLE
                $table->text('first_aid_code')->nullable(); // first_aid_code -> NULLABLE
                $table->text('firstaid_others')->nullable(); // firstaid_others -> NULLABLE
                $table->text('firstaid_others2')->nullable(); // firstaid_others2 -> NULLABLE
                $table->String('mult_inj', 10)->nullable(); // mult_inj -> NULLABLE
                $table->String('noi_abrasion', 10)->nullable(); // noi_abrasion -> NULLABLE
                $table->text('noi_abradtl')->nullable(); // noi_abradtl -> NULLABLE
                $table->String('noi_avulsion', 10)->nullable(); // noi_avulsion -> NULLABLE
                $table->text('noi_avuldtl')->nullable(); // noi_avuldtl -> NULLABLE
                $table->String('noi_burn_r', 10)->nullable(); // noi_burn_r -> NULLABLE
                $table->text('noi_burndtl')->nullable(); // noi_burndtl -> NULLABLE
                $table->String('noi_concussion', 10)->nullable(); // noi_concussion -> NULLABLE
                $table->text('noi_concussiondtl')->nullable(); // noi_concussiondtl -> NULLABLE
                $table->String('noi_contusion', 10)->nullable(); // noi_contusion -> NULLABLE
                $table->text('noi_contudtl')->nullable(); // noi_contudtl -> NULLABLE
                $table->String('noi_frac_clo', 10)->nullable(); // noi_frac_clo -> NULLABLE
                $table->text('noi_frcldtl')->nullable(); // noi_frcldtl -> NULLABLE
                $table->String('noi_frac_ope', 10)->nullable(); // noi_frac_ope -> NULLABLE
                $table->text('noi_fropdtl')->nullable(); // noi_fropdtl -> NULLABLE
                $table->String('noi_owound', 10)->nullable(); // noi_owound -> NULLABLE
                $table->text('noi_owoudtl')->nullable(); // noi_owoudtl -> NULLABLE
                $table->String('noi_amp', 10)->nullable(); // noi_amp -> NULLABLE
                $table->text('noi_ampdtl')->nullable(); // noi_ampdtl -> NULLABLE
                $table->String('noi_others', 10)->nullable(); // noi_others -> NULLABLE
                $table->text('noi_otherinj')->nullable(); // noi_otherinj -> NULLABLE
                $table->String('ext_bite', 10)->nullable(); // ext_bite -> NULLABLE
                $table->text('ext_bite_sp')->nullable(); // ext_bite_sp -> NULLABLE
                $table->String('ext_burn_r', 10)->nullable(); // ext_burn_r -> NULLABLE
                $table->text('ref_burn_code')->nullable(); // ref_burn_code -> NULLABLE
                $table->text('ext_burn_sp')->nullable(); // ext_burn_sp -> NULLABLE
                $table->String('ext_chem', 10)->nullable(); // ext_chem -> NULLABLE
                $table->text('ext_chem_sp')->nullable(); // ext_chem_sp -> NULLABLE
                $table->String('ext_sharp', 10)->nullable(); // ext_sharp -> NULLABLE
                $table->text('ext_sharp_sp')->nullable(); // ext_sharp_sp -> NULLABLE
                $table->String('ext_drown_r', 10)->nullable(); // ext_drown_r -> NULLABLE
                $table->text('ref_drowning_code')->nullable(); // ref_drowning_code -> NULLABLE
                $table->text('ext_drown_sp')->nullable(); // ext_drown_sp -> NULLABLE
                $table->String('ext_expo_nature_r', 10)->nullable(); // ext_expo_nature_r -> NULLABLE
                $table->text('ref_expnature_code')->nullable(); // ref_expnature_code -> NULLABLE
                $table->text('ext_expo_nature_sp')->nullable(); // ext_expo_nature_sp -> NULLABLE
                $table->String('ext_fall', 10)->nullable(); // ext_fall -> NULLABLE
                $table->text('ext_falldtl')->nullable(); // ext_falldtl -> NULLABLE
                $table->String('ext_firecracker_r', 10)->nullable(); // ext_firecracker_r -> NULLABLE
                $table->text('firecracker_code')->nullable()->nullable(); // firecracker_code -> NULLABLE
                $table->text('ext_firecracker_sp')->nullable()->nullable(); // ext_firecracker_sp -> NULLABLE
                $table->String('ext_sexual', 10)->nullable(); // ext_sexual -> NULLABLE
                $table->String('ext_gun', 10)->nullable(); // ext_gun -> NULLABLE
                $table->text('ext_gun_sp')->nullable(); // ext_gun_sp -> NULLABLE
                $table->String('ext_hang', 10)->nullable(); // ext_hang -> NULLABLE
                $table->String('ext_maul', 10)->nullable(); // ext_maul -> NULLABLE
                $table->String('ext_transport', 10)->nullable(); // ext_transport -> NULLABLE
                $table->text('vehicle_type_id')->nullable(); // vehicle_type_id -> NULLABLE
                $table->text('ref_veh_acctype_code')->nullable(); // ref_veh_acctype_code -> NULLABLE
                $table->text('vehicle_code')->nullable(); // vehicle_code -> NULLABLE
                $table->text('pat_veh_sp')->nullable(); // pat_veh_sp -> NULLABLE
                $table->text('etc_veh')->nullable(); // etc_veh -> NULLABLE
                $table->text('etc_veh_sp')->nullable(); // etc_veh_sp -> NULLABLE
                $table->text('position_code')->nullable(); // position_code -> NULLABLE
                $table->text('pos_pat_sp')->nullable(); // pos_pat_sp -> NULLABLE
                $table->String('ext_other', 10)->nullable(); // ext_other -> NULLABLE
                $table->text('ext_other_sp')->nullable(); // ext_other_sp -> NULLABLE
                $table->text('place_occ_code')->nullable(); // place_occ_code -> NULLABLE
                $table->text('poc_wp_spec')->nullable(); // poc_wp_spec -> NULLABLE
                $table->text('poc_etc_spec')->nullable(); // poc_etc_spec -> NULLABLE
                $table->text('activity_code')->nullable(); // activity_code -> NULLABLE
                $table->String('act_etc_spec', 10)->nullable(); // act_etc_spec -> NULLABLE
                $table->String('risk_alcliq', 10)->nullable(); // risk_alcliq -> NULLABLE
                $table->String('risk_none', 10)->nullable(); // risk_none -> NULLABLE
                $table->String('risk_sleep', 10)->nullable(); // risk_sleep -> NULLABLE
                $table->String('risk_smoke', 10)->nullable(); // risk_smoke -> NULLABLE
                $table->String('risk_mobpho', 10)->nullable(); // risk_mobpho -> NULLABLE
                $table->String('risk_other', 10)->nullable(); // risk_other -> NULLABLE
                $table->text('risk_etc_spec')->nullable(); // risk_etc_spec -> NULLABLE
                $table->String('safe_none', 10)->nullable(); // safe_none -> NULLABLE
                $table->String('safe_unkn', 10)->nullable(); // safe_unkn -> NULLABLE
                $table->String('safe_airbag', 10)->nullable(); // safe_airbag -> NULLABLE
                $table->String('safe_helmet', 10)->nullable(); // safe_helmet -> NULLABLE
                $table->String('safe_cseat', 10)->nullable(); // safe_cseat -> NULLABLE
                $table->String('safe_sbelt', 10)->nullable(); // safe_sbelt -> NULLABLE
                $table->String('safe_drown', 10)->nullable(); // safe_drown -> NULLABLE
                $table->String('safe_other', 10)->nullable(); // safe_other -> NULLABLE
                $table->text('safe_other_sp')->nullable(); // safe_other_sp -> NULLABLE
                $table->text('trans_ref')->nullable(); // trans_ref -> NULLABLE
                $table->text('trans_ref2')->nullable(); // trans_ref2 -> NULLABLE
                $table->text('ref_physician')->nullable(); // ref_physician -> NULLABLE
                $table->text('ref_hosp_code')->nullable(); // ref_hosp_code -> NULLABLE
                $table->text('ref_hosp_code_sp')->nullable(); // ref_hosp_code_sp -> NULLABLE
                $table->text('status_code')->nullable(); // status_code -> NULLABLE
                $table->text('mode_transport_code')->nullable(); // mode_transport_code -> NULLABLE
                $table->text('stat_reachdtl')->nullable(); // stat_reachdtl -> NULLABLE
                $table->text('diagnosis')->nullable(); // diagnosis -> NULLABLE
                $table->text('icd_10_external_er')->nullable(); // icd_10_external_er -> NULLABLE
                $table->text('icd_10_nature_er')->nullable(); // icd_10_nature_er -> NULLABLE
                $table->text('disposition_code')->nullable(); // disposition_code -> NULLABLE
                $table->text('disp_er_sp')->nullable(); // disp_er_sp -> NULLABLE
                $table->text('disp_er_sp_oth')->nullable(); // disp_er_sp_oth -> NULLABLE
                $table->text('outcome_code')->nullable(); // outcome_code -> NULLABLE
                $table->text('complete_diagnosis')->nullable(); // complete_diagnosis -> NULLABLE
                $table->text('disp_inpat')->nullable(); // disp_inpat -> NULLABLE
                $table->text('disp_inpat_oth')->nullable(); // disp_inpat_oth -> NULLABLE
                $table->text('disp_inpat_sp')->nullable(); // disp_inpat_sp -> NULLABLE
                $table->text('disp_inpat_sp2')->nullable(); // disp_inpat_sp2 -> NULLABLE
                $table->text('icd10_nature_inpatient')->nullable(); // icd10_nature_inpatient -> NULLABLE
                $table->text('outcome_inpat')->nullable(); // outcome_inpat -> NULLABLE
                $table->text('icd_10_ext_inpatient')->nullable(); // icd_10_ext_inpatient -> NULLABLE
                $table->text('comments')->nullable(); // comments -> NULLABLE

                // system metadata
                $table->timestamps();
            });
        } catch (\Exception $e) {
            Log::error('Migration failed (up): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('oneiss_extracted_patient_injury');
        } catch (\Exception $e) {
            Log::error('Migration failed (down): ' . $e->getMessage());
            throw $e;
        }
    }
};
