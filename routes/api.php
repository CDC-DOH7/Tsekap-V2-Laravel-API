<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TsekapV2\UserController;
use App\Http\Controllers\TsekapV2\Misc\MiscDataController;
use App\Http\Controllers\TsekapV2\FacilityController;
use App\Http\Controllers\TsekapV2\ProfileController;
use App\Http\Controllers\TsekapV2\Forms\RiskAssessmentForm\DataController;

Route::prefix('v2')->group(function () {
    // Non-authenticated Routes
    Route::post('/login', [AuthController::class, 'login'])->name('api-v2-login');

    // Misc Routes
    Route::prefix('misc')->group(function () {
        Route::get('/get-all-facility', [MiscDataController::class, 'getAllFacility'])->name('api-v2-get-all-facility');
        Route::get('/get-province', [MiscDataController::class, 'getProvince'])->name('api-v2-get-province');
        Route::get('/get-muncity', [MiscDataController::class, 'getMuncity'])->name('api-v2-get-muncity');
        Route::get('/get-barangay', [MiscDataController::class, 'getBarangay'])->name('api-v2-get-barangay');
        Route::get('/get-all-provinces', [MiscDataController::class, 'getAllProvinces'])->name('api-v2-get-all-provinces');
        Route::get('/get-all-muncities', [MiscDataController::class, 'getAllMuncities'])->name('api-v2-get-all-muncities');
        Route::get('/get-all-barangay', [MiscDataController::class, 'getAllBarangays'])->name('api-v2-get-all-barangay');

        // Citizenships and Religions
        Route::get('/get-all-citizenships', [MiscDataController::class, 'getAllCitizenships'])->name('api-v2-get-all-citizenships');
        Route::get('/get-all-religions', [MiscDataController::class, 'getAllReligions'])->name('api-v2-get-all-religions');
    });

    // Protected Routes (Requires Authentication)
    Route::middleware('auth:sanctum')->group(function () {
        
        // Admin Routes
        Route::prefix('admin')->group(function () {
            Route::post('/reset-user-password', [AdminController::class, 'resetUserPassword'])->name('api-v2-reset-user-password');
            Route::post('/register-user', [AdminController::class, 'registerUser'])->name('api-v2-register-user');
        });
        
        // User Routes
        Route::prefix('user')->group(function () {
            Route::post('/checkauth', [UserController::class, 'checkAuth']);
            Route::post('/update-password', [UserController::class, 'updateUserPassword'])->name('api-v2-update-password');
            Route::post('/update-name', [UserController::class, 'updateUserFullName'])->name('api-v2-update-name');
            Route::post('/update-contact', [UserController::class, 'updateUserContact'])->name('api-v2-update-contact');
            Route::post('/update-email', [UserController::class, 'updateUserEmail'])->name('api-v2-update-email');
            
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
            Route::post('/add-profile', [ProfileController::class, 'addProfile'])->name('api-v2-retrieve-profile');
            Route::post('/update-profile', [ProfileController::class, 'updateProfile'])->name('api-v2-update-profile');
            Route::post('/delete-profile', [ProfileController::class, 'deleteProfile'])->name('api-v2-delete-profile');
        });

        // Forms - Risk Assessment Routes
        Route::prefix('forms/risk-assessment')->group(function () {
            Route::post('/retrieve-patient-risk-assessment', [DataController::class, 'retrievePatientRiskAssessment'])->name('api-v2-retrieve-patient-risk-assessment');
            Route::post('/retrieve-patient-risk-profile-by-facility', [DataController::class, 'retrievePatientRiskProfileByFacility'])->name('api-v2-retrieve-patient-risk-profile-by-facility');
            Route::post('/retrieve-patient-risk-profile', [DataController::class, 'retrievePatientRiskProfileWithoutFacility'])->name('api-v2-retrieve-patient-risk-profile-without-facility');
            Route::post('/add-risk-profile', [DataController::class, 'addRiskProfile'])->name('api-v2-add-risk-profile');
            Route::post('/add-risk-form', [DataController::class, 'addRiskForm'])->name('api-v2-add-risk-form');
            Route::post('/update-risk-profile', [DataController::class, 'updateRiskProfile'])->name('api-v2-update-risk-profile');
            Route::post('/update-risk-form', [DataController::class, 'updateRiskForm'])->name('api-v2-update-risk-form');
            Route::post('/delete-risk-profile', [DataController::class, 'deleteRiskProfile'])->name('api-v2-delete-risk-profile');
            Route::post('/delete-risk-form', [DataController::class, 'deleteRiskForm'])->name('api-v2-delete-risk-form');
        });
    });
});
