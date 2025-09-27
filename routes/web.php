<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\DormitoryController;
use App\Http\Controllers\AcademicSessionController;
use App\Http\Controllers\EnrollmentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes (if using Laravel Breeze / Jetstream / Auth)
require __DIR__.'/auth.php';

// Home / Dashboard
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Students CRUD
    Route::resource('students', StudentController::class);

    // Guardians CRUD
    Route::resource('guardians', GuardianController::class);

    Route::resource('enrollments', EnrollmentController::class);


    // Classes CRUD
   // Route::resource('classes', SchoolClassController::class);

    // Dormitories CRUD
  //  Route::resource('dormitories', DormitoryController::class);

    // Academic Sessions CRUD
  //  Route::resource('sessions', AcademicSessionController::class);
});
