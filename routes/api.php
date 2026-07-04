<?php

use App\Http\Controllers\Api\GuardianRegistrationController;
use App\Http\Controllers\Api\StudentDirectoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::get('/public/students-by-class/{schoolSlug}', [StudentDirectoryController::class, 'index']);

    Route::post('/public/guardian-registration/{schoolSlug}', [GuardianRegistrationController::class, 'store'])
        ->middleware('throttle:20,1');
});
