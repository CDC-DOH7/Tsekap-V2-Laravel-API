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
                $table->unsignedInteger('pno')->index(); // pno -> NOT NULL
                $table->text('status'); // status -> NOT NULL
                $table->text('pat_facility_no'); // pat_facility_no -> NOT NULL
                $table->date('date_report'); // date_report -> NOT NULL
                $table->time('time_report'); // time_report -> NOT NULL
                $table->text('reg_no')->nullable(); // reg_no -> NULLABLE
                $table->text('tempreg_no')->nullable(); // tempreg_no -> NULLABLE
                $table->text('hosp_no')->nullable(); // hosp_no -> NULLABLE
                $table->text('hosp_reg_no')->nullable(); // hosp_reg_no -> NULLABLE
                $table->text('hosp_cas_no')->nullable(); // hosp_cas_no -> NULLABLE
                $table->text('ptype_code'); // ptype_code -> NOT NULL
                $table->string('pat_sex', 10); // pat_sex -> NOT NULL
                $table->date('pat_date_of_birth'); // pat_date_of_birth -> NOT NULL
                $table->integer('age_years'); // age_years -> NOT NULL
                $table->integer('age_months')->nullable(); // age_months -> NULLABLE
                $table->integer('age_days')->nullable(); // age_days -> NULLABLE
                $table->text('pat_current_address_region'); // pat_current_address_region -> NOT NULL
                $table->text('pat_current_address_province'); // pat_current_address_province -> NOT NULL
                $table->text('pat_current_address_city'); // pat_current_address_city -> NOT NULL
                $table->text('temp_regcode')->nullable(); // temp_regcode -> NULLABLE
                $table->text('temp_provcode')->nullable(); // temp_provcode -> NULLABLE
                $table->text('temp_citycode')->nullable(); // temp_citycode -> NULLABLE
                $table->text('pat_phil_health_no')->nullable(); // pat_phil_health_no -> NULLABLE
                $table->text('plc_provcode'); // plc_provcode -> NOT NULL
                $table->text('plc_regcode'); // plc_regcode -> NOT NULL
                $table->text('plc_ctycode'); // plc_ctycode -> NOT NULL
                $table->date('inj_date'); // inj_date -> NOT NULL
                $table->time('inj_time'); // inj_time -> NOT NULL
                $table->date('encounter_date'); // encounter_date -> NOT NULL
                $table->time('encounter_time'); // encounter_time -> NOT NULL
                $table->text('inj_intent_code'); // inj_intent_code -> NOT NULL
                $table->text('vawcyn')->nullable(); // vawcyn -> NULLABLE
                $table->text('first_aid_code'); // first_aid_code -> NOT NULL
                $table->text('firstaid_others')->nullable(); // firstaid_others -> NULLABLE
                $table->text('firstaid_others2')->nullable(); // firstaid_others2 -> NULLABLE
                $table->String('mult_inj', 10); // mult_inj -> NOT NULL
                $table->String('noi_abrasion', 10); // noi_abrasion -> NOT NULL
                $table->text('noi_abradtl'); // noi_abradtl -> NOT NULL
                $table->String('noi_avulsion', 10)->nullable(); // noi_avulsion -> NULLABLE
                $table->text('noi_avuldtl')->nullable(); // noi_avuldtl -> NULLABLE
                $table->String('noi_burn_r', 10); // noi_burn_r -> NOT NULL
                $table->text('noi_burndtl')->nullable(); // noi_burndtl -> NULLABLE
                $table->String('noi_concussion', 10); // noi_concussion -> NOT NULL
                $table->text('noi_concussiondtl')->nullable(); // noi_concussiondtl -> NULLABLE
                $table->String('noi_contusion', 10); // noi_contusion -> NOT NULL
                $table->text('noi_contudtl')->nullable(); // noi_contudtl -> NULLABLE
                $table->String('noi_frac_clo', 10); // noi_frac_clo -> NOT NULL
                $table->text('noi_frcldtl')->nullable(); // noi_frcldtl -> NULLABLE
                $table->String('noi_frac_ope', 10); // noi_frac_ope -> NOT NULL
                $table->text('noi_fropdtl')->nullable(); // noi_fropdtl -> NULLABLE
                $table->String('noi_owound', 10); // noi_owound -> NOT NULL
                $table->text('noi_owoudtl')->nullable(); // noi_owoudtl -> NULLABLE
                $table->String('noi_amp', 10); // noi_amp -> NOT NULL
                $table->text('noi_ampdtl')->nullable(); // noi_ampdtl -> NULLABLE
                $table->String('noi_others', 10)->nullable(); // noi_others -> NULLABLE
                $table->text('noi_otherinj')->nullable(); // noi_otherinj -> NULLABLE
                $table->String('ext_bite', 10); // ext_bite -> NOT NULL
                $table->text('ext_bite_sp')->nullable(); // ext_bite_sp -> NULLABLE
                $table->String('ext_burn_r', 10); // ext_burn_r -> NOT NULL
                $table->text('ref_burn_code'); // ref_burn_code -> NOT NULL
                $table->text('ext_burn_sp')->nullable(); // ext_burn_sp -> NULLABLE
                $table->String('ext_chem', 10); // ext_chem -> NOT NULL
                $table->text('ext_chem_sp')->nullable(); // ext_chem_sp -> NULLABLE
                $table->String('ext_sharp', 10); // ext_sharp -> NOT NULL
                $table->text('ext_sharp_sp')->nullable(); // ext_sharp_sp -> NULLABLE
                $table->String('ext_drown_r', 10); // ext_drown_r -> NOT NULL
                $table->text('ref_drowning_code')->nullable(); // ref_drowning_code -> NULLABLE
                $table->text('ext_drown_sp')->nullable(); // ext_drown_sp -> NULLABLE
                $table->String('ext_expo_nature_r', 10); // ext_expo_nature_r -> NOT NULL
                $table->text('ref_expnature_code')->nullable(); // ref_expnature_code -> NULLABLE
                $table->text('ext_expo_nature_sp')->nullable(); // ext_expo_nature_sp -> NULLABLE
                $table->String('ext_fall', 10); // ext_fall -> NOT NULL
                $table->text('ext_falldtl')->nullable(); // ext_falldtl -> NULLABLE
                $table->String('ext_firecracker_r', 10); // ext_firecracker_r -> NOT NULL
                $table->text('firecracker_code')->nullable(); // firecracker_code -> NULLABLE
                $table->text('ext_firecracker_sp')->nullable(); // ext_firecracker_sp -> NULLABLE
                $table->String('ext_sexual', 10); // ext_sexual -> NOT NULL
                $table->String('ext_gun', 10); // ext_gun -> NOT NULL
                $table->text('ext_gun_sp')->nullable(); // ext_gun_sp -> NULLABLE
                $table->String('ext_hang', 10); // ext_hang -> NOT NULL
                $table->String('ext_maul', 10); // ext_maul -> NOT NULL
                $table->String('ext_transport', 10); // ext_transport -> NOT NULL
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
                $table->String('risk_alcliq', 10); // risk_alcliq -> NOT NULL
                $table->String('risk_none', 10); // risk_none -> NOT NULL
                $table->String('risk_sleep', 10); // risk_sleep -> NOT NULL
                $table->String('risk_smoke', 10); // risk_smoke -> NOT NULL
                $table->String('risk_mobpho', 10); // risk_mobpho -> NOT NULL
                $table->String('risk_other', 10); // risk_other -> NOT NULL
                $table->text('risk_etc_spec')->nullable(); // risk_etc_spec -> NULLABLE
                $table->String('safe_none', 10); // safe_none -> NOT NULL
                $table->String('safe_unkn', 10); // safe_unkn -> NOT NULL
                $table->String('safe_airbag', 10); // safe_airbag -> NOT NULL
                $table->String('safe_helmet', 10); // safe_helmet -> NOT NULL
                $table->String('safe_cseat', 10); // safe_cseat -> NOT NULL
                $table->String('safe_sbelt', 10); // safe_sbelt -> NOT NULL
                $table->String('safe_drown', 10); // safe_drown -> NOT NULL
                $table->String('safe_other', 10); // safe_other -> NOT NULL
                $table->text('safe_other_sp')->nullable(); // safe_other_sp -> NULLABLE
                $table->text('trans_ref'); // trans_ref -> NOT NULL
                $table->text('trans_ref2'); // trans_ref2 -> NOT NULL
                $table->text('ref_physician')->nullable(); // ref_physician -> NULLABLE
                $table->text('ref_hosp_code')->nullable(); // ref_hosp_code -> NULLABLE
                $table->text('ref_hosp_code_sp')->nullable(); // ref_hosp_code_sp -> NULLABLE
                $table->text('status_code')->nullable(); // status_code -> NULLABLE
                $table->text('mode_transport_code')->nullable(); // mode_transport_code -> NULLABLE
                $table->text('stat_reachdtl')->nullable(); // stat_reachdtl -> NULLABLE
                $table->text('diagnosis'); // diagnosis -> NOT NULL
                $table->text('icd_10_external_er')->nullable(); // icd_10_external_er -> NULLABLE
                $table->text('icd_10_nature_er')->nullable(); // icd_10_nature_er -> NULLABLE
                $table->text('disposition_code'); // disposition_code -> NOT NULL
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
