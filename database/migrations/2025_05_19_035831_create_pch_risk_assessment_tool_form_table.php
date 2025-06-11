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
            Schema::create('pch_risk_assessment_tool_form', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('pch_profile_id')->index();
                $table->string('nature_of_visit');
                $table->string('nature_of_visit_duration', 50);
                $table->string('type_of_consultation');

                $table->unsignedInteger('vit_bp_systolic');
                $table->unsignedInteger('vit_bp_diastolic');
                $table->unsignedInteger('vit_oxygen_saturation');
                $table->unsignedInteger('vit_heart_rate_or_pulse_rate');
                $table->string('vit_is_normal_rate', 50);
                $table->string('vit_is_regular_rhythm', 50);
                $table->unsignedInteger('vit_respiratory_rate');
                $table->double('vit_temperature');
                $table->double('vit_weight');
                $table->double('vit_height');
                $table->double('vit_bmi');
                $table->string('vit_chief_complaint');
                $table->text('vit_history_of_present_illness_and_remarks')->nullable();

                $table->string('pe_skin_extremities_description', 50)->nullable();
                $table->string('pe_heent_description', 50)->nullable();
                $table->string('pe_chest_description', 50)->nullable();
                $table->string('pe_heart', 50)->nullable();
                $table->string('pe_abdomen', 50)->nullable();
                $table->string('pe_alert_type', 50)->nullable();
                $table->string('pe_description', 50)->nullable();

                $table->string('ab_anatomical_location', 50)->nullable();
                $table->string('ab_anatomical_location_others_specify', 255)->nullable();
                $table->string('ab_animal_type', 50)->nullable();
                $table->string('ab_animal_type_others_specify', 255)->nullable();
                $table->text('ab_description_of_event')->nullable();
                $table->string('ab_type_of_exposure', 50)->nullable();
                $table->string('ab_wash_bite', 50)->nullable();
                $table->date('ab_date_of_exposure')->nullable();

                $table->string('comorbidities', 50)->nullable();
                $table->string('comorbidities_others', 255)->nullable();

                $table->string('ph_immunization_record_child', 255)->nullable();
                $table->string('ph_immunization_record_child_others_specify', 255)->nullable();
                $table->string('ph_immunization_record_pregnant', 255)->nullable();
                $table->string('ph_immunization_record_pregnant_others_specify', 255)->nullable();
                $table->string('ph_immunization_record_adult_and_elderly', 255)->nullable();
                $table->string('ph_immunization_record_adult_and_elderly_others_specify', 255)->nullable();

                $table->string('wr_menarche', 15)->nullable();
                $table->unsignedInteger('wr_menarche_age')->nullable();
                $table->string('wr_menopause', 15)->nullable();
                $table->unsignedInteger('wr_menopause_age')->nullable();
                $table->unsignedInteger('wr_no_of_pads_used_per_day')->nullable();
                $table->unsignedInteger('wr_interval_cycle_of_menstruation_in_days')->nullable();
                $table->text('wr_birth_control_method_used')->nullable();
                $table->unsignedInteger('wr_onset_of_sexual_intercourse_age')->nullable();

                $table->unsignedInteger('wr_pregnancy_history_gravidity')->nullable();
                $table->unsignedInteger('wr_pregnancy_history_parity')->nullable();
                $table->string('wr_pre_eclampsia', 15)->nullable();
                $table->string('wr_with_access_to_family_planning_counseling', 15)->nullable();
                $table->unsignedInteger('wr_pregnancy_history_num_of_full_term_pregnancy')->nullable();
                $table->unsignedInteger('wr_pregnancy_history_num_of_premature_pregnancy')->nullable();
                $table->unsignedInteger('wr_number_of_abortion')->nullable();
                $table->unsignedInteger('wr_number_of_living_children')->nullable();

                $table->string('fmh_first_degree_relatives_with', 50)->nullable();
                $table->string('fmh_first_degree_relatives_with_specify_others', 255)->nullable();

                $table->string('sh_smoking', 50)->nullable();
                $table->string('sh_use_of_vape', 15)->nullable();
                $table->unsignedInteger('sh_use_of_vape_age_started')->nullable();

                $table->string('soch_illicit_drug_use', 15)->nullable();
                $table->string('soch_illicit_drug_use_specify_illicit_drug_used', 255)->nullable();
                $table->string('soch_sexual_activity_is_sexually_active', 15)->nullable();
                $table->string('soch_sexual_activity_number_of_partners', 15)->nullable();
                $table->string('soch_sexual_activity_with_protection', 15)->nullable();
                $table->string('soch_sexual_activity_testing_done', 15)->nullable();

                $table->string('excessive_alcohol_intake', 15)->nullable();
                $table->string('dietary_fiber_intake_3_servings_of_vegetable_daily', 15)->nullable();
                $table->string('dietary_fiber_intake_2_to_3_servings_of_fruits_daily', 15)->nullable();
                $table->string('high_fat_or_high_salt_food_intake', 15)->nullable();
                $table->string('physical_activity', 15)->nullable();

                $table->string('pahas_or_tia_q1', 15)->nullable();
                $table->string('pahas_or_tia_q2', 15)->nullable();
                $table->string('pahas_or_tia_q3', 15)->nullable();
                $table->string('pahas_or_tia_q4', 15)->nullable();
                $table->string('pahas_or_tia_q5', 15)->nullable();
                $table->string('pahas_or_tia_q6', 15)->nullable();
                $table->string('pahas_or_tia_q7', 15)->nullable();
                $table->string('pahas_or_tia_q8', 15)->nullable();

                $table->string('diagnosed_as_having_diabetes', 15)->nullable();
                $table->string('symptoms_polyphagia', 15)->nullable();
                $table->string('symptoms_polydipsia', 15)->nullable();
                $table->string('symptoms_polyuria', 15)->nullable();
                $table->double('blood_glucose')->nullable();
                $table->string('has_raised_blood_glucose', 15)->nullable();
                $table->string('presence_of_urine_ketones_newly_diagnosed', 15)->nullable();
                $table->double('urine_ketones')->nullable();
                $table->date('urine_ketones_date_taken')->nullable();
                $table->string('fbs_rbs', 255)->nullable();
                $table->date('fbs_rbs_date_taken')->nullable();
                $table->double('blood_lipid')->nullable();
                $table->string('has_raised_blood_lipid', 15)->nullable();
                $table->double('total_cholesterol')->nullable();
                $table->date('total_cholesterol_date_taken')->nullable();
                $table->string('management', 15)->nullable();
                $table->string('lifestyle_modification', 15)->nullable();
                $table->text('medications')->nullable();
                $table->string('presence_of_urine_protein', 15)->nullable();
                $table->double('urine_protein')->nullable();
                $table->date('urine_protein_date_taken')->nullable();
                $table->date('date_follow_up')->nullable();

                $table->string('do_laboratory_request', 255)->nullable();
                $table->string('do_imaging', 255)->nullable();
                $table->text('do_diagnosis')->nullable();
                $table->text('do_treatment_plan')->nullable();
                $table->date('do_follow_up_date')->nullable();
                $table->text('do_prescription')->nullable();
                $table->text('do_remarks')->nullable();

                // system metadata
                $table->timestamps();

                // foreign key constraints
                $table->foreign('pch_profile_id')->references('id')->on('pch_risk_assessment_tool_profile')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            Log::error('Migration failed (create pch_risk_assessment_tool_form): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::dropIfExists('pch_risk_assessment_tool_form');
        } catch (\Exception $e) {
            Log::error('Migration failed (drop pch_risk_assessment_tool_form): ' . $e->getMessage());
            throw $e;
        }
    }
};
