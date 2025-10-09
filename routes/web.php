<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\AcademicSessionController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\StaffController;   
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\DormitoryController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GradeController;     
use App\Http\Controllers\DivisionController;  
use App\Http\Controllers\StudentResultController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\JobCardController;




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
    // Enrollments CRUD
    Route::resource('enrollments', EnrollmentController::class);


    // Classes CRUD
    Route::resource('classes', SchoolClassController::class);

    // Dormitories CRUD
    Route::resource('dormitories', DormitoryController::class);
   

    // Academic Sessions CRUD
    Route::resource('sessions', AcademicSessionController::class);

    // Staff CRUD
    Route::resource('staff', StaffController::class);
    // Subjects CRUD
    Route::resource('subjects', SubjectController::class);
    // Exams CRUD
    Route::resource('exams', ExamController::class);
    // Grades CRUD
    Route::resource('grades', GradeController::class);
    // Divisions CRUD
    Route::resource('divisions', DivisionController::class);

    // AJAX: get students by class & session
    Route::get('/marks/students', [MarkController::class, 'getStudents'])->name('marks.students');
        // Marks CRUD      
    Route::resource('marks', MarkController::class);  


    // Class-wise results
    Route::get('/results/class', [StudentResultController::class, 'classResults'])->name('results.class');
    
   


    // Student Results with GPA & Division  

    Route::get('/students/{student}/results', [StudentResultController::class, 'show'])
     ->name('students.results');


     

Route::prefix('results')->group(function () {
    Route::get('/', [StudentResultController::class, 'index'])->name('results.index');
    Route::get('/{student}', [StudentResultController::class, 'show'])->name('results.show');
});





Route::resource('departments', DepartmentController::class)->middleware('auth');

Route::resource('staff', StaffController::class);


Route::middleware(['auth'])->group(function () {
    Route::get('jobcards', [JobCardController::class, 'index'])->name('jobcards.index');
    Route::get('jobcards/create', [JobCardController::class, 'create'])->name('jobcards.create');
    Route::post('jobcards', [JobCardController::class, 'store'])->name('jobcards.store');
    Route::get('jobcards/{jobcard}/edit', [JobCardController::class, 'edit'])->name('jobcards.edit');
    Route::patch('jobcards/{jobcard}', [JobCardController::class, 'update'])->name('jobcards.update');
    Route::delete('jobcards/{jobcard}', [JobCardController::class, 'destroy'])->name('jobcards.destroy');

    // Staff routes
    Route::get('jobcards/my', [JobCardController::class, 'myJobCards'])->name('jobcards.my');
    Route::patch('jobcards/{jobcard}/status', [JobCardController::class, 'updateStatus'])->name('jobcards.updateStatus');
    Route::patch('jobcards/{jobcard}/rate', [JobCardController::class, 'rateTask'])->name('jobcards.rateTask');
});









});
