<?php

use App\Http\Controllers\Api\BiometricScanController;
use App\Http\Controllers\Api\GuardianRegistrationController;
use App\Http\Controllers\Api\StudentDirectoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::get('/public/students-by-class/{schoolSlug}', [StudentDirectoryController::class, 'index']);

    Route::post('/public/guardian-registration/{schoolSlug}', [GuardianRegistrationController::class, 'store'])
        ->middleware('throttle:20,1');

    // Fingerprint relay ingestion. The outer api.key check is defense-in-depth;
    // the per-school biometric_api_key (checked inside the controller) is
    // what actually isolates one school's relay credential from another's.
    Route::post('/public/biometric-scans/{schoolSlug}', [BiometricScanController::class, 'store'])
        ->middleware('throttle:30,1');
});
