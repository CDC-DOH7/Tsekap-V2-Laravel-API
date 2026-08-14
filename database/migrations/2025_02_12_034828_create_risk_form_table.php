<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('risk_form', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('risk_profile_id')->nullable()->index();
            $table->foreign('risk_profile_id')->references('id')->on('risk_profile')->onDelete('set null');

            // Assess Red Flags
            $table->string('ar_chest_pain', 8)->nullable();
            $table->string('ar_difficulty_breathing', 8)->nullable();
            $table->string('ar_loss_of_consciousness', 8)->nullable();
            $table->string('ar_slurred_speech', 8)->nullable();
            $table->string('ar_facial_asymmetry', 8)->nullable();
            $table->string('ar_weakness_numbness', 8)->nullable();
            $table->string('ar_disoriented', 8)->nullable();
            $table->string('ar_chest_retractions', 8)->nullable();
            $table->string('ar_seizure_convulsion', 8)->nullable();
            $table->string('ar_act_self_harm_suicide', 8)->nullable();
            $table->string('ar_agitated_behavior', 8)->nullable();
            $table->string('ar_eye_injury', 8)->nullable();
            $table->string('ar_severe_injuries', 8)->nullable();
            // $table->string('ar_refer_physician_name')->nullable();
            // $table->string('ar_refer_reason')->nullable();
            // $table->string('ar_refer_facility')->nullable();

            // Past Medical History
            $table->string('pmh_hypertension', 8)->nullable();
            $table->string('pmh_heart_disease', 8)->nullable();
            $table->string('pmh_diabetes', 8)->nullable();
            $table->string('pmh_specify_diabetes')->nullable();
            $table->string('pmh_cancer', 8)->nullable();
            $table->string('pmh_specify_cancer')->nullable();
            $table->string('pmh_copd', 8)->nullable();
            $table->string('pmh_asthma', 8)->nullable();
            $table->string('pmh_allergies', 8)->nullable();
            $table->string('pmh_specify_allergies')->nullable();
            $table->string('pmh_mn_and_s_disorder', 8)->nullable();
            $table->string('pmh_specify_mn_and_s_disorder')->nullable();
            $table->string('pmh_vision_problems', 8)->nullable();
            $table->string('pmh_previous_surgical', 8)->nullable();
            $table->string('pmh_specify_previous_surgical')->nullable();
            $table->string('pmh_thyroid_disorders', 8)->nullable();
            $table->string('pmh_kidney_disorders', 8)->nullable();

            // Family Medical History
            $table->string('fmh_hypertension', 20)->nullable();
            $table->string('fmh_stroke', 20)->nullable();
            $table->string('fmh_heart_disease', 20)->nullable();
            $table->string('fmh_diabetes_mellitus', 20)->nullable();
            $table->string('fmh_asthma', 20)->nullable();
            $table->string('fmh_cancer', 20)->nullable();
            $table->string('fmh_kidney_disease', 20)->nullable();
            $table->string('fmh_first_degree_relative', 20)->nullable();
            $table->string('fmh_having_tuberculosis_5_years', 20)->nullable();
            $table->string('fmh_mn_and_s_disorder', 20)->nullable();
            $table->string('fmh_copd', 20)->nullable();

            // Risk Factors
            $table->string('rf_tobacco_use')->nullable();
            $table->string('rf_alcohol_intake', 8)->nullable();
            $table->string('rf_alcohol_binge_drinker', 8)->nullable();
            $table->string('rf_physical_activity', 8)->nullable();
            $table->string('rf_nutrition_dietary', 8)->nullable();
            $table->double('rf_weight')->nullable();
            $table->double('rf_height')->nullable();
            $table->double('rf_body_mass')->nullable();
            $table->double('rf_waist_circumference')->nullable();

            // Readings
            $table->double('rs_systolic_t1')->nullable();
            $table->double('rs_diastolic_t1')->nullable();
            $table->double('rs_systolic_t2')->nullable();
            $table->double('rs_diastolic_t2')->nullable();
            $table->double('rs_blood_sugar_fbs')->nullable();
            $table->double('rs_blood_sugar_rbs')->nullable();
            $table->date('rs_blood_sugar_date_taken')->nullable();
            $table->string('rs_blood_sugar_symptoms')->nullable();
            $table->double('rs_lipid_cholesterol')->nullable();
            $table->double('rs_lipid_hdl')->nullable();
            $table->double('rs_lipid_ldl')->nullable();
            $table->double('rs_lipid_vldl')->nullable();
            $table->double('rs_lipid_triglyceride')->nullable();
            $table->date('rs_lipid_date_taken')->nullable();
            $table->double('rs_urine_protein')->nullable();
            $table->date('rs_urine_protein_date_taken')->nullable();
            $table->double('rs_urine_ketones')->nullable();
            $table->date('rs_urine_ketones_date_taken')->nullable();
            $table->string('rs_chronic_respiratory_disease')->nullable();
            $table->string('rs_if_yes_any_symptoms')->nullable();

            // Management
            $table->string('mngm_med_hypertension', 8)->nullable();
            $table->string('mngm_med_hypertension_specify')->nullable();
            $table->string('mngm_med_diabetes', 8)->nullable();
            $table->string('mngm_med_diabetes_options', 50)->nullable();
            $table->string('mngm_med_diabetes_specify')->nullable();
            $table->date('mngm_date_follow_up')->nullable();
            $table->text('mngm_remarks')->nullable();

            // Metadata
            $table->boolean('offline_entry')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_form');
    }
};
