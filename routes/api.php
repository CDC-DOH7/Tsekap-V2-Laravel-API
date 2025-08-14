<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TsekapV2\Analytics\DataRetrieval\AdministrativeAnalyticsDataController;
use App\Http\Controllers\TsekapV2\SessionController;
use App\Http\Controllers\TsekapV2\UserController;
use App\Http\Controllers\TsekapV2\Misc\MiscDataController;
use App\Http\Controllers\TsekapV2\FacilityController;
use App\Http\Controllers\TsekapV2\ProfileController;
use App\Http\Controllers\TsekapV2\UserHealthFacilityController;

// Websockets Controllers
use App\Http\Controllers\TsekapV2\Websockets\NotificationController;

// Data Controllers
use App\Http\Controllers\TsekapV2\Forms\GeneralDataController;
use App\Http\Controllers\TsekapV2\Forms\RiskAssessmentForm\DataController as PhilpenRiskDataController;
use App\Http\Controllers\TsekapV2\Forms\PchRiskAssessmentForm\DataController as PchRiskDataController;
use App\Http\Controllers\TsekapV2\Analytics\DataRetrieval\Analytics\PhilpenAnalyticsDataController;
use App\Http\Controllers\TsekapV2\Analytics\DataRetrieval\Analytics\GeneralAnalyticsDataController;
use App\Http\Controllers\TsekapV2\Analytics\DataRetrieval\Analytics\PchratAnalyticsDataController;


// Total Population and Target Setting Controllers
use App\Http\Controllers\TsekapV2\Analytics\ProfilingTargetSetting\Muncity\ProfilingTotalPerMuncityController;
use App\Http\Controllers\TsekapV2\Analytics\ProfilingTargetSetting\Muncity\ProfilingTargetPerMuncityController;
use App\Http\Controllers\TsekapV2\Analytics\ProfilingTargetSetting\Barangay\ProfilingTotalPerBarangayController;
use App\Http\Controllers\TsekapV2\Analytics\ProfilingTargetSetting\Barangay\ProfilingTargetPerBarangayController;

Route::prefix('v2')->group(function () {
    // Non-authenticated Routes
    Route::post('/login', [AuthController::class, 'login'])->name('api-v2-login');
    Route::post('/register', [AuthController::class, 'selfRegisterUser'])->name('api-v2-register');

    // Misc Routes
    Route::prefix('misc')->group(function () {
        // For Addresses and Facilities
        Route::get('/get-all-facility', [MiscDataController::class, 'getAllFacility'])->name('api-v2-get-all-facility');
        Route::get('/get-countries', [MiscDataController::class, 'getCountries'])->name('api-v2-get-countries');
        Route::get('/get-regions', [MiscDataController::class, 'getRegions'])->name('api-v2-get-regions');
        Route::get('/get-provinces', [MiscDataController::class, 'getProvinces'])->name('api-v2-get-all-provinces');
        Route::get('/get-muncities', [MiscDataController::class, 'getMuncities'])->name('api-v2-get-muncity');
        Route::get('/get-barangays', [MiscDataController::class, 'getBarangays'])->name('api-v2-get-barangay');
        Route::get('/get-all-muncities', [MiscDataController::class, 'getAllMuncities'])->name('api-v2-get-all-muncities');
        Route::get('/get-all-barangays', [MiscDataController::class, 'getAllBarangays'])->name('api-v2-get-all-barangays');

        // Citizenships and Religions
        Route::get('/get-all-citizenships', [MiscDataController::class, 'getAllCitizenships'])->name('api-v2-get-all-citizenships');
        Route::get('/get-all-religions', [MiscDataController::class, 'getAllReligions'])->name('api-v2-get-all-religions');

        // Get By Id
        Route::get('/get-province-by-id', [MiscDataController::class, 'getProvinceById'])->name('api-v2-get-province-by-id');
        Route::get('/get-muncity-by-id', [MiscDataController::class, 'getMuncityById'])->name('api-v2-get-muncity-by-id');
        Route::get('/get-barangay-by-id', [MiscDataController::class, 'getBarangayById'])->name('api-v2-get-barangay-by-id');
    });

    Route::prefix('facility')->group(function () {
        Route::post('/add-user-facility', [UserHealthFacilityController::class, 'addUserHealthFacility'])->name('api-v2-add-user-facility');
    });

    // Protected Routes (Requires Authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Admin Routes
        Route::prefix('admin')->group(function () {
            Route::post('/reset-user-password', [AdminController::class, 'resetUserPassword'])->name('api-v2-reset-user-password');
            Route::post('/register-user', [AdminController::class, 'registerUser'])->name('api-v2-admin-register-user');

            // additional routes for verifying users
            Route::post('/verify-user', [AdminController::class, 'verifyUser'])->name('api-v2-admin-verify-user');
            Route::post('/unverify-user', [AdminController::class, 'unverifyUser'])->name('api-v2-admin-unverify-user');
            Route::get('/list-users', [AdminController::class, 'listAllUsers'])->name('api-v2-admin-list-all-users');
            Route::get('/list-users-by-facility', [AdminController::class, 'listAllUsersByFacility'])->name('api-v2-admin-list-all-users-by-facility');
            Route::get('/list-unverified-users', [AdminController::class, 'listUnverifiedUsers'])->name('api-v2-admin-list-unverified-users');
        });

        // Analytics Routes
        Route::prefix('analytics')->group(function () {

            // Administrative Analytics
            Route::prefix('admin-analytics')->group(function () {
                Route::get('/get-number-of-entries-by-user-per-facility', [AdministrativeAnalyticsDataController::class, 'countNumberOfEntriesPerByUserPerFacility']);
            });

            // ---- Analytics Endpoints for Profiling Target Setting ---- 
            // Targets
            Route::prefix('target')->group(function () {
                // muncity
                Route::prefix('muncity')->group(function () {
                    Route::get('/check-if-target-muncity-mapping-exists', [ProfilingTargetPerMuncityController::class, 'checkTargetMappingPerMuncityExists'])->name('api-v2-check-if-target-per-muncity-mapping-exists');
                    Route::post('/create-target-per-muncity', [ProfilingTargetPerMuncityController::class, 'createTargetPerMuncity'])->name('api-v2-create-target-per-muncity');
                    Route::get('/retrieve-target-per-muncity', [ProfilingTargetPerMuncityController::class, 'retrieveTargetPerMuncity'])->name('api-v2-retrieve-target-per-muncity');
                    Route::post('/update-target-per-muncity', [ProfilingTargetPerMuncityController::class, 'updateTargetPerMuncity'])->name('api-v2-update-target-per-muncity');
                    Route::post('/delete-target-per-muncity', [ProfilingTargetPerMuncityController::class, 'deleteTargetPerMuncity'])->name('api-v2-delete-target-per-muncity');
                });

                // barangayx
                Route::prefix('barangay')->group(function () {
                    Route::get('/check-if-target-barangay-mapping-exists', [ProfilingTargetPerBarangayController::class, 'checkTargetMappingPerBarangayExists'])->name('api-v2-check-if-target-per-barangay-mapping-exists');
                    Route::post('/create-target-per-barangay', [ProfilingTargetPerBarangayController::class, 'createTargetPerBarangay'])->name('api-v2-create-target-per-barangay');
                    Route::get('/retrieve-target-per-barangay', [ProfilingTargetPerBarangayController::class, 'retrieveTargetPerBarangay'])->name('api-v2-retrieve-target-per-barangay');
                    Route::post('/update-target-per-barangay', [ProfilingTargetPerBarangayController::class, 'updateTargetPerBarangay'])->name('api-v2-update-target-per-barangay');
                    Route::post('/delete-target-per-barangay', [ProfilingTargetPerBarangayController::class, 'deleteTargetPerBarangay'])->name('api-v2-delete-target-per-barangay');
                });
            });

            // Population
            Route::prefix('population')->group(function () {
                // muncity
                Route::prefix('muncity')->group(function () {
                    Route::get('/check-if-totals-per-muncity-mapping-exists', [ProfilingTotalPerMuncityController::class, 'checkPopulationMappingPerMuncityExists'])->name('api-v2-check-if-totals-per-muncity-mapping-exists');
                    Route::post('/create-totals-per-muncity', [ProfilingTotalPerMuncityController::class, 'createTotalsPerMuncity'])->name('api-v2-create-population-totals-per-muncity');
                    Route::get('/retrieve-totals-per-muncity', [ProfilingTotalPerMuncityController::class, 'retrieveTotalsPerMuncity'])->name('api-v2-retrieve-population-totals-per-muncity');
                    Route::post('/update-totals-per-muncity', [ProfilingTotalPerMuncityController::class, 'updateTotalsPerMuncity'])->name('api-v2-update-population-totals-per-muncity');
                    Route::post('/delete-totals-per-muncity', [ProfilingTotalPerMuncityController::class, 'deleteTotalsPerMuncity'])->name('api-v2-delete-population-totals-per-muncity');
                });

                // barangay
                Route::prefix('barangay')->group(function () {
                    Route::get('/check-if-totals-per-barangay-mapping-exists', [ProfilingTotalPerBarangayController::class, 'checkPopulationMappingPerBarangayExists'])->name('api-v2-check-if-totals-per-barangay-mapping-exists');
                    Route::post('/create-totals-per-barangay', [ProfilingTotalPerBarangayController::class, 'createTotalsPerBarangay'])->name('api-v2-create-population-totals-per-barangay');
                    Route::get('/retrieve-totals-per-barangay', [ProfilingTotalPerBarangayController::class, 'retrieveTotalsPerBarangay'])->name('api-v2-retrieve-population-totals-per-barangay');
                    Route::post('/update-totals-per-barangay', [ProfilingTotalPerBarangayController::class, 'updateTotalsPerBarangay'])->name('api-v2-update-population-totals-per-barangay');
                    Route::post('/delete-totals-per-barangay', [ProfilingTotalPerBarangayController::class, 'deleteTotalsPerBarangay'])->name('api-v2-delete-population-totals-per-barangay');
                });
            });

            // Data
            Route::prefix('data')->group(function () {
                Route::prefix('general_analytics')->group(function () {
                    // endpoint: /age_brackets
                    Route::get('/get-age-brackets', [GeneralAnalyticsDataController::class, 'getAgeBrackets'])->name('api-v2-analytics-get-age-brackets');

                    // endpoint : /patient_summary
                    Route::get('/get-patient-summary', [GeneralAnalyticsDataController::class, 'getPatientSummary'])->name('api-v2-analytics-get-patient-summary');

                    // endpoint : /age_group_summary
                    Route::get('/get-age-group-summary', [GeneralAnalyticsDataController::class, 'getAgeGroupSummary'])->name('api-v2-analytics-get-age-group-summary');

                    // endpoint : /age_range_summary
                    Route::get('/get-age-range-summary', [GeneralAnalyticsDataController::class, 'getAgeRangeSummary'])->name('api-v2-analytics-get-age-range-summary');

                    // endpoint : /month_year_summary
                    Route::get('/get-month-year-summary-patient-data', [GeneralAnalyticsDataController::class, 'getMonthYearSummaryPatientData'])
                        ->name('api-v2-analytics-get-month-year-summary-patient-data');

                    // endpoint: /morbidity_summary
                    Route::get('/get-morbidity-summary-patient-data', [GeneralAnalyticsDataController::class, 'getMorbiditySummaryPatientData'])
                        ->name('api-v2-analytics-get-morbidity-summary-patient-data');

                    // endpoint: /monthly_summary
                    Route::get('/get-monthly-summary-patient-data', [GeneralAnalyticsDataController::class, 'getMonthlySummaryPatientData'])
                        ->name('api-v2-analytics-get-monthly-summary-patient-data');
                    Route::get('/get-monthly-summary-risk-profile-by-facility', [GeneralAnalyticsDataController::class, 'getMonthlySummaryRiskProfileByFacility'])
                        ->name('api-v2-analytics-get-monthly-summary-risk-profile-by-facility');
                });

                // pchrat analytics
                Route::prefix('pchrat')->group(function () {
                    // endpoint: /family_history_summary
                    Route::get('/get-pchrat-family-history-summary-data', [PchratAnalyticsDataController::class, 'getFamilyHistorySummaryData'])
                        ->name('api-v2-analytics-pchrat-get-family-history-summary-data');
                    Route::get('/get-pchrat-family-history-summary-patient-info', [PchratAnalyticsDataController::class, 'getFamilyHistorySummaryPatientInfo'])
                        ->name('api-v2-analytics-pchrat-get-family-history-summary-patient-info');
                    Route::get('/get-pchrat-family-history-summary-risk-profile', [PchratAnalyticsDataController::class, 'getFamilyHistorySummaryRiskProfile'])
                        ->name('api-v2-analytics-pchrat-get-family-history-summary-risk-profile');

                    // endpoint: /smoking_history_summary
                    Route::get('/get-pchrat-smoking-history-summary-data', [PchratAnalyticsDataController::class, 'getSmokingHistorySummaryData'])
                        ->name('api-v2-analytics-pchrat-get-smoking-history-summary-data');
                    Route::get('/get-pchrat-smoking-history-summary-patient-info', [PchratAnalyticsDataController::class, 'getSmokingHistorySummaryPatientInfo'])
                        ->name('api-v2-analytics-pchrat-get-smoking-history-summary-patient-info');
                    Route::get('/get-pchrat-smoking-history-summary-risk-profile', [PchratAnalyticsDataController::class, 'getSmokingHistorySummaryRiskProfile'])
                        ->name('api-v2-analytics-pchrat-get-smoking-history-summary-risk-profile');

                    // endpoint: /social_history_summary
                    Route::get('/get-pchrat-social-history-summary-data', [PchratAnalyticsDataController::class, 'getSocialHistorySummaryData'])
                        ->name('api-v2-analytics-pchrat-get-social-history-summary-data');
                    Route::get('/get-pchrat-social-history-summary-patient-info', [PchratAnalyticsDataController::class, 'getSocialHistorySummaryPatientInfo'])
                        ->name('api-v2-analytics-pchrat-get-social-history-summary-patient-info');
                    Route::get('/get-pchrat-social-history-summary-risk-profile', [PchratAnalyticsDataController::class, 'getSocialHistorySummaryRiskProfile'])
                        ->name('api-v2-analytics-pchrat-get-social-history-summary-risk-profile');

                    // endpoint: /lifestyle_summary
                    Route::get('/get-pchrat-lifestyle-summary-data', [PchratAnalyticsDataController::class, 'getLifestyleSummaryData'])
                        ->name('api-v2-analytics-pchrat-get-lifestyle-summary-data');
                    Route::get('/get-pchrat-lifestyle-summary-patient-info', [PchratAnalyticsDataController::class, 'getLifestyleSummaryPatientInfo'])
                        ->name('api-v2-analytics-pchrat-get-lifestyle-summary-patient-info');
                    Route::get('/get-pchrat-lifestyle-summary-risk-profile', [PchratAnalyticsDataController::class, 'getLifestyleSummaryRiskProfile'])
                        ->name('api-v2-analytics-pchrat-get-lifestyle-summary-risk-profile');

                    // endpoint: /questionnaire_summary
                    Route::get('/get-pchrat-questionnaire-summary-data', [PchratAnalyticsDataController::class, 'getQuestionnaireSummaryData'])
                        ->name('api-v2-analytics-pchrat-get-questionnaire-summary-data');
                    Route::get('/get-pchrat-questionnaire-summary-patient-info', [PchratAnalyticsDataController::class, 'getQuestionnaireSummaryPatientInfo'])
                        ->name('api-v2-analytics-pchrat-get-questionnaire-summary-patient-info');
                    Route::get('/get-pchrat-questionnaire-summary-risk-profile', [PchratAnalyticsDataController::class, 'getQuestionnaireSummaryRiskProfile'])
                        ->name('api-v2-analytics-pchrat-get-questionnaire-summary-risk-profile');
                });

                // philpen analytics
                Route::prefix('philpen')->group(function () {
                    // endpoint: /clinical_complaints_summary
                    Route::get('/get-clinical-complaints-ar-records', [PhilpenAnalyticsDataController::class, 'getClinicalComplaintsArRecords'])
                        ->name('api-v2-get-clinical-complaints-ar-records');
                    Route::get('/get-clinical-complaints-risk-profile', [PhilpenAnalyticsDataController::class, 'getClinicalComplaintsRiskProfile'])
                        ->name('api-v2-analytics-get-clinical-complaints-risk-profile');

                    // endpoint: /alcohol_intake_summary
                    Route::get('/get-alcohol-intake-summary-data', [PhilpenAnalyticsDataController::class, 'getAlcoholIntakeSummaryData'])
                        ->name('api-v2-analytics-get-alcohol-intake-summary-data');
                    Route::get('/get-alcohol-intake-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getAlcoholIntakeSummaryPatientInfo'])
                        ->name('api-v2-analytics-get-alcohol-intake-summary-patient-info');
                    Route::get('/get-alcohol-intake-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getAlcoholIntakeSummaryRiskProfile'])
                        ->name('api-v2-analytics-get-alcohol-intake-summary-risk-profile');

                    // endpoint: /alcohol_binge_drinker_summary
                    Route::get('/get-alcohol-binge-drinker-summary-data', [PhilpenAnalyticsDataController::class, 'getAlcoholBingeDrinkerSummaryData'])
                        ->name('api-v2-analytics-get-alcohol-binge-drinker-summary-data');
                    Route::get('/get-alcohol-binge-drinker-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getAlcoholBingeDrinkerSummaryPatientInfo'])
                        ->name('api-v2-analytics-get-alcohol-binge-drinker-summary-patient-info');
                    Route::get('/get-alcohol-binge-drinker-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getAlcoholBingeDrinkerSummaryRiskProfile'])
                        ->name('api-v2-analytics-get-alcohol-binge-drinker-summary-risk-profile');

                    // endpoint: /physical_activity_summary
                    Route::get('/get-physical-activity-summary-data', [PhilpenAnalyticsDataController::class, 'getPhysicalActivitySummaryData'])
                        ->name('api-v2-analytics-get-physical-activity-summary-data');
                    Route::get('/get-physical-activity-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getPhysicalActivitySummaryPatientInfo'])
                        ->name('api-v2-analytics-get-physical-activity-summary-patient-info');
                    Route::get('/get-physical-activity-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getPhysicalActivitySummaryRiskProfile'])
                        ->name('api-v2-analytics-get-physical-activity-summary-risk-profile');

                    // endpoint: /nutrition_summary
                    Route::get('/get-nutrition-summary-data', [PhilpenAnalyticsDataController::class, 'getNutritionSummaryData'])
                        ->name('api-v2-analytics-get-nutrition-summary-data');
                    Route::get('/get-nutrition-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getNutritionSummaryPatientInfo'])
                        ->name('api-v2-analytics-get-nutrition-summary-patient-info');
                    Route::get('/get-nutrition-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getNutritionSummaryRiskProfile'])
                        ->name('api-v2-analytics-get-nutrition-summary-risk-profile');

                    // endpoint: /prev_med_history_summary
                    Route::get('/get-prev-med-history-summary-data', [PhilpenAnalyticsDataController::class, 'getPrevMedHistorySummaryData'])
                        ->name('api-v2-analytics-get-prev-med-history-summary-data');
                    Route::get('/get-prev-med-history-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getPrevMedHistorySummaryRiskProfile'])
                        ->name('api-v2-analytics-get-prev-med-history-summary-patient-info');
                    Route::get('/get-prev-med-history-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getPrevMedHistorySummaryPatientInfo'])
                        ->name('api-v2-analytics-get-prev-med-history-summary-risk-profile');

                    // endpoint: /family_history_summary
                    Route::get('/get-family-history-summary-data', [PhilpenAnalyticsDataController::class, 'getFamilyHistorySummaryData'])
                        ->name('api-v2-analytics-get-family-history-summary-data');
                    Route::get('/get-family-history-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getFamilyHistorySummaryRiskProfile'])
                        ->name('api-v2-analytics-get-family-history-summary-patient-info');
                    Route::get('/get-family-history-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getFamilyHistorySummaryPatientInfo'])
                        ->name('api-v2-analytics-get-family-history-summary-risk-profile');

                    // endpoint: /tobacco_summary
                    Route::get('/get-tobacco-summary-data', [PhilpenAnalyticsDataController::class, 'getTobaccoSummaryData'])
                        ->name('api-v2-analytics-get-tobacco-summary-data');
                    Route::get('/get-tobacco-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getTobaccoSummaryPatientInfo'])
                        ->name('api-v2-analytics-tobacco-summary-patient-info');
                    Route::get('/get-tobacco-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getTobaccoSummaryRiskProfile'])
                        ->name('api-v2-analytics-get-tobacco-summary-risk-profile');

                    // endpoint: /social_history_summary
                    Route::get('/get-social-history-summary-data', [PhilpenAnalyticsDataController::class, 'getSocialHistorySummaryData'])
                        ->name('api-v2-analytics-get-social-history-summary-data');
                    Route::get('/get-social-history-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getSocialHistorySummaryPatientInfo'])
                        ->name('api-v2-analytics-get-social-history-summary-patient-info');
                    Route::get('/get-social-history-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getSocialHistorySummaryRiskProfile'])
                        ->name('api-v2-analytics-get-social-history-summary-risk-profile');

                    // endpoint: /lifestyle_info_summary
                    Route::get('/get-lifestyle-info-summary-data', [PhilpenAnalyticsDataController::class, 'getLifestyleInfoSummaryData'])
                        ->name('api-v2-analytics-get-lifestyle-info-summary-data');
                    Route::get('/get-lifestyle-info-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getLifestyleInfoSummaryPatientInfo'])
                        ->name('api-v2-analytics-get-lifestyle-info-summary-patient-info');
                    Route::get('/get-lifestyle-info-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getLifestyleInfoSummaryRiskProfile'])
                        ->name('api-v2-analytics-get-lifestyle-info-summary-risk-profile');

                    // endpoint: /questionnaire_summary
                    Route::get('/get-questionnaire-summary-data', [PhilpenAnalyticsDataController::class, 'getQuestionnaireSummaryData'])
                        ->name('api-v2-analytics-get-questionnaire-summary-data');
                    Route::get('/get-questionnaire-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getQuestionnaireSummaryPatientInfo'])
                        ->name('api-v2-analytics-get-questionnaire-summary-patient-info');
                    Route::get('/get-questionnaire-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getQuestionnaireSummaryRiskProfile'])
                        ->name('api-v2-analytics-get-questionnaire-summary-risk-profile');

                    // endpoint: /bp1_summary
                    Route::get('/get-bp1-summary-data', [PhilpenAnalyticsDataController::class, 'getBp1SummaryData'])
                        ->name('api-v2-analytics-get-bp1-summary-data');
                    Route::get('/get-bp1-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getBp1SummaryRiskProfile'])
                        ->name('api-v2-analytics-get-bp1-summary-patient-info');
                    Route::get('/get-bp1-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getBp1SummaryPatientInfo'])
                        ->name('api-v2-analytics-get-bp1-summary-risk-profile');

                    // endpoint: /hypertension_summary
                    Route::get('/get-hypertension-summary-data', [PhilpenAnalyticsDataController::class, 'getBp2SummaryData'])
                        ->name('api-v2-analytics-get-hypertension-summary-data');
                    Route::get('/get-hypertension-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getBp2SummaryRiskProfile'])
                        ->name('api-v2-analytics-get-hypertension-summary-patient-info');
                    Route::get('/get-hypertension-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getBp2SummaryPatientInfo'])
                        ->name('api-v2-analytics-get-hypertension-summary-risk-profile');

                    // endpoint: /diabetes_summary
                    Route::get('/get-diabetes-summary-data', [PhilpenAnalyticsDataController::class, 'getDiabetesSummaryData'])
                        ->name('api-v2-analytics-get-diabetes-summary-data');
                    Route::get('/get-diabetes-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getDiabetesSummaryRiskProfile'])
                        ->name('api-v2-analytics-get-diabetes-summary-patient-info');
                    Route::get('/get-diabetes-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getDiabetesSummaryPatientInfo'])
                        ->name('api-v2-analytics-get-diabetes-summary-risk-profile');

                    // endpoint: /hypercholesterolemia_summary
                    Route::get('/get-hypercholesterolemia-summary-data', [PhilpenAnalyticsDataController::class, 'getHypercholesterolemiaSummaryData'])
                        ->name('api-v2-analytics-get-hypercholesterolemia-summary-data');
                    Route::get('/get-hypercholesterolemia-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getHypercholesterolemiaSummaryRiskProfile'])
                        ->name('api-v2-analytics-get-hypercholesterolemia-summary-patient-info');
                    Route::get('/get-hypercholesterolemia-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getHypercholesterolemiaSummaryPatientInfo'])
                        ->name('api-v2-analytics-get-hypercholesterolemia-summary-risk-profile');

                    // endpoint: /respiratory_summary
                    Route::get('/get-respiratory-summary-data', [PhilpenAnalyticsDataController::class, 'getRespiratorySummaryData'])
                        ->name('api-v2-analytics-get-respiratory-summary-data');
                    Route::get('/get-respiratory-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getRespiratorySummaryRiskProfile'])
                        ->name('api-v2-analytics-get-respiratory-summary-patient-info');
                    Route::get('/get-respiratory-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getRespiratorySummaryPatientInfo'])
                        ->name('api-v2-analytics-get-respiratory-summary-risk-profile');

                    // endpoint: /probable_summary
                    Route::get('/get-probable-summary-data', [PhilpenAnalyticsDataController::class, 'getProbableSummaryData'])
                        ->name('api-v2-analytics-get-probable-summary-data');
                    Route::get('/get-probable-summary-patient-info', [PhilpenAnalyticsDataController::class, 'getProbableSummaryRiskProfile'])
                        ->name('api-v2-analytics-get-probable-summary-patient-info');
                    Route::get('/get-probable-summary-risk-profile', [PhilpenAnalyticsDataController::class, 'getProbableSummaryPatientInfo'])
                        ->name('api-v2-analytics-get-probable-summary-risk-profile');
                });
            });
        });

        // session validator
        Route::prefix('session')->group(function () {
            Route::get('/validate', [SessionController::class, 'validate'])->name('api-v2-session-validate');
        });

        // User Routes
        Route::prefix('user')->group(function () {
            Route::post('/checkauth', [UserController::class, 'checkAuth']);
            Route::post('/update-password', [UserController::class, 'updateUserPassword'])->name('api-v2-update-password');
            Route::post('/update-name', [UserController::class, 'updateUserFullName'])->name('api-v2-update-name');
            Route::post('/update-contact', [UserController::class, 'updateUserContact'])->name('api-v2-update-contact');
            Route::post('/update-email', [UserController::class, 'updateUserEmail'])->name('api-v2-update-email');
            Route::post('/create-remarks', [UserController::class, 'storeUserRemarks'])->name('api-v2-create-remarks');
            Route::post('/deactivate-account', [UserController::class, 'deactivateUserAccount'])->name('api-v2-deactivate-account');
            // two logouts for different functions
            Route::post('/logout', [AuthController::class, 'logout'])->name('api-v2-logout');
            Route::post('/logout-all-sessions', [AuthController::class, 'logoutAllSessions'])->name('api-v2-logout-all-sessions');
        });

        // Facility Routes
        Route::prefix('facility')->group(function () {
            Route::post('/retrieve-facility-by-code', [FacilityController::class, 'retrieveFacilityByCode'])->name('api-v2-retrieve-facility-by-code');
            Route::post('/add-facility', [FacilityController::class, 'addFacility'])->name('api-v2-add-facility');
            Route::post('/update-facility', [FacilityController::class, 'updateFacility'])->name('api-v2-update-facility');
            Route::post('/delete-facility', [FacilityController::class, 'deleteFacility'])->name('api-v2-delete-facility');
        });

        // Profile Routes
        Route::prefix('profile')->group(function () {
            Route::post('/retrieve-profile', [ProfileController::class, 'retrieveProfile'])->name('api-v2-retrieve-profile');
            Route::post('/add-profile', [ProfileController::class, 'addProfile'])->name('api-v2-add-profile');
            Route::post('/update-profile', [ProfileController::class, 'updateProfile'])->name('api-v2-update-profile');
            Route::post('/delete-profile', [ProfileController::class, 'deleteProfile'])->name('api-v2-delete-profile');
        });

        Route::prefix('system')->group(function () {
            Route::prefix('notifications')->group(function () {
                Route::get('/retrieve-notification-by-facility', [NotificationController::class, 'retrieveNotificationsByFacility'])->name('api-v2-retrieve-notifications-by-facility');
                Route::post('/add-notification', [NotificationController::class, 'addNotification'])->name('api-v2-add-notification');
                Route::post('/mark-notification-as-read', [NotificationController::class, 'markNotificationAsRead'])->name('api-v2-mark-notification-as-read');
                Route::post('/delete-notification', [NotificationController::class, 'deleteNotification'])->name('api-v2-delete-notification');
            });
        });

        Route::prefix('forms')->group(function () {
            Route::prefix('/general')->group(function () {
                Route::get('/retrieve-all-forms', [GeneralDataController::class, 'retrieveAllForms'])->name('api-v2-retrieve-all-forms');
                Route::get('/retrieve-recently-uploaded-forms', [GeneralDataController::class, 'retrieveRecentlyUploadedForms'])->name('api-v2-retrieve-recently-uploaded-formsg');
            });

            // Forms - Risk Assessment Routes
            Route::prefix('/risk-assessment')->group(function () {
                // ---- Converted to GET Statements ---- //
                Route::get('/retrieve-patient-risk-assessment', [PhilpenRiskDataController::class, 'retrievePatientRiskAssessment'])->name('api-v2-retrieve-patient-risk-assessment');
                Route::get('/retrieve-patient-risk-profile-by-facility', [PhilpenRiskDataController::class, 'retrievePatientRiskProfileByFacility'])->name('api-v2-retrieve-patient-risk-profile-by-facility');
                Route::get('/retrieve-patient-risk-profile', [PhilpenRiskDataController::class, 'retrievePatientRiskProfileWithoutFacility'])->name('api-v2-retrieve-patient-risk-profile-without-facility');

                Route::post('/add-risk-profile', [PhilpenRiskDataController::class, 'addRiskProfile'])->name('api-v2-add-risk-profile');
                Route::post('/add-risk-form', [PhilpenRiskDataController::class, 'addRiskForm'])->name('api-v2-add-risk-form');
                Route::post('/update-risk-profile', [PhilpenRiskDataController::class, 'updateRiskProfile'])->name('api-v2-update-risk-profile');
                Route::post('/update-risk-form', [PhilpenRiskDataController::class, 'updateRiskForm'])->name('api-v2-update-risk-form');
                Route::post('/delete-risk-profile', [PhilpenRiskDataController::class, 'deleteRiskProfile'])->name('api-v2-delete-risk-profile');
                Route::post('/delete-risk-form', [PhilpenRiskDataController::class, 'deleteRiskForm'])->name('api-v2-delete-risk-form');
            });

            // Forms - General PCH Risk Assessment Routes
            Route::prefix('/pch-risk-assessment')->group(function () {
                // ---- Converted to GET Statements ---- //
                Route::get('/pch-retrieve-patient-risk-assessment', [PchRiskDataController::class, 'retrievePchRiskAssessmentForm'])->name('api-v2-retrieve-pch-risk-assessment');
                Route::get('/pch-retrieve-patient-risk-profile-by-facility', [PchRiskDataController::class, 'retrievePchRiskProfileByFacility'])->name('api-v2-retrieve-pch-risk-profile-by-facility');
                Route::get('/pch-retrieve-patient-risk-profile', [PchRiskDataController::class, 'retrievePchRiskProfileWithoutFacility'])->name('api-v2-retrieve-pch-risk-profile-without-facility');

                Route::post('/pch-add-risk-profile', [PchRiskDataController::class, 'addPchRiskProfile'])->name('api-v2-add-pch-risk-profile');
                Route::post('/pch-add-risk-form', [PchRiskDataController::class, 'addPchRiskForm'])->name('api-v2-add-pch-risk-form');
                Route::post('/pch-update-risk-profile', [PchRiskDataController::class, 'updatePchRiskProfile'])->name('api-v2-update-pch-risk-profile');
                Route::post('/pch-update-risk-form', [PchRiskDataController::class, 'updatePchRiskForm'])->name('api-v2-update-pch-risk-form');
                Route::post('/pch-delete-risk-profile', [PchRiskDataController::class, 'deletePchRiskProfile'])->name('api-v2-delete-pch-risk-profile');
                Route::post('/pch-delete-risk-form', [PchRiskDataController::class, 'deletePchRiskForm'])->name('api-v2-delete-pch-risk-form');
            });
        });
    });
});
