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
    CategoryController,
    ResultController,
    BillController,
    StudentBillController,
    PaymentController,
    PocketTransactionController,
    BudgetController,
    CounselingIntakeFormController,
    CounselingSessionReportController,
    GroupCounselingSessionReportController,
    IndividualSessionReportController,
    ClassroomGuidanceController,
    LearningProfileController,
    InterestInventoryController,
    AptitudeTestController,
    AptitudeQuestionController,
    
    
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

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

    Route::get('/marks/subjects-by-department', [\App\Http\Controllers\MarkController::class, 'getSubjectsByDepartment'])
    ->name('marks.subjects.by.department');

    Route::get('/results/classes-by-department', [App\Http\Controllers\StudentResultController::class, 'getClassesByDepartment'])
    ->name('results.classes.by.department');


    Route::get('results/export/pdf', [StudentResultController::class, 'exportPdf'])->name('results.export.pdf');
      Route::get('results/show/{student}', [StudentResultController::class, 'show'])->name('results.show');




    Route::prefix('subjects')->middleware(['auth', 'verified'])->group(function () {
    Route::get('{subject}/assign-students', [SubjectController::class, 'assignStudents'])->name('subjects.assign_students');
    Route::put('{subject}/update-assigned-students', [SubjectController::class, 'updateAssignedStudents'])->name('subjects.updateAssignedStudents');
});


Route::get('/results/terminal-report', [StudentResultController::class, 'terminalReport'])
    ->name('results.terminal_report')
    ->middleware(['web', 'auth', 'verified', 'permission:view results']);






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
    // Main results page
    Route::get('/', [StudentResultController::class, 'index'])->name('index');

    // Class results summary
    Route::get('/class', [StudentResultController::class, 'classResults'])->name('class');

    // Export filter form
    Route::get('/export', [StudentResultController::class, 'showExportForm'])->name('export.form');

    // Export PDF after submitting filters (POST)
    Route::post('/export/pdf', [StudentResultController::class, 'exportResultsPdf'])->name('export.pdf');

    // Optional: Export Excel
    Route::get('/export/excel', [StudentResultController::class, 'exportExcel'])->name('export.excel');

    // Show individual student result
    Route::get('/{student}', [StudentResultController::class, 'show'])->name('show');
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


   


Route::prefix('finance')->name('finance.')->middleware(['auth', 'verified'])->group(function () {

    // 🧾 Bills CRUD
    Route::resource('bills', BillController::class)->names([
        'index'   => 'bills.index',
        'create'  => 'bills.create',
        'store'   => 'bills.store',
        'show'    => 'bills.show',
        'edit'    => 'bills.edit',
        'update'  => 'bills.update',
        'destroy' => 'bills.destroy',
    ]);

    // 👨‍🎓 Student Bills (link students to bills)
    Route::resource('student-bills', StudentBillController::class)->names([
        'index'   => 'student_bills.index',
        'create'  => 'student_bills.create',
        'store'   => 'student_bills.store',
        'show'    => 'student_bills.show',
        'edit'    => 'student_bills.edit',
        'update'  => 'student_bills.update',
        'destroy' => 'student_bills.destroy',
    ]);

    // 💵 Payments Module
    Route::prefix('payments')->name('payments.')->group(function () {

        // Step 1: Payment selection page (filter by Session → Class → Bill)
        Route::get('/create', [PaymentController::class, 'create'])
            ->name('create')
            ->middleware('permission:record payments');

        // Step 2: AJAX: Get students for selected session/class
        Route::get('/students', [PaymentController::class, 'getStudents'])
            ->name('students')
            ->middleware('permission:record payments');

        // Step 3: AJAX: Get student bills for selected student
        Route::get('/student-bills', [PaymentController::class, 'getStudentBills'])
            ->name('student-bills')
            ->middleware('permission:record payments');

        // Step 4: Individual payment form (click "Pay" on a student)
        Route::get('/{studentBill}/create', [PaymentController::class, 'createIndividual'])
            ->name('create.individual')
            ->middleware('permission:record payments');

        // Step 5: Store individual payment
        Route::post('/store-individual', [PaymentController::class, 'storeIndividual'])
            ->name('store.individual')
            ->middleware('permission:record payments');

        // Step 6: List all payments
        Route::get('/', [PaymentController::class, 'index'])
            ->name('index')
            ->middleware('permission:view payments');

        // Step 7: Payment receipt
        Route::get('/{id}/receipt', [PaymentController::class, 'receipt'])
            ->name('receipt')
            ->middleware('permission:view payments');
    });

    // 💰 Pocket Money Transactions
    Route::prefix('pocket')->name('pocket.')->group(function () {
        Route::resource('transactions', PocketTransactionController::class)->names([
            'index'   => 'index',
            'create'  => 'create',
            'store'   => 'store',
            'show'    => 'show',
            'edit'    => 'edit',
            'update'  => 'update',
            'destroy' => 'destroy',
        ]);

        // AJAX: Get students by class
        Route::get('/students-by-class', [PocketTransactionController::class, 'getStudentsByClass'])
            ->name('students-by-class');

        // AJAX: Get last balance
        Route::get('/last-balance', [PocketTransactionController::class, 'getLastBalance'])
            ->name('last-balance');
    });
});



   Route::prefix('finance/budgets')->name('finance.budgets.')->middleware('auth')->group(function () {

    // List all budgets
    Route::get('/', [BudgetController::class, 'index'])->name('index');

    // Create budget
    Route::get('/create', [BudgetController::class, 'create'])->name('create');
    Route::post('/', [BudgetController::class, 'store'])->name('store');

    // Pending approvals
    Route::get('/pending', [BudgetController::class, 'pending'])->name('pending');

    // Summary page (must be **before {budget}**)
    Route::get('/summary', [BudgetController::class, 'summary'])->name('summary');

    // Show budget details
    Route::get('/{budget}', [BudgetController::class, 'show'])->name('show');

    // Approve form and action
    Route::get('/{budget}/approve', [BudgetController::class, 'approveForm'])->name('approve.form');
    Route::post('/{budget}/approve', [BudgetController::class, 'approve'])->name('approve');

    // AJAX approve/reject item
    Route::post('/{budget}/item/approve', [BudgetController::class, 'approveItem'])->name('approve.item');
});











    
    //Staff Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::get('/create', [AttendanceController::class, 'create'])->name('create');
    Route::post('/', [AttendanceController::class, 'store'])->name('store');
    Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
    Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
    Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
    
    Route::get('/bulk', [AttendanceController::class, 'bulkCreate'])->name('bulk.create');
    Route::post('/bulk/store', [AttendanceController::class, 'bulkStore'])->name('bulk.store'); // ← fixed
    
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


// Intake Forms
Route::prefix('counseling/intake')->group(function () {
    Route::get('/', [CounselingIntakeFormController::class, 'index'])->name('counseling.intake.index');
    Route::get('/create', [CounselingIntakeFormController::class, 'create'])->name('counseling.intake.create');
    Route::post('/store', [CounselingIntakeFormController::class, 'store'])->name('counseling.intake.store');
    Route::get('/{form}/edit', [CounselingIntakeFormController::class, 'edit'])->name('counseling.intake.edit');
    Route::put('/{form}', [CounselingIntakeFormController::class, 'update'])->name('counseling.intake.update');

    // 🗑️ Delete route
    Route::delete('/{form}', [CounselingIntakeFormController::class, 'destroy'])->name('counseling.intake.destroy');

    Route::get('/{form}', [CounselingIntakeFormController::class, 'show'])->name('counseling.intake.show');

    Route::delete('/counseling/intake/{form}', [CounselingIntakeFormController::class, 'destroy'])
     ->name('counseling.intake.destroy')
     ->middleware(['auth', 'verified']);

});


Route::prefix('counseling/individual')->name('counseling.individual.')->middleware(['auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\IndividualSessionReportController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\IndividualSessionReportController::class, 'create'])->name('create');
    Route::post('/store', [\App\Http\Controllers\IndividualSessionReportController::class, 'store'])->name('store');
    Route::get('/{individualSessionReport}', [\App\Http\Controllers\IndividualSessionReportController::class, 'show'])->name('show');
    Route::get('/{individualSessionReport}/edit', [\App\Http\Controllers\IndividualSessionReportController::class, 'edit'])->name('edit');
    Route::put('/{individualSessionReport}', [\App\Http\Controllers\IndividualSessionReportController::class, 'update'])->name('update');
    Route::delete('/{individualSessionReport}', [\App\Http\Controllers\IndividualSessionReportController::class, 'destroy'])->name('destroy');
});


Route::middleware(['auth'])->group(function () {
    Route::resource('classroom-guidances', ClassroomGuidanceController::class)->middleware('auth');

});

Route::middleware(['auth'])->group(function () {
    Route::resource('interest-inventories', InterestInventoryController::class);
    // optional PDF export route
    Route::get('interest-inventories/{interestInventory}/export', [InterestInventoryController::class, 'exportPdf'])
        ->name('interest-inventories.export');
});



Route::prefix('counseling/psychometric/aptitude')->middleware(['auth'])->group(function() {
    Route::get('/', [AptitudeTestController::class, 'index'])->name('aptitude.index');
    Route::get('/create', [AptitudeTestController::class, 'create'])->name('aptitude.create');
    Route::post('/store', [AptitudeTestController::class, 'store'])->name('aptitude.store');
    Route::get('/{aptitudeAttempt}', [AptitudeTestController::class, 'show'])->name('aptitude.show');
    Route::get('/{aptitudeAttempt}/pdf', [AptitudeTestController::class, 'pdf'])->name('aptitude.pdf');
});


// Questions CRUD
Route::prefix('aptitude/questions')->name('aptitude.questions.')->group(function() {
    Route::get('/', [AptitudeQuestionController::class, 'index'])->name('index');
    Route::get('/create', [AptitudeQuestionController::class, 'create'])->name('create');
    Route::post('/store', [AptitudeQuestionController::class, 'store'])->name('store');
    Route::get('/{aptitudeQuestion}/edit', [AptitudeQuestionController::class, 'edit'])->name('edit');
    Route::put('/{aptitudeQuestion}/update', [AptitudeQuestionController::class, 'update'])->name('update');
    Route::delete('/{aptitudeQuestion}/delete', [AptitudeQuestionController::class, 'destroy'])->name('destroy');
});








Route::resource('counseling/group', GroupCounselingSessionReportController::class)
     ->names([
         'index' => 'counseling.group.index',
         'create' => 'counseling.group.create',
         'store' => 'counseling.group.store',
         'show' => 'counseling.group.show',
         'edit' => 'counseling.group.edit',
         'update' => 'counseling.group.update',
         'destroy' => 'counseling.group.destroy',
     ])
     ->middleware('auth');

});
