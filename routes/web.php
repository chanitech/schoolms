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
    EventController,
    SchoolInfoController,
    AcademicYearController,
    RoleController,
    SystemLogController,
    PermissionController,
    HRReportController,
    BookController,
    LendingController,
    CategoryController
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
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    });

    // System Settings: Roles & Permissions
    Route::middleware(['role:Admin'])->prefix('settings')->group(function () {

        // Roles CRUD
        Route::resource('roles', RoleController::class)->names([
            'index'   => 'roles.index',
            'create'  => 'roles.create',
            'store'   => 'roles.store',
            'show'    => 'roles.show',
            'edit'    => 'roles.edit',
            'update'  => 'roles.update',
            'destroy' => 'roles.destroy',
        ]);

        // Permissions CRUD
        Route::resource('permissions', PermissionController::class)->names([
            'index'   => 'permissions.index',
            'create'  => 'permissions.create',
            'store'   => 'permissions.store',
            'show'    => 'permissions.show',
            'edit'    => 'permissions.edit',
            'update'  => 'permissions.update',
            'destroy' => 'permissions.destroy',
        ]);

        // ✅ School Info Routes
    Route::get('school-info', [App\Http\Controllers\SchoolInfoController::class, 'index'])
        ->name('school.info.index');
    Route::post('school-info', [App\Http\Controllers\SchoolInfoController::class, 'update'])
        ->name('school.info.update');
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

    // Marks
    Route::prefix('marks')->name('marks.')->group(function () {
        Route::get('students', [MarkController::class, 'getStudents'])->name('students');
        Route::get('/', [MarkController::class, 'index'])->name('index');
        Route::get('/create', [MarkController::class, 'create'])->name('create');
        Route::post('/', [MarkController::class, 'store'])->name('store');
        Route::get('/{mark}/edit', [MarkController::class, 'edit'])->name('edit');
        Route::put('/{mark}', [MarkController::class, 'update'])->name('update');
        Route::delete('/{mark}', [MarkController::class, 'destroy'])->name('destroy');
    });

    // Results
    Route::prefix('results')->name('results.')->group(function () {
        Route::get('/', [StudentResultController::class, 'index'])->name('index');
        Route::get('/class', [StudentResultController::class, 'classResults'])->name('class');
        Route::get('/{student}', [StudentResultController::class, 'show'])->name('show');
        Route::get('/export/excel', [StudentResultController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [StudentResultController::class, 'exportPDF'])->name('export.pdf');
    });

    Route::prefix('jobcards')->name('jobcards.')->group(function () {
    Route::get('/', [JobCardController::class, 'index'])->name('index');
    Route::get('/create', [JobCardController::class, 'create'])->name('create');
    Route::post('/', [JobCardController::class, 'store'])->name('store');
    Route::get('/{jobcard}/edit', [JobCardController::class, 'edit'])->name('edit');
    Route::put('/{jobcard}', [JobCardController::class, 'update'])->name('update');
    Route::delete('/{jobcard}', [JobCardController::class, 'destroy'])->name('destroy');

    // Staff routes
    Route::get('/my', [JobCardController::class, 'myJobCards'])->name('my');

    // Allow PATCH for status update
    Route::patch('/{jobcard}/update-status', [JobCardController::class, 'updateStatus'])->name('updateStatus');

    // Allow PATCH for rating task
    Route::patch('/{jobcard}/rate-task', [JobCardController::class, 'rateTask'])->name('rateTask');
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
        Route::post('/bulk/store', [AttendanceController::class, 'bulk.store']);
        Route::get('/filter', [AttendanceController::class, 'filter'])->name('filter');
        Route::get('/export/excel', [AttendanceController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [AttendanceController::class, 'exportPDF'])->name('export.pdf');
    });

   // Leaves
Route::prefix('leaves')->name('leaves.')->group(function () {
    Route::get('/', [LeaveController::class, 'index'])->name('index');
    Route::get('/create', [LeaveController::class, 'create'])->name('create');
    Route::post('/', [LeaveController::class, 'store'])->name('store');

    // Edit & Update
    Route::get('/{leave}/edit', [LeaveController::class, 'edit'])->name('edit');
    Route::put('/{leave}', [LeaveController::class, 'update'])->name('update');

    // Delete
    Route::delete('/{leave}', [LeaveController::class, 'destroy'])->name('destroy');

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










    // HR Reports
Route::prefix('hr-reports')
    ->middleware(['auth'])
    ->name('hr-reports.')
    ->group(function () {

        Route::get('/', [HRReportController::class, 'index'])
            ->name('index')
            ->middleware('permission:view hr reports');

        Route::get('/staff', [HRReportController::class, 'staffReport'])
            ->name('staff')
            ->middleware('permission:view staff report');

        Route::get('/attendance', [HRReportController::class, 'attendanceReport'])
            ->name('attendance')
            ->middleware('permission:view attendance report');

        Route::get('/leaves', [HRReportController::class, 'leaveReport'])
            ->name('leaves')
            ->middleware('permission:view leave report');

        Route::get('/leaves/export/excel', [HRReportController::class, 'exportLeaveExcel'])
            ->name('leaves.export.excel')
            ->middleware('permission:view leave report');

        Route::get('/leaves/export/pdf', [HRReportController::class, 'exportLeavePDF'])
            ->name('leaves.export.pdf')
            ->middleware('permission:view leave report');

        Route::get('/jobcards', [HRReportController::class, 'jobCardReport'])
            ->name('jobcards');
            //->middleware('permission:view job card report');

        Route::get('/evaluation', [HRReportController::class, 'evaluationReport'])
            ->name('evaluation');
           // ->middleware('permission:view evaluation report');

        Route::get('/evaluation/export', [HRReportController::class, 'exportEvaluation'])
            ->name('evaluation.export');
           //->middleware('permission:view evaluation report');

        Route::get('/events', [HRReportController::class, 'eventReport'])
            ->name('events')
            ->middleware('permission:view event report');

        Route::get('/summary', [HRReportController::class, 'summaryDashboard'])
            ->name('summary')
            ->middleware('permission:view hr summary dashboard');
});

Route::get('/hr-reports/evaluation/export', [HRReportController::class, 'exportEvaluation'])
    ->name('hr.reports.export.evaluation');



Route::group(['middleware' => ['auth']], function () {

    // Library module
    Route::prefix('library')->name('library.')->group(function () {

        // 📘 AJAX Routes (must come before resource routes)
        Route::get('lendings/get-students/{class_id}', [App\Http\Controllers\LendingController::class, 'getStudentsByClass'])
            ->name('lendings.getStudentsByClass');

        Route::get('lendings/get-staff/{role}', [App\Http\Controllers\LendingController::class, 'getStaffByRole'])
            ->name('lendings.getStaffByRole');

        // 📚 Books
        Route::resource('books', App\Http\Controllers\BookController::class)->names([
            'index' => 'books.index',
            'create' => 'books.create',
            'store' => 'books.store',
            'edit' => 'books.edit',
            'update' => 'books.update',
            'destroy' => 'books.destroy',
            'show' => 'books.show',
        ]);

        // 🗂 Categories
        Route::resource('categories', App\Http\Controllers\CategoryController::class)->names([
            'index' => 'categories.index',
            'create' => 'categories.create',
            'store' => 'categories.store',
            'edit' => 'categories.edit',
            'update' => 'categories.update',
            'destroy' => 'categories.destroy',
            'show' => 'categories.show',
        ]);

        // 🔄 Return Lending
        Route::post('lendings/{lending}/return', [App\Http\Controllers\LendingController::class, 'returnBook'])
            ->name('lendings.return');

        // 📦 Lending
        Route::resource('lendings', App\Http\Controllers\LendingController::class)->names([
            'index' => 'lendings.index',
            'create' => 'lendings.create',
            'store' => 'lendings.store',
            'edit' => 'lendings.edit',
            'update' => 'lendings.update',
            'destroy' => 'lendings.destroy',
            'show' => 'lendings.show',
        ]);
    });

});









});
