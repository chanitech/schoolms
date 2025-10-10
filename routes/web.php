<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    HomeController,
    ProfileController,
    StudentController,
    GuardianController,
    SchoolClassController,
    AcademicSessionController,
    EnrollmentController,
    StaffController,
    SubjectController,
    DormitoryController,
    ExamController,
    GradeController,
    DivisionController,
    StudentResultController,
    MarkController,
    DepartmentController,
    JobCardController,
    AttendanceController,
    LeaveController,
    EventController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('welcome'));

// Authentication routes
require __DIR__.'/auth.php';

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Resource routes
    Route::resources([
        'students' => StudentController::class,
        'guardians' => GuardianController::class,
        'enrollments' => EnrollmentController::class,
        'classes' => SchoolClassController::class,
        'dormitories' => DormitoryController::class,
        'sessions' => AcademicSessionController::class,
        'staff' => StaffController::class,
        'subjects' => SubjectController::class,
        'exams' => ExamController::class,
        'grades' => GradeController::class,
        'divisions' => DivisionController::class,
        'departments' => DepartmentController::class,
    ]);

    // Marks routes
    Route::prefix('marks')->name('marks.')->group(function () {
        // Custom AJAX route must come first
        Route::get('students', [MarkController::class, 'getStudents'])->name('students');

        // Resource-like routes for marks
        Route::get('/', [MarkController::class, 'index'])->name('index');
        Route::get('/create', [MarkController::class, 'create'])->name('create');
        Route::post('/', [MarkController::class, 'store'])->name('store');
        Route::get('/{mark}/edit', [MarkController::class, 'edit'])->name('edit');
        Route::put('/{mark}', [MarkController::class, 'update'])->name('update');
        Route::delete('/{mark}', [MarkController::class, 'destroy'])->name('destroy');
        // Optional show route (remove if not implemented)
        // Route::get('/{mark}', [MarkController::class, 'show'])->name('show');
    });

    // Results
    Route::prefix('results')->name('results.')->group(function () {
        Route::get('/', [StudentResultController::class, 'index'])->name('index');
        Route::get('/class', [StudentResultController::class, 'classResults'])->name('class');
        Route::get('/{student}', [StudentResultController::class, 'show'])->name('show');

       // Route::get('/results/class', [StudentResultController::class, 'classResults'])->name('results.class');
       
        // Export routes
    Route::get('/export/excel', [StudentResultController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf', [StudentResultController::class, 'exportPDF'])->name('export.pdf');



    });

    // JobCards
    Route::prefix('jobcards')->name('jobcards.')->group(function () {
        Route::get('/', [JobCardController::class, 'index'])->name('index');
        Route::get('/create', [JobCardController::class, 'create'])->name('create');
        Route::post('/', [JobCardController::class, 'store'])->name('store');
        Route::get('/{jobcard}/edit', [JobCardController::class, 'edit'])->name('edit');
        Route::patch('/{jobcard}', [JobCardController::class, 'update'])->name('update');
        Route::delete('/{jobcard}', [JobCardController::class, 'destroy'])->name('destroy');
        Route::get('/my', [JobCardController::class, 'myJobCards'])->name('my');
        Route::patch('/{jobcard}/status', [JobCardController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('/{jobcard}/rate', [JobCardController::class, 'rateTask'])->name('rateTask');
    });

    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/create', [AttendanceController::class, 'create'])->name('create');
        Route::post('/', [AttendanceController::class, 'store'])->name('store');
        Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
        Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
        Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
        Route::get('/bulk', [AttendanceController::class, 'bulkCreate'])->name('bulk.create');
        Route::post('/bulk/store', [AttendanceController::class, 'bulkStore'])->name('bulk.store');
        Route::get('/filter', [AttendanceController::class, 'filter'])->name('filter');
        Route::get('/export/excel', [AttendanceController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [AttendanceController::class, 'exportPDF'])->name('export.pdf');
    });

    // Leaves
  //  Route::prefix('leaves')->name('leaves.')->group(function () {
  //      Route::get('/', [LeaveController::class, 'index'])->name('index');
  //      Route::get('/received', [LeaveController::class, 'received'])->name('received');
  //      Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
  //      Route::post('/{leave}/reject', [LeaveController::class, 'reject'])->name('reject');
  //      Route::get('/received/export/excel', [LeaveController::class, 'exportReceivedExcel'])->name('received.export.excel');
  //      Route::get('/received/export/pdf', [LeaveController::class, 'exportReceivedPdf'])->name('received.export.pdf');
  //  });


    // Leaves
    Route::prefix('leaves')->name('leaves.')->group(function () {
    Route::get('/', [LeaveController::class, 'index'])->name('index');
    Route::get('/create', [LeaveController::class, 'create'])->name('create'); // <-- ✅ Add this
    Route::post('/', [LeaveController::class, 'store'])->name('store');       // <-- Optional if you need to handle form submission

    Route::get('/received', [LeaveController::class, 'received'])->name('received');
    Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
    Route::post('/{leave}/reject', [LeaveController::class, 'reject'])->name('reject');
    Route::get('/received/export/excel', [LeaveController::class, 'exportReceivedExcel'])->name('received.export.excel');
    Route::get('/received/export/pdf', [LeaveController::class, 'exportReceivedPdf'])->name('received.export.pdf');
});


    // Events
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::get('/create', [EventController::class, 'create'])->name('create');
        Route::post('/', [EventController::class, 'store'])->name('store');
        Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit');
        Route::put('/{event}', [EventController::class, 'update'])->name('update');
        Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
        Route::get('/export/excel', [EventController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [EventController::class, 'exportPDF'])->name('export.pdf');
        Route::get('/calendar', [EventController::class, 'calendar'])->name('calendar');
        Route::get('/fetch', [EventController::class, 'fetchEvents'])->name('fetch');
    });
});
