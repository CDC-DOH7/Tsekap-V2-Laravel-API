<?php

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
    Route::post('/register', [AuthController::class, 'register'])->name('api-v2-register');

    // Misc Routes
    Route::prefix('misc')->group(function () {
        Route::get('/get-all-facility', [MiscDataController::class, 'getAllFacility']);
        Route::get('/get-province', [MiscDataController::class, 'getProvince']);
        Route::get('/get-muncity', [MiscDataController::class, 'getMuncity']);
        Route::get('/get-barangay', [MiscDataController::class, 'getBarangay']);
        Route::get('/get-all-muncities', [MiscDataController::class, 'getAllMuncities']);
        Route::get('/get-all-barangay', [MiscDataController::class, 'getAllBarangays']);
    });

    // Protected Routes (Requires Authentication)
    Route::middleware('auth:sanctum')->group(function () {

        // User Routes
        Route::prefix('user')->group(function () {
            Route::post('/checkauth', [UserController::class, 'checkAuth']);
            Route::post('/update-password', [UserController::class, 'updateUserPassword'])->name('api-v2-update-password');
            Route::post('/update-name', [UserController::class, 'updateUserFullName'])->name('api-v2-update-name');
            Route::post('/update-contact', [UserController::class, 'updateUserContact'])->name('api-v2-update-contact');
            Route::post('/update-email', [UserController::class, 'updateUserEmail'])->name('api-v2-update-email');
            Route::post('/logout', [AuthController::class, 'logout'])->name('api-v2-logout');
        });

        // Facility Routes
        Route::prefix('facility')->group(function () {
            Route::post('/retrieve-facility-by-code', [FacilityController::class, 'retrieveFacilityByCode']);
            Route::post('/add-facility', [FacilityController::class, 'addFacility']);
            Route::post('/update-facility', [FacilityController::class, 'updateFacility']);
            Route::post('/delete-facility', [FacilityController::class, 'deleteFacility']);
        });

        // Profile Routes
        Route::prefix('profile')->group(function () {
            Route::post('/retrieve-profile', [ProfileController::class, 'retrieveProfile']);
            Route::post('/add-profile', [ProfileController::class, 'addProfile']);
            Route::post('/update-profile', [ProfileController::class, 'updateProfile']);
            Route::post('/delete-profile', [ProfileController::class, 'deleteProfile']);
        });

        // Forms - Risk Assessment Routes
        Route::prefix('forms/risk-assessment')->group(function () {
            Route::post('/retrieve-patient-risk-assessment', [DataController::class, 'retrievePatientRiskAssessment']);
            Route::post('/retrieve-patient-risk-profile-by-facility', [DataController::class, 'retrievePatientRiskProfileByFacility']);
            Route::post('/retrieve-patient-risk-profile', [DataController::class, 'retrievePatientRiskProfileWithoutFacility']);
            Route::post('/add-risk-profile', [DataController::class, 'addRiskProfile']);
            Route::post('/add-risk-form', [DataController::class, 'addRiskForm']);
            Route::post('/update-risk-profile', [DataController::class, 'updateRiskProfile']);
            Route::post('/update-risk-form', [DataController::class, 'updateRiskForm']);
            Route::post('/delete-risk-profile', [DataController::class, 'deleteRiskProfile']);
            Route::post('/delete-risk-form', [DataController::class, 'deleteRiskForm']);
        });
    });
});
