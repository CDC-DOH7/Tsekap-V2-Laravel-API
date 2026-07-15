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
                $table->string('status', 50); // status -> NOT NULL
                $table->string('pat_facility_no', 50); // pat_facility_no -> NOT NULL
                $table->date('date_report'); // date_report -> NOT NULL
                $table->time('time_report'); // time_report -> NOT NULL
                $table->string('reg_no', 50)->nullable(); // reg_no -> NULLABLE
                $table->string('tempreg_no', 50)->nullable(); // tempreg_no -> NULLABLE
                $table->string('hosp_no', 50)->nullable(); // hosp_no -> NULLABLE
                $table->string('hosp_reg_no', 50)->nullable(); // hosp_reg_no -> NULLABLE
                $table->string('hosp_cas_no', 50)->nullable(); // hosp_cas_no -> NULLABLE
                $table->string('ptype_code', 50); // ptype_code -> NOT NULL
                $table->string('pat_sex', 10); // pat_sex -> NOT NULL
                $table->date('pat_date_of_birth'); // pat_date_of_birth -> NOT NULL
                $table->integer('age_years'); // age_years -> NOT NULL
                $table->integer('age_months')->nullable(); // age_months -> NULLABLE
                $table->integer('age_days')->nullable(); // age_days -> NULLABLE
                $table->string('pat_current_address_region', 50); // pat_current_address_region -> NOT NULL
                $table->string('pat_current_address_province', 50); // pat_current_address_province -> NOT NULL
                $table->string('pat_current_address_city', 50); // pat_current_address_city -> NOT NULL
                $table->string('temp_regcode', 50)->nullable(); // temp_regcode -> NULLABLE
                $table->string('temp_provcode', 50)->nullable(); // temp_provcode -> NULLABLE
                $table->string('temp_citycode', 50)->nullable(); // temp_citycode -> NULLABLE
                $table->string('pat_phil_health_no', 50)->nullable(); // pat_phil_health_no -> NULLABLE
                $table->string('plc_provcode', 50); // plc_provcode -> NOT NULL
                $table->string('plc_regcode', 50); // plc_regcode -> NOT NULL
                $table->string('plc_ctycode', 50); // plc_ctycode -> NOT NULL
                $table->date('inj_date'); // inj_date -> NOT NULL
                $table->time('inj_time'); // inj_time -> NOT NULL
                $table->date('encounter_date'); // encounter_date -> NOT NULL
                $table->time('encounter_time'); // encounter_time -> NOT NULL
                $table->string('inj_intent_code', 50); // inj_intent_code -> NOT NULL
                $table->string('vawcyn', 10)->nullable(); // vawcyn -> NULLABLE
                $table->string('first_aid_code', 50); // first_aid_code -> NOT NULL
                $table->string('firstaid_others', 100)->nullable(); // firstaid_others -> NULLABLE
                $table->string('firstaid_others2', 100)->nullable(); // firstaid_others2 -> NULLABLE
                $table->string('mult_inj', 10); // mult_inj -> NOT NULL
                $table->string('noi_abrasion', 10); // noi_abrasion -> NOT NULL
                $table->string('noi_abradtl', 100); // noi_abradtl -> NOT NULL
                $table->string('noi_avulsion', 10)->nullable(); // noi_avulsion -> NULLABLE
                $table->string('noi_avuldtl', 100)->nullable(); // noi_avuldtl -> NULLABLE
                $table->string('noi_burn_r', 10); // noi_burn_r -> NOT NULL
                $table->string('noi_burndtl', 100)->nullable(); // noi_burndtl -> NULLABLE
                $table->string('noi_concussion', 10); // noi_concussion -> NOT NULL
                $table->string('noi_concussiondtl', 100)->nullable(); // noi_concussiondtl -> NULLABLE
                $table->string('noi_contusion', 10); // noi_contusion -> NOT NULL
                $table->string('noi_contudtl', 100)->nullable(); // noi_contudtl -> NULLABLE
                $table->string('noi_frac_clo', 10); // noi_frac_clo -> NOT NULL
                $table->string('noi_frcldtl', 100)->nullable(); // noi_frcldtl -> NULLABLE
                $table->string('noi_frac_ope', 10); // noi_frac_ope -> NOT NULL
                $table->string('noi_fropdtl', 100)->nullable(); // noi_fropdtl -> NULLABLE
                $table->string('noi_owound', 10); // noi_owound -> NOT NULL
                $table->string('noi_owoudtl', 100)->nullable(); // noi_owoudtl -> NULLABLE
                $table->string('noi_amp', 10); // noi_amp -> NOT NULL
                $table->string('noi_ampdtl', 100)->nullable(); // noi_ampdtl -> NULLABLE
                $table->string('noi_others', 10)->nullable(); // noi_others -> NULLABLE
                $table->string('noi_otherinj', 100)->nullable(); // noi_otherinj -> NULLABLE
                $table->string('ext_bite', 10); // ext_bite -> NOT NULL
                $table->string('ext_bite_sp', 100)->nullable(); // ext_bite_sp -> NULLABLE
                $table->string('ext_burn_r', 10); // ext_burn_r -> NOT NULL
                $table->string('ref_burn_code', 50); // ref_burn_code -> NOT NULL
                $table->string('ext_burn_sp', 100)->nullable(); // ext_burn_sp -> NULLABLE
                $table->string('ext_chem', 10); // ext_chem -> NOT NULL
                $table->string('ext_chem_sp', 100)->nullable(); // ext_chem_sp -> NULLABLE
                $table->string('ext_sharp', 10); // ext_sharp -> NOT NULL
                $table->string('ext_sharp_sp', 100)->nullable(); // ext_sharp_sp -> NULLABLE
                $table->string('ext_drown_r', 10); // ext_drown_r -> NOT NULL
                $table->string('ref_drowning_code', 50)->nullable(); // ref_drowning_code -> NULLABLE
                $table->string('ext_drown_sp', 100)->nullable(); // ext_drown_sp -> NULLABLE
                $table->string('ext_expo_nature_r', 10); // ext_expo_nature_r -> NOT NULL
                $table->string('ref_expnature_code', 50)->nullable(); // ref_expnature_code -> NULLABLE
                $table->string('ext_expo_nature_sp', 100)->nullable(); // ext_expo_nature_sp -> NULLABLE
                $table->string('ext_fall', 10); // ext_fall -> NOT NULL
                $table->string('ext_falldtl', 100)->nullable(); // ext_falldtl -> NULLABLE
                $table->string('ext_firecracker_r', 10); // ext_firecracker_r -> NOT NULL
                $table->string('firecracker_code', 50)->nullable(); // firecracker_code -> NULLABLE
                $table->string('ext_firecracker_sp', 100)->nullable(); // ext_firecracker_sp -> NULLABLE
                $table->string('ext_sexual', 10); // ext_sexual -> NOT NULL
                $table->string('ext_gun', 10); // ext_gun -> NOT NULL
                $table->string('ext_gun_sp', 100)->nullable(); // ext_gun_sp -> NULLABLE
                $table->string('ext_hang', 10); // ext_hang -> NOT NULL
                $table->string('ext_maul', 10); // ext_maul -> NOT NULL
                $table->string('ext_transport', 10); // ext_transport -> NOT NULL
                $table->string('vehicle_type_id', 50)->nullable(); // vehicle_type_id -> NULLABLE
                $table->string('ref_veh_acctype_code', 50)->nullable(); // ref_veh_acctype_code -> NULLABLE
                $table->string('vehicle_code', 50)->nullable(); // vehicle_code -> NULLABLE
                $table->string('pat_veh_sp', 100)->nullable(); // pat_veh_sp -> NULLABLE
                $table->string('etc_veh', 100)->nullable(); // etc_veh -> NULLABLE
                $table->string('etc_veh_sp', 100)->nullable(); // etc_veh_sp -> NULLABLE
                $table->string('position_code', 50)->nullable(); // position_code -> NULLABLE
                $table->string('pos_pat_sp', 100); // pos_pat_sp -> NOT NULL
                $table->string('ext_other', 10)->nullable(); // ext_other -> NULLABLE
                $table->string('ext_other_sp', 100)->nullable(); // ext_other_sp -> NULLABLE
                $table->string('place_occ_code', 50)->nullable(); // place_occ_code -> NULLABLE
                $table->string('poc_wp_spec', 100)->nullable(); // poc_wp_spec -> NULLABLE
                $table->string('poc_etc_spec', 100)->nullable(); // poc_etc_spec -> NULLABLE
                $table->string('activity_code', 50)->nullable(); // activity_code -> NULLABLE
                $table->string('act_etc_spec', 100)->nullable(); // act_etc_spec -> NULLABLE
                $table->string('risk_alcliq', 10); // risk_alcliq -> NOT NULL
                $table->string('risk_none', 10); // risk_none -> NOT NULL
                $table->string('risk_sleep', 10); // risk_sleep -> NOT NULL
                $table->string('risk_smoke', 10); // risk_smoke -> NOT NULL
                $table->string('risk_mobpho', 10); // risk_mobpho -> NOT NULL
                $table->string('risk_other', 10); // risk_other -> NOT NULL
                $table->string('risk_etc_spec', 100)->nullable(); // risk_etc_spec -> NULLABLE
                $table->string('safe_none', 10); // safe_none -> NOT NULL
                $table->string('safe_unkn', 10); // safe_unkn -> NOT NULL
                $table->string('safe_airbag', 10); // safe_airbag -> NOT NULL
                $table->string('safe_helmet', 10); // safe_helmet -> NOT NULL
                $table->string('safe_cseat', 10); // safe_cseat -> NOT NULL
                $table->string('safe_sbelt', 10); // safe_sbelt -> NOT NULL
                $table->string('safe_drown', 10); // safe_drown -> NOT NULL
                $table->string('safe_other', 10); // safe_other -> NOT NULL
                $table->string('safe_other_sp', 100)->nullable(); // safe_other_sp -> NULLABLE
                $table->string('trans_ref', 50); // trans_ref -> NOT NULL
                $table->string('trans_ref2', 50); // trans_ref2 -> NOT NULL
                $table->string('ref_physician', 50)->nullable(); // ref_physician -> NULLABLE
                $table->string('ref_hosp_code', 50)->nullable(); // ref_hosp_code -> NULLABLE
                $table->string('ref_hosp_code_sp', 100)->nullable(); // ref_hosp_code_sp -> NULLABLE
                $table->string('status_code', 10)->nullable(); // status_code -> NULLABLE
                $table->string('mode_transport_code', 10)->nullable(); // mode_transport_code -> NULLABLE
                $table->string('stat_reachdtl', 100)->nullable(); // stat_reachdtl -> NULLABLE
                $table->string('diagnosis', 100); // diagnosis -> NOT NULL
                $table->string('icd_10_external_er', 10)->nullable(); // icd_10_external_er -> NULLABLE
                $table->string('icd_10_nature_er', 10)->nullable(); // icd_10_nature_er -> NULLABLE
                $table->string('disposition_code', 10); // disposition_code -> NOT NULL
                $table->string('disp_er_sp', 100)->nullable(); // disp_er_sp -> NULLABLE
                $table->string('disp_er_sp_oth', 100)->nullable(); // disp_er_sp_oth -> NULLABLE
                $table->string('outcome_code', 10)->nullable(); // outcome_code -> NULLABLE
                $table->string('complete_diagnosis', 100)->nullable(); // complete_diagnosis -> NULLABLE
                $table->string('disp_inpat', 10)->nullable(); // disp_inpat -> NULLABLE
                $table->string('disp_inpat_oth', 100)->nullable(); // disp_inpat_oth -> NULLABLE
                $table->string('disp_inpat_sp', 100)->nullable(); // disp_inpat_sp -> NULLABLE
                $table->string('disp_inpat_sp2', 100)->nullable(); // disp_inpat_sp2 -> NULLABLE
                $table->string('icd10_nature_inpatient', 10)->nullable(); // icd10_nature_inpatient -> NULLABLE
                $table->string('outcome_inpat', 10)->nullable(); // outcome_inpat -> NULLABLE
                $table->string('icd_10_ext_inpatient', 10)->nullable(); // icd_10_ext_inpatient -> NULLABLE
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
