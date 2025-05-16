<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TsekapV2\UserController;
use App\Http\Controllers\TsekapV2\Misc\MiscDataController;
use App\Http\Controllers\TsekapV2\FacilityController;
use App\Http\Controllers\TsekapV2\ProfileController;
use App\Http\Controllers\TsekapV2\UserHealthFacilityController;
use App\Http\Controllers\TsekapV2\Forms\GeneralDataController;
use App\Http\Controllers\TsekapV2\Forms\RiskAssessmentForm\DataController;
use App\Http\Controllers\TsekapV2\Analytics\DataRetrieval\AnalyticsDataController;
use App\Http\Controllers\TsekapV2\SessionController;
use App\Http\Controllers\TsekapV2\Analytics\ProfilingTargetSetting\ProfilingTargetController;

Route::prefix('v2')->group(function () {
    // Non-authenticated Routes
    Route::post('/login', [AuthController::class, 'login'])->name('api-v2-login');
    Route::post('/register', [AuthController::class, 'selfRegisterUser'])->name('api-v2-register');

    // Misc Routes
    Route::prefix('misc')->group(function () {
        // For Addresses and Facilities
        Route::get('/get-all-facility', [MiscDataController::class, 'getAllFacility'])->name('api-v2-get-all-facility');
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
            Route::get('/list-users', [AdminController::class, 'listUnverifiedUsers'])->name('api-v2-admin-list-unverified-users');
        });

        // Analytics Routes
        Route::prefix('analytics')->group(function () {
            // endpoint : /patient_summary
            Route::get('/get-patient-summary', [AnalyticsDataController::class, 'getPatientSummary'])->name('api-v2-analytics-get-patient-summary');

            // endpoint : /age_group_summary
            Route::get('/get-age-group-summary', [AnalyticsDataController::class, 'getAgeGroupSummary'])->name('api-v2-analytics-get-age-group-summary');

            // endpoint : /age_range_summary
            Route::get('/get-age-range-summary', [AnalyticsDataController::class, 'getAgeRangeSummary'])->name('api-v2-analytics-get-age-range-summary');
            Route::get('/get-age-range-summary-age-brackets', [AnalyticsDataController::class, 'getAgeRangeSummaryAgeBrackets'])
                ->name('api-v2-analytics-get-age-range-summary-age-brackets');

            // endpoint : /month_year_summary
            Route::get('/get-month-year-summary-patient-data', [AnalyticsDataController::class, 'getMonthYearSummaryPatientData'])
                ->name('api-v2-analytics-get-month-year-summary-patient-data');

            // endpoint: /morbidity_summary
            Route::get('/get-morbidity-summary-patient-data', [AnalyticsDataController::class, 'getMorbiditySummaryPatientData'])
                ->name('api-v2-analytics-get-morbidity-summary-patient-data');

            // endpoint: /monthly_summary   
            Route::get('/get-monthly-summary-patient-data', [AnalyticsDataController::class, 'getMonthlySummaryPatientData'])
                ->name('api-v2-analytics-get-monthly-summary-patient-data');
            Route::get('/get-all-monthly-summary-age-brackets', [AnalyticsDataController::class, 'getAllMonthlySummaryAgeBrackets'])
                ->name('api-v2-analytics-get-all-monthly-summary-age-brackets');
            Route::get('/get-monthly-summary-risk-profile-by-facility', [AnalyticsDataController::class, 'getMonthlySummaryRiskProfileByFacility'])
                ->name('api-v2-analytics-get-monthly-summary-risk-profile-by-facility');

            // endpoint: /clinical_complaints_summary
            Route::get('/get-clinical-complaints-ar-records', [AnalyticsDataController::class, 'getClinicalComplaintsArRecords'])
                ->name('api-v2-get-clinical-complaints-ar-records');
            Route::get('/get-clinical-complaints-risk-profile', [AnalyticsDataController::class, 'getClinicalComplaintsRiskProfile'])
                ->name('api-v2-analytics-get-clinical-complaints-risk-profile');
            Route::get('/get-clinical-complaints-age-brackets', [AnalyticsDataController::class, 'getClinicalComplaintsAgeBrackets'])
                ->name('api-v2-analytics-get-clinical-complaints-age-brackets');

            // endpoint: /tobacco_summary
            Route::get('/get-tobacco-summary-data', [AnalyticsDataController::class, 'getTobaccoSummaryData'])
                ->name('api-v2-analytics-get-tobacco-summary-data');
            Route::get('/get-tobacco-summary-patient-info', [AnalyticsDataController::class, 'getTobaccoSummaryPatientInfo'])
                ->name('api-v2-analytics-tobacco-summary-patient-info');
            Route::get('/get-tobacco-summary-risk-profile', [AnalyticsDataController::class, 'getTobaccoSummaryRiskProfile'])
                ->name('api-v2-analytics-get-tobacco-summary-risk-profile');
            Route::get('/get-tobacco-summary-age-brackets', [AnalyticsDataController::class, 'getTobaccoSummaryAgeBrackets'])
                ->name('api-v2-analytics-get-tobacco-summary-age-brackets');

            // endpoint: /alcohol_intake_summary
            Route::get('/get-alcohol-intake-summary-data', [AnalyticsDataController::class, 'getAlcoholIntakeSummaryData'])
                ->name('api-v2-analytics-get-alcohol-intake-summary-data');
            Route::get('/get-alcohol-intake-summary-patient-info', [AnalyticsDataController::class, 'getAlcoholIntakeSummaryPatientInfo'])
                ->name('api-v2-analytics-get-alcohol-intake-summary-patient-info');
            Route::get('/get-alcohol-intake-summary-risk-profile', [AnalyticsDataController::class, 'getAlcoholIntakeSummaryRiskProfile'])
                ->name('api-v2-analytics-get-alcohol-intake-summary-risk-profile');
            Route::get('/get-alcohol-intake-summary-age-brackets', [AnalyticsDataController::class, 'getAlcoholIntakeSummaryAgeBrackets'])
                ->name('api-v2-analytics-get-alcohol-intake-summary-age-brackets');

            // endpoint: /physical_activity_summary
            Route::get('/get-physical-activity-summary-data', [AnalyticsDataController::class, 'getPhysicalActivitySummaryData'])
                ->name('api-v2-analytics-get-physical-activity-summary-data');
            Route::get('/get-physical-activity-summary-patient-info', [AnalyticsDataController::class, 'getPhysicalActivitySummaryPatientInfo'])
                ->name('api-v2-analytics-get-physical-activity-summary-patient-info');
            Route::get('/get-physical-activity-summary-risk-profile', [AnalyticsDataController::class, 'getPhysicalActivitySummaryRiskProfile'])
                ->name('api-v2-analytics-get-physical-activity-summary-risk-profile');
            Route::get('/get-physical-activity-summary-age-brackets', [AnalyticsDataController::class, 'getPhysicalActivitySummaryAgeBrackets'])
                ->name('api-v2-analytics-get-physical-activity-summary-age-brackets');

            // endpoint: /nutrition_summary
            Route::get('/get-nutrition-summary-data', [AnalyticsDataController::class, 'getNutritionSummaryData'])
                ->name('api-v2-analytics-get-nutrition-summary-data');
            Route::get('/get-nutrition-summary-patient-info', [AnalyticsDataController::class, 'getNutritionSummaryPatientInfo'])
                ->name('api-v2-analytics-get-nutrition-summary-patient-info');
            Route::get('/get-nutrition-summary-risk-profile', [AnalyticsDataController::class, 'getNutritionSummaryRiskProfile'])
                ->name('api-v2-analytics-get-nutrition-summary-risk-profile');
            Route::get('/get-nutrition-summary-age-brackets', [AnalyticsDataController::class, 'getNutritionSummaryAgeBrackets'])
                ->name('api-v2-analytics-get-nutrition-summary-age-brackets');

            // endpoint: /prev_med_history_summary
            Route::get('/get-prev-med-history-summary-data', [AnalyticsDataController::class, 'getPrevMedHistorySummaryData'])
                ->name('api-v2-analytics-get-prev-med-history-summary-data');
            Route::get('/get-prev-med-history-summary-patient-info', [AnalyticsDataController::class, 'getPrevMedHistorySummaryRiskProfile'])
                ->name('api-v2-analytics-get-prev-med-history-summary-patient-info');
            Route::get('/get-prev-med-history-summary-risk-profile', [AnalyticsDataController::class, 'getPrevMedHistorySummaryPatientInfo'])
                ->name('api-v2-analytics-get-prev-med-history-summary-risk-profile');
            Route::get('/get-prev-med-history-summary-age-brackets', [AnalyticsDataController::class, 'getPrevMedHistorySummaryAgeBrackets'])
                ->name('api-v2-analytics-get-prev-med-history-summary-age-brackets');

            // endpoint: /family_history_summary
            Route::get('/get-family-history-summary-data', [AnalyticsDataController::class, 'getFamilyHistorySummaryData'])
                ->name('api-v2-analytics-get-family-history-summary-data');
            Route::get('/get-family-history-summary-patient-info', [AnalyticsDataController::class, 'getFamilyHistorySummaryRiskProfile'])
                ->name('api-v2-analytics-get-family-history-summary-patient-info');
            Route::get('/get-family-history-summary-risk-profile', [AnalyticsDataController::class, 'getFamilyHistorySummaryPatientInfo'])
                ->name('api-v2-analytics-get-family-history-summary-risk-profile');
            Route::get('/get-family-history-summary-age-brackets', [AnalyticsDataController::class, 'getFamilyHistorySummaryAgeBrackets'])
                ->name('api-v2-analytics-get-family-history-summary-age-brackets');

            // endpoint: /bp1_summary
            Route::get('/get-bp1-summary-data', [AnalyticsDataController::class, 'getBp1SummaryData'])
                ->name('api-v2-analytics-get-bp1-summary-data');
            Route::get('/get-bp1-summary-patient-info', [AnalyticsDataController::class, 'getBp1SummaryRiskProfile'])
                ->name('api-v2-analytics-get-bp1-summary-patient-info');
            Route::get('/get-bp1-summary-risk-profile', [AnalyticsDataController::class, 'getBp1SummaryPatientInfo'])
                ->name('api-v2-analytics-get-bp1-summary-risk-profile');
            Route::get('/get-bp1-summary-age-brackets', [AnalyticsDataController::class, 'getBp1SummaryAgeBrackets'])
                ->name('api-v2-analytics-get-bp1-summary-age-brackets');

            // endpoint: /hypertension_summary
            Route::get('/get-hypertension-summary-data', [AnalyticsDataController::class, 'getBp2SummaryData'])
                ->name('api-v2-analytics-get-hypertension-summary-data');
            Route::get('/get-hypertension-summary-patient-info', [AnalyticsDataController::class, 'getBp2SummaryRiskProfile'])
                ->name('api-v2-analytics-get-hypertension-summary-patient-info');
            Route::get('/get-hypertension-summary-risk-profile', [AnalyticsDataController::class, 'getBp2SummaryPatientInfo'])
                ->name('api-v2-analytics-get-hypertension-summary-risk-profile');
            Route::get('/get-hypertension-summary-age-brackets', [AnalyticsDataController::class, 'getBp2SummaryAgeBrackets'])
                ->name('api-v2-analytics-get-hypertension-summary-age-brackets');

            // endpoint: /diabetes_summary
            Route::get('/get-diabetes-summary-data', [AnalyticsDataController::class, 'getDiabetesSummaryData'])
                ->name('api-v2-analytics-get-diabetes-summary-data');
            Route::get('/get-diabetes-summary-patient-info', [AnalyticsDataController::class, 'getDiabetesSummaryRiskProfile'])
                ->name('api-v2-analytics-get-diabetes-summary-patient-info');
            Route::get('/get-diabetes-summary-risk-profile', [AnalyticsDataController::class, 'getDiabetesSummaryPatientInfo'])
                ->name('api-v2-analytics-get-diabetes-summary-risk-profile');
            Route::get('/get-diabetes-summary-age-brackets', [AnalyticsDataController::class, 'getDiabetesSummaryAgeBrackets'])
                ->name('api-v2-analytics-get-diabetes-summary-age-brackets');

            // endpoint: /hypercholesterolemia_summary
            Route::get('/get-hypercholesterolemia-summary-data', [AnalyticsDataController::class, 'getHypercholesterolemiaSummaryData'])
                ->name('api-v2-analytics-get-hypercholesterolemia-summary-data');
            Route::get('/get-hypercholesterolemia-summary-patient-info', [AnalyticsDataController::class, 'getHypercholesterolemiaSummaryRiskProfile'])
                ->name('api-v2-analytics-get-hypercholesterolemia-summary-patient-info');
            Route::get('/get-hypercholesterolemia-summary-risk-profile', [AnalyticsDataController::class, 'getHypercholesterolemiaSummaryPatientInfo'])
                ->name('api-v2-analytics-get-hypercholesterolemia-summary-risk-profile');
            Route::get('/get-hypercholesterolemia-summary-age-brackets', [AnalyticsDataController::class, 'getHypercholesterolemiaSummaryAgeBrackets'])
                ->name('api-v2-analytics-get-hypercholesterolemia-summary-age-brackets');

            // endpoint: /respiratory_summary
            Route::get('/get-respiratory-summary-data', [AnalyticsDataController::class, 'getRespiratorySummaryData'])
                ->name('api-v2-analytics-get-respiratory-summary-data');
            Route::get('/get-respiratory-summary-patient-info', [AnalyticsDataController::class, 'getRespiratorySummaryRiskProfile'])
                ->name('api-v2-analytics-get-respiratory-summary-patient-info');
            Route::get('/get-respiratory-summary-risk-profile', [AnalyticsDataController::class, 'getRespiratorySummaryPatientInfo'])
                ->name('api-v2-analytics-get-respiratory-summary-risk-profile');
            Route::get('/get-respiratory-summary-age-brackets', [AnalyticsDataController::class, 'getRespiratorySummaryAgeBrackets'])
                ->name('api-v2-analytics-get-respiratory-summary-age-brackets');

            // endpoint: /probable_summary
            Route::get('/get-probable-summary-data', [AnalyticsDataController::class, 'getProbableSummaryData'])
                ->name('api-v2-analytics-get-probable-summary-data');
            Route::get('/get-probable-summary-patient-info', [AnalyticsDataController::class, 'getProbableSummaryRiskProfile'])
                ->name('api-v2-analytics-get-probable-summary-patient-info');
            Route::get('/get-probable-summary-risk-profile', [AnalyticsDataController::class, 'getProbableSummaryPatientInfo'])
                ->name('api-v2-analytics-get-probable-summary-risk-profile');
            Route::get('/get-probable-summary-age-brackets', [AnalyticsDataController::class, 'getProbableSummaryAgeBrackets'])
                ->name('api-v2-analytics-get-probable-summary-age-brackets');
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

        // Targets
        Route::prefix('target')->group(function () {
            Route::get('/create-profiling-target', [ProfilingTargetController::class, 'createProfilingTarget']);
            Route::get('/retrieve-profiling-target', [ProfilingTargetController::class, 'retrieveProfilingTarget']);
            Route::post('/retrieve-profiling-target-by-id', [ProfilingTargetController::class, 'retrieveProfilingTargetById']);
            Route::post('/update-profiling-target', [ProfilingTargetController::class, 'updateProfilingTarget']);
            Route::post('/delete-profiling-target', [ProfilingTargetController::class, 'deleteProfilingTarget']);
        });

        Route::prefix('forms')->group(function () {
            Route::prefix('/general')->group(function () {
                Route::get('/retrieve-all-forms', [GeneralDataController::class, 'retrieveAllForms'])->name('api-v2-retrieve-all-forms');
                Route::get('/retrieve-recently-uploaded-forms', [GeneralDataController::class, 'retrieveRecentlyUploadedForms'])->name('api-v2-retrieve-recently-uploaded-formsg');
            });

            // Forms - Risk Assessment Routes
            Route::prefix('/risk-assessment')->group(function () {                // ---- Previously POST Statements ---- //
                // Route::post('/retrieve-patient-risk-assessment', [DataController::class, 'retrievePatientRiskAssessment'])->name('api-v2-retrieve-patient-risk-assessment');
                // Route::post('/retrieve-patient-risk-profile-by-facility', [DataController::class, 'retrievePatientRiskProfileByFacility'])->name('api-v2-retrieve-patient-risk-profile-by-facility');
                // Route::post('/retrieve-patient-risk-profile', [DataController::class, 'retrievePatientRiskProfileWithoutFacility'])->name('api-v2-retrieve-patient-risk-profile-without-facility');

                // ---- Converted to GET Statements ---- //
                Route::get('/retrieve-patient-risk-assessment', [DataController::class, 'retrievePatientRiskAssessment'])->name('api-v2-retrieve-patient-risk-assessment');
                Route::get('/retrieve-patient-risk-profile-by-facility', [DataController::class, 'retrievePatientRiskProfileByFacility'])->name('api-v2-retrieve-patient-risk-profile-by-facility');
                Route::get('/retrieve-patient-risk-profile', [DataController::class, 'retrievePatientRiskProfileWithoutFacility'])->name('api-v2-retrieve-patient-risk-profile-without-facility');

                Route::post('/add-risk-profile', [DataController::class, 'addRiskProfile'])->name('api-v2-add-risk-profile');
                Route::post('/add-risk-form', [DataController::class, 'addRiskForm'])->name('api-v2-add-risk-form');
                Route::post('/update-risk-profile', [DataController::class, 'updateRiskProfile'])->name('api-v2-update-risk-profile');
                Route::post('/update-risk-form', [DataController::class, 'updateRiskForm'])->name('api-v2-update-risk-form');
                Route::post('/delete-risk-profile', [DataController::class, 'deleteRiskProfile'])->name('api-v2-delete-risk-profile');
                Route::post('/delete-risk-form', [DataController::class, 'deleteRiskForm'])->name('api-v2-delete-risk-form');
            });
        });
    });
});
