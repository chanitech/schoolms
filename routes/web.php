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
    InvoiceController,
    PromotionController,
    AIAnalysisController,
    AIAssistantController,
    ExamQuestionController,
    TopicCoverageController,
    TimetableController,
    GuardianLoginController,
    NotificationController,
    DocumentController,
};
use App\Http\Controllers\Staff\LoanApplicationController;
use App\Http\Controllers\Staff\BankStatementController as StaffBankStatementController;
use App\Http\Controllers\Treasurer\LoanApprovalController;
use App\Http\Controllers\Treasurer\LoanCategoryController;
use App\Http\Controllers\Treasurer\BankStatementController;
use App\Http\Controllers\Treasurer\ProcurementRequestController;
use App\Http\Controllers\Treasurer\StockRequestController;
use App\Http\Controllers\Treasurer\JobDescriptionController;
use App\Http\Controllers\Treasurer\TaskLogController;
use App\Http\Controllers\Treasurer\TaskJustificationController;
use App\Http\Controllers\Treasurer\FinanceDashboardController;
use App\Http\Controllers\SuperAdmin\SchoolController as SuperSchoolController;
use App\Http\Controllers\SuperAdmin\AccountController as SuperAccountController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Subscription expired — shown when school's subscription is inactive
Route::get('/subscription/expired', function () {
    return view('subscription.expired');
})->name('subscription.expired');

// Authentication routes
require __DIR__.'/auth.php';

// ==================== GUARDIAN AUTH (phone-based) ====================
// Must live outside the ['auth','verified'] group below — otherwise the
// `auth` middleware runs before `guest` and blocks unauthenticated visitors
// from ever reaching the login form.
Route::prefix('guardian')->name('guardian.')->group(function () {
    Route::get('/login',  [GuardianLoginController::class, 'showLogin'])->name('login')->middleware('guest');
    Route::post('/login', [GuardianLoginController::class, 'login'])->name('login.post')->middleware('guest');
    Route::post('/logout', [GuardianLoginController::class, 'logout'])->name('logout');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {

    // ==================== NOTIFICATIONS ====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',              [NotificationController::class, 'index'])->name('index');
        Route::get('/recent',        [NotificationController::class, 'recent'])->name('recent');
        Route::get('/count',         [NotificationController::class, 'count'])->name('count');
        Route::post('/mark-all-read',[NotificationController::class, 'markAllRead'])->name('mark-all-read');
        Route::post('/{id}/read',    [NotificationController::class, 'markRead'])->name('read');
        Route::delete('/{id}',       [NotificationController::class, 'destroy'])->name('destroy');
    });

    // ==================== DASHBOARD & PROFILE ====================
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // ==================== SYSTEM SETTINGS (Admin only) ====================
    Route::middleware(['role:Admin'])->prefix('settings')->name('settings.')->group(function () {
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
        Route::get('school-info', [SchoolInfoController::class, 'index'])->name('school.info.index');
        Route::post('school-info', [SchoolInfoController::class, 'update'])->name('school.info.update');
    });

    // ==================== STAFF LOAN ROUTES ====================
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::resource('loans', LoanApplicationController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('loans/{loan}/statement', [LoanApplicationController::class, 'statement'])->name('loans.statement');
        Route::get('bank-statements', [StaffBankStatementController::class, 'index'])->name('bank-statements.index');
        Route::get('loans/{loan}/download-statement', [LoanApplicationController::class, 'downloadStatement'])->name('loans.download-statement');
    });

    // ==================== TREASURER ROUTES ====================
    Route::middleware(['auth', 'role:chief-accountant|accountant|treasurer|Admin'])->prefix('treasurer')->name('treasurer.')->group(function () {
        Route::get('loans/pending', [LoanApprovalController::class, 'pending'])->name('loans.pending');
        Route::post('loans/{loan}/approve', [LoanApprovalController::class, 'approve'])->name('loans.approve');
        Route::post('loans/{loan}/reject', [LoanApprovalController::class, 'reject'])->name('loans.reject');
        Route::get('loans/{loan}/disburse', [LoanApprovalController::class, 'disburseForm'])->name('loans.disburse.form');
        Route::post('loans/{loan}/disburse', [LoanApprovalController::class, 'disburse'])->name('loans.disburse');
        Route::get('loans', [LoanApprovalController::class, 'index'])->name('loans.index');
        Route::get('loans/active', [LoanApprovalController::class, 'activeLoans'])->name('loans.active');
        Route::get('loans/{loan}/statement', [LoanApprovalController::class, 'treasurerStatement'])->name('loans.statement');
        Route::post('loans/{loan}/repayments/{repayment}/pay', [LoanApprovalController::class, 'recordRepayment'])->name('loans.repayments.pay');
        Route::resource('loan-categories', LoanCategoryController::class)->middleware('role:treasurer|Admin');
        Route::resource('bank-statements', BankStatementController::class)->except(['show', 'edit', 'update']);
    });

    // ==================== FINANCE OFFICE ====================
    // Payment reconciliation — Class Accountants verify/flag, Treasurer reviews
    Route::prefix('finance/payments')->name('finance.payments.')->group(function () {
        Route::get('/review', [PaymentController::class, 'pendingReview'])->name('review');
        Route::post('/{payment}/verify', [PaymentController::class, 'verify'])->name('verify');
        Route::post('/{payment}/flag', [PaymentController::class, 'flag'])->name('flag');
    });

    // Procurement/Expense — Storekeeper/Procurement Officer request, Treasurer approves, Cashier disburses
    Route::prefix('treasurer/procurement')->name('treasurer.procurement.')->group(function () {
        Route::get('/', [ProcurementRequestController::class, 'index'])->name('index');
        Route::get('/create', [ProcurementRequestController::class, 'create'])->name('create');
        Route::post('/', [ProcurementRequestController::class, 'store'])->name('store');
        Route::get('/pending', [ProcurementRequestController::class, 'pending'])->name('pending');
        Route::post('/{procurementRequest}/approve', [ProcurementRequestController::class, 'approve'])->name('approve');
        Route::post('/{procurementRequest}/reject', [ProcurementRequestController::class, 'reject'])->name('reject');
        Route::post('/{procurementRequest}/disburse', [ProcurementRequestController::class, 'disburse'])->name('disburse');
    });

    // Stock Requests — Storekeeper flags a need to the Procurement Officer,
    // who converts an approved one into a procurement request above.
    Route::prefix('treasurer/stock-requests')->name('treasurer.stock-requests.')->group(function () {
        Route::get('/', [StockRequestController::class, 'index'])->name('index');
        Route::get('/create', [StockRequestController::class, 'create'])->name('create');
        Route::post('/', [StockRequestController::class, 'store'])->name('store');
        Route::post('/{stockRequest}/approve', [StockRequestController::class, 'approve'])->name('approve');
        Route::post('/{stockRequest}/reject', [StockRequestController::class, 'reject'])->name('reject');
    });

    // Job Descriptions — Treasurer-managed, one per Finance Office role
    Route::prefix('treasurer/job-descriptions')->name('treasurer.job-descriptions.')->group(function () {
        Route::get('/', [JobDescriptionController::class, 'index'])->name('index');
        Route::put('/{roleName}', [JobDescriptionController::class, 'update'])->name('update');
    });

    // Task/Performance tracking — assigned by Treasurer, tracked by assignee
    Route::prefix('treasurer/tasks')->name('treasurer.tasks.')->group(function () {
        Route::get('/', [TaskLogController::class, 'index'])->name('index');
        Route::get('/create', [TaskLogController::class, 'create'])->name('create');
        Route::post('/', [TaskLogController::class, 'store'])->name('store');
        Route::post('/{taskLog}/progress', [TaskLogController::class, 'updateProgress'])->name('progress');
        Route::post('/{taskLog}/submit', [TaskLogController::class, 'submitForReview'])->name('submit');
        Route::post('/{taskLog}/approve', [TaskLogController::class, 'approve'])->name('approve');
        Route::post('/{taskLog}/toggle-exceeds', [TaskLogController::class, 'toggleExceeds'])->name('toggle-exceeds');
        Route::post('/{taskLog}/justification', [TaskJustificationController::class, 'store'])->name('justification.store');
    });
    Route::post('/treasurer/justifications/{justification}/review', [TaskJustificationController::class, 'review'])->name('treasurer.justifications.review');

    // Treasurer oversight dashboard
    Route::get('/treasurer/dashboard', [FinanceDashboardController::class, 'index'])->name('treasurer.dashboard');
    Route::get('/treasurer/my-dashboard', [FinanceDashboardController::class, 'myDashboard'])->name('treasurer.my-dashboard');

    // ==================== STUDENT ROUTES ====================
    Route::get('/students/download-template', [StudentController::class, 'downloadTemplate'])->name('students.download-template');
    Route::post('/students/import', [StudentController::class, 'importExcel'])->name('students.import');
    Route::get('/students/get-rooms', [StudentController::class, 'getRooms'])->name('students.get-rooms');
    Route::get('/students/get-beds', [StudentController::class, 'getBeds'])->name('students.get-beds');
    Route::resource('students', StudentController::class);

    // ==================== DORMITORY MANAGEMENT ====================
    // IMPORTANT: Static routes (no parameters) must be declared BEFORE wildcard routes
    Route::prefix('dormitories')->name('dormitories.')->group(function () {
        // Dashboard & Reports (static)
        Route::get('/dashboard', [DormitoryController::class, 'dashboard'])->name('dashboard');
        Route::get('/reports', [DormitoryController::class, 'reports'])->name('reports');

        // Allocations (static – must come before wildcard routes)
        Route::get('/allocations', [DormitoryController::class, 'allocations'])->name('allocations');
        Route::get('/allocations/create', [DormitoryController::class, 'allocateBed'])->name('allocations.create');
        Route::post('/allocations', [DormitoryController::class, 'storeAllocation'])->name('allocations.store');
        Route::delete('/allocations/{allocation}', [DormitoryController::class, 'deallocateBed'])->name('allocations.delete');

        // AJAX routes (static)
        Route::get('/get-rooms', [DormitoryController::class, 'getRooms'])->name('get-rooms');
        Route::get('/get-beds', [DormitoryController::class, 'getBeds'])->name('get-beds');

        // Dormitory CRUD (some are static, some have parameters)
        Route::get('/', [DormitoryController::class, 'index'])->name('index');
        Route::get('/create', [DormitoryController::class, 'create'])->name('create');
        Route::post('/', [DormitoryController::class, 'store'])->name('store');
        Route::get('/{dormitory}/edit', [DormitoryController::class, 'edit'])->name('edit');
        Route::put('/{dormitory}', [DormitoryController::class, 'update'])->name('update');
        Route::delete('/{dormitory}', [DormitoryController::class, 'destroy'])->name('destroy');
        Route::get('/{dormitory}', [DormitoryController::class, 'show'])->name('show');

        // Room Management (wildcard – after static routes)
        Route::get('/{dormitoryId}/rooms', [DormitoryController::class, 'rooms'])->name('rooms');
        Route::get('/{dormitoryId}/rooms/create', [DormitoryController::class, 'createRoom'])->name('rooms.create');
        Route::post('/rooms', [DormitoryController::class, 'storeRoom'])->name('rooms.store');
        Route::get('/rooms/{room}/edit', [DormitoryController::class, 'editRoom'])->name('rooms.edit');
        Route::put('/rooms/{room}', [DormitoryController::class, 'updateRoom'])->name('rooms.update');
        Route::delete('/rooms/{room}', [DormitoryController::class, 'deleteRoom'])->name('rooms.delete');

        // Bed Management (wildcard)
        Route::get('/rooms/{roomId}/beds', [DormitoryController::class, 'beds'])->name('beds');
        Route::get('/rooms/{roomId}/beds/create', [DormitoryController::class, 'createBed'])->name('beds.create');
        Route::post('/beds', [DormitoryController::class, 'storeBed'])->name('beds.store');
        Route::get('/beds/{bed}/edit', [DormitoryController::class, 'editBed'])->name('beds.edit');
        Route::put('/beds/{bed}', [DormitoryController::class, 'updateBed'])->name('beds.update');
        Route::delete('/beds/{bed}', [DormitoryController::class, 'deleteBed'])->name('beds.delete');

        // Bulk bed creation (static after wildcard? Actually it has {roomId} but it's a specific pattern – must come before generic wildcard that catches all)
        // Since we already have '/rooms/{roomId}/beds', placing the bulk routes below is fine because they are more specific.
        Route::get('/rooms/{roomId}/beds/bulk', [DormitoryController::class, 'bulkCreateBedsForm'])->name('beds.bulk.form');
        Route::post('/rooms/{roomId}/beds/bulk', [DormitoryController::class, 'bulkStoreBeds'])->name('beds.bulk.store');
    });

    // ==================== OTHER RESOURCE ROUTES ====================
    Route::resources([
        'guardians' => GuardianController::class,
        'enrollments' => EnrollmentController::class,
        'classes' => SchoolClassController::class,
        'sessions' => AcademicSessionController::class,
        'staff' => StaffController::class,
        'subjects' => SubjectController::class,
        'exams' => ExamController::class,
        'grades' => GradeController::class,
        'divisions' => DivisionController::class,
        'departments' => DepartmentController::class,
    ]);

    // ==================== MARKS & RESULTS AJAX ====================
    Route::get('/marks/subjects-by-department', [MarkController::class, 'getSubjectsByDepartment'])->name('marks.subjects.by.department');
    Route::get('/exams/by-session', [ExamController::class, 'getExamsBySession'])->name('exams.by.session');
    Route::post('/exams/{exam}/review',        [ExamController::class, 'review'])->name('exams.review');
    Route::post('/exams/{exam}/publish',       [ExamController::class, 'publish'])->name('exams.publish');
    Route::post('/exams/{exam}/unpublish',     [ExamController::class, 'unpublish'])->name('exams.unpublish');
    Route::post('/exams/{exam}/reject-review', [ExamController::class, 'rejectReview'])->name('exams.reject-review');
    Route::get('/marks/exams-by-session', [MarkController::class, 'getExamsBySession'])->name('marks.exams.by.session');
    Route::post('/marks/import', [StudentResultController::class, 'importExcel'])->name('marks.import');
    Route::get('/marks/template', [StudentResultController::class, 'downloadTemplate'])->name('marks.download-template');
    Route::get('/marks/download-template/filtered', [StudentResultController::class, 'downloadFilteredTemplate'])->name('marks.download-filtered-template');
    Route::get('/marks/download-question-template', [MarkController::class, 'downloadQuestionMarksTemplate'])->name('marks.download.question.template');
    Route::post('/marks/import-question-marks', [MarkController::class, 'importQuestionMarks'])->name('marks.import.question.marks');
    Route::get('/results/classes-by-department', [StudentResultController::class, 'getClassesByDepartment'])->name('results.classes.by.department');

    // ==================== SUBJECTS (Assign Students) ====================
    Route::prefix('subjects')->group(function () {
        Route::get('{subject}/assign-students', [SubjectController::class, 'assignStudents'])->name('subjects.assign_students');
        Route::put('{subject}/update-assigned-students', [SubjectController::class, 'updateAssignedStudents'])->name('subjects.updateAssignedStudents');
    });

    // ==================== PROMOTION ====================
    Route::get('/promotion/students', [PromotionController::class, 'studentsJson']);
    Route::get('/promotion', [PromotionController::class, 'index'])->name('promotion.index');
    Route::post('/promotion/class', [PromotionController::class, 'promoteClass'])->name('promotion.class');
    Route::post('/promotion/student/{id}', [PromotionController::class, 'promoteSingle'])->name('promotion.student');

    // ==================== TERMINAL REPORT ====================
    Route::get('/results/terminal-report', [StudentResultController::class, 'terminalReport'])
        ->name('results.terminal_report')
        ->middleware('permission:view results');

    // ==================== MARKS CRUD ====================
    Route::prefix('marks')->name('marks.')->group(function () {
        Route::get('students', [MarkController::class, 'getStudents'])->name('students');
        Route::get('students-with-questions', [MarkController::class, 'getStudentsWithQuestions'])->name('students.with.questions');
        Route::get('/', [MarkController::class, 'index'])->name('index');
        Route::get('/create', [MarkController::class, 'create'])->name('create');
        Route::post('/', [MarkController::class, 'store'])->name('store');
        Route::post('/store-by-questions', [MarkController::class, 'storeByQuestions'])->name('store.by.questions');
        Route::get('/question-evaluation', [MarkController::class, 'questionEvaluation'])->name('question.evaluation');
        Route::get('/{mark}/edit', [MarkController::class, 'edit'])->name('edit');
        Route::put('/{mark}', [MarkController::class, 'update'])->name('update');
        Route::delete('/{mark}', [MarkController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('exam-questions')->name('exam.questions.')->group(function () {
        Route::get('/manage', [ExamQuestionController::class, 'manage'])->name('manage');
        Route::get('/get', [ExamQuestionController::class, 'getQuestions'])->name('get');
        Route::post('/save', [ExamQuestionController::class, 'save'])->name('save');
    });

    // ==================== TOPIC COVERAGE ====================
    Route::prefix('topic-coverage')->name('topic-coverage.')->group(function () {
        Route::get('/',              [TopicCoverageController::class, 'index'])->name('index');
        Route::get('/create',        [TopicCoverageController::class, 'create'])->name('create');
        Route::post('/',             [TopicCoverageController::class, 'store'])->name('store');
        Route::get('/evaluation',    [TopicCoverageController::class, 'evaluation'])->name('evaluation');
        Route::get('/{lessonPlan}',  [TopicCoverageController::class, 'show'])->name('show');
        Route::put('/{lessonPlan}',  [TopicCoverageController::class, 'update'])->name('update');
        Route::delete('/{lessonPlan}', [TopicCoverageController::class, 'destroy'])->name('destroy');
        // Topic AJAX
        Route::post('/{lessonPlan}/topics', [TopicCoverageController::class, 'storeTopic'])->name('topics.store');
    });
    Route::prefix('lesson-topics')->name('lesson-topics.')->group(function () {
        Route::put('/{topic}',            [TopicCoverageController::class, 'updateTopic'])->name('update');
        Route::delete('/{topic}',         [TopicCoverageController::class, 'destroyTopic'])->name('destroy');
        Route::post('/{topic}/subtopics', [TopicCoverageController::class, 'storeSubtopic'])->name('subtopics.store');
    });
    Route::prefix('lesson-subtopics')->name('lesson-subtopics.')->group(function () {
        Route::put('/{subtopic}',                    [TopicCoverageController::class, 'updateSubtopic'])->name('update');
        Route::delete('/{subtopic}',                 [TopicCoverageController::class, 'destroySubtopic'])->name('destroy');
        Route::patch('/{subtopic}/toggle',           [TopicCoverageController::class, 'toggleSubtopic'])->name('toggle');
        Route::post('/{subtopic}/generate-plan',     [TopicCoverageController::class, 'generateSubtopicPlan'])->name('generate-plan');
        Route::get('/{subtopic}/plan',               [TopicCoverageController::class, 'getSubtopicPlan'])->name('plan');
    });

    // ==================== TIMETABLE ====================
    Route::prefix('timetables')->name('timetables.')->group(function () {
        Route::get('/today-schedule',      [TimetableController::class, 'todaySchedule'])->name('today-schedule');
        Route::get('/subjects-by-classes', [TimetableController::class, 'subjectsByClasses'])->name('subjects-by-classes');
        Route::get('/my-sessions',         [TimetableController::class, 'mySessionsDashboard'])->name('my-sessions');
        Route::post('/entries/{entry}/log',         [TimetableController::class, 'logSession'])->name('log-session');
        Route::post('/entries/{entry}/log-ajax',    [TimetableController::class, 'logSessionAjax'])->name('log-session-ajax');
        Route::get('/entries/{entry}/topics',       [TimetableController::class, 'topicsForEntry'])->name('entry-topics');
        Route::get('/',                    [TimetableController::class, 'index'])->name('index');
        Route::get('/create',              [TimetableController::class, 'create'])->name('create');
        Route::post('/',                   [TimetableController::class, 'store'])->name('store');
        Route::get('/{timetable}',         [TimetableController::class, 'show'])->name('show');
        Route::get('/{timetable}/edit',    [TimetableController::class, 'edit'])->name('edit');
        Route::put('/{timetable}',         [TimetableController::class, 'update'])->name('update');
        Route::delete('/{timetable}',      [TimetableController::class, 'destroy'])->name('destroy');
        Route::post('/{timetable}/regenerate', [TimetableController::class, 'regenerate'])->name('regenerate');
        Route::post('/{timetable}/submit',     [TimetableController::class, 'submitForReview'])->name('submit');
        Route::post('/{timetable}/review',     [TimetableController::class, 'review'])->name('review');
        Route::post('/{timetable}/publish',    [TimetableController::class, 'publish'])->name('publish');
        Route::post('/{timetable}/unpublish',  [TimetableController::class, 'unpublish'])->name('unpublish');
    });

    // ==================== RESULTS GROUP ====================
    Route::prefix('results')->name('results.')->group(function () {
        Route::get('/', [StudentResultController::class, 'index'])->name('index');
        Route::get('/class', [StudentResultController::class, 'classResults'])->name('class');
        Route::get('/export', [StudentResultController::class, 'showExportForm'])->name('export.form');
        Route::get('/export/pdf', [StudentResultController::class, 'exportPdf'])->name('export.pdf');
        Route::post('/export/pdf', [StudentResultController::class, 'exportResultsPdf'])->name('export.pdf.submit');
        Route::get('/export/excel', [StudentResultController::class, 'exportExcel'])->name('export.excel');
        Route::get('/{student}', [StudentResultController::class, 'show'])->name('show');
    });

    // ==================== JOBCARDS ====================
    Route::prefix('jobcards')->name('jobcards.')->group(function () {
        Route::get('/', [JobCardController::class, 'index'])->name('index');
        Route::get('/create', [JobCardController::class, 'create'])->name('create');
        Route::post('/', [JobCardController::class, 'store'])->name('store');
        Route::get('/{jobcard}/edit', [JobCardController::class, 'edit'])->name('edit');
        Route::put('/{jobcard}', [JobCardController::class, 'update'])->name('update');
        Route::delete('/{jobcard}', [JobCardController::class, 'destroy'])->name('destroy');
        Route::get('/my', [JobCardController::class, 'myJobCards'])->name('my');
        Route::patch('/{jobcard}/update-status', [JobCardController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('/{jobcard}/rate-task', [JobCardController::class, 'rateTask'])->name('rateTask');
    });

    // ==================== FINANCE MODULE ====================
    Route::prefix('finance')->name('finance.')->group(function () {
        // Bills
        Route::resource('bills', BillController::class);

        // Student Bills
        Route::resource('student-bills', StudentBillController::class);
        Route::get('student_bills', [StudentBillController::class, 'index'])->name('student_bills.index');

        // Payments
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/create', [PaymentController::class, 'create'])->name('create')->middleware('permission:record payments');
            Route::get('/students', [PaymentController::class, 'getStudents'])->name('students')->middleware('permission:record payments');
            Route::get('/student-bills', [PaymentController::class, 'getStudentBills'])->name('student-bills')->middleware('permission:record payments');
            Route::get('/{studentBill}/create', [PaymentController::class, 'createIndividual'])->name('create.individual')->middleware('permission:record payments');
            Route::post('/store-individual', [PaymentController::class, 'storeIndividual'])->name('store.individual')->middleware('permission:record payments');
            Route::get('/', [PaymentController::class, 'index'])->name('index')->middleware('permission:view payments');
            Route::get('/{id}/receipt', [PaymentController::class, 'receipt'])->name('receipt')->middleware('permission:view payments');
        });

        // Pocket Money
        Route::prefix('pocket')->name('pocket.')->group(function () {
            Route::resource('transactions', PocketTransactionController::class);
            Route::get('/students-by-class', [PocketTransactionController::class, 'getStudentsByClass'])->name('students-by-class');
            Route::get('/last-balance', [PocketTransactionController::class, 'getLastBalance'])->name('last-balance');
        });

        // Budgets
        Route::prefix('budgets')->name('budgets.')->group(function () {
            Route::get('/', [BudgetController::class, 'index'])->name('index');
            Route::get('/create', [BudgetController::class, 'create'])->name('create');
            Route::post('/', [BudgetController::class, 'store'])->name('store');
            Route::get('/pending', [BudgetController::class, 'pending'])->name('pending');
            Route::get('/summary', [BudgetController::class, 'summary'])->name('summary');
            Route::get('/hod', [BudgetController::class, 'hodBudgets'])->name('hod');
            Route::get('/{budget}/edit', [BudgetController::class, 'edit'])->name('edit');
            Route::put('/{budget}', [BudgetController::class, 'update'])->name('update');
            Route::post('/items/{item}/withdraw', [BudgetController::class, 'withdrawItem'])->name('withdraw');
            Route::get('/{budget}', [BudgetController::class, 'show'])->name('show');
            Route::get('/{budget}/approve', [BudgetController::class, 'approveForm'])->name('approve.form');
            Route::post('/{budget}/approve', [BudgetController::class, 'approve'])->name('approve');
            Route::post('/{budget}/item/approve', [BudgetController::class, 'approveItem'])->name('approve.item');
            Route::delete('/{budget}', [BudgetController::class, 'destroy'])->name('destroy');
        });

        // Invoices
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('index');
            Route::get('/do', [InvoiceController::class, 'doDashboard'])->name('do');
            Route::post('/{invoice}/approve', [InvoiceController::class, 'approve'])->name('approve');
            Route::get('/finance', [InvoiceController::class, 'financeDashboard'])->name('finance');
            Route::post('/{invoice}/pay', [InvoiceController::class, 'pay'])->name('pay');
            Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        });
    });

    // ==================== GUARDIAN PORTAL ====================
    Route::middleware(['auth', 'role:guardian'])->prefix('guardian')->name('guardian.')->group(function () {
        Route::get('/dashboard', [GuardianController::class, 'dashboard'])->name('dashboard');
        Route::get('/fees', [GuardianController::class, 'fees'])->name('fees');
        Route::get('/result/{student}', [GuardianController::class, 'showResult'])->name('result.show');
        // NEW: receipt route
    Route::get('/payment/{payment}/receipt', [GuardianController::class, 'paymentReceipt'])->name('payment.receipt');
    });

    // ==================== STAFF ATTENDANCE ====================
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

    // ==================== LEAVES ====================
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/create', [LeaveController::class, 'create'])->name('create');
        Route::post('/', [LeaveController::class, 'store'])->name('store');
        Route::get('/{leave}/edit', [LeaveController::class, 'edit'])->name('edit');
        Route::put('/{leave}', [LeaveController::class, 'update'])->name('update');
        Route::delete('/{leave}', [LeaveController::class, 'destroy'])->name('destroy');
        Route::get('/received', [LeaveController::class, 'received'])->name('received');
        Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
        Route::post('/{leave}/reject', [LeaveController::class, 'reject'])->name('reject');
        Route::get('/received/export/excel', [LeaveController::class, 'exportReceivedExcel'])->name('received.export.excel');
        Route::get('/received/export/pdf', [LeaveController::class, 'exportReceivedPdf'])->name('received.export.pdf');
    });

    // ==================== EVENTS ====================
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

    // ==================== HR REPORTS ====================
    Route::prefix('hr-reports')->name('hr-reports.')->group(function () {
        Route::get('/', [HRReportController::class, 'index'])->name('index')->middleware('permission:view hr reports');
        Route::get('/staff', [HRReportController::class, 'staffReport'])->name('staff')->middleware('permission:view staff report');
        Route::get('/attendance', [HRReportController::class, 'attendanceReport'])->name('attendance')->middleware('permission:view attendance report');
        Route::get('/leaves', [HRReportController::class, 'leaveReport'])->name('leaves')->middleware('permission:view leave report');
        Route::get('/leaves/export/excel', [HRReportController::class, 'exportLeaveExcel'])->name('leaves.export.excel')->middleware('permission:view leave report');
        Route::get('/leaves/export/pdf', [HRReportController::class, 'exportLeavePDF'])->name('leaves.export.pdf')->middleware('permission:view leave report');
        Route::get('/jobcards', [HRReportController::class, 'jobCardReport'])->name('jobcards');
        Route::get('/evaluation', [HRReportController::class, 'evaluationReport'])->name('evaluation');
        Route::get('/evaluation/export', [HRReportController::class, 'exportEvaluation'])->name('evaluation.export');
        Route::get('/events', [HRReportController::class, 'eventReport'])->name('events')->middleware('permission:view event report');
        Route::get('/summary', [HRReportController::class, 'summaryDashboard'])->name('summary')->middleware('permission:view hr summary dashboard');
    });

    Route::get('/hr-reports/evaluation/export', [HRReportController::class, 'exportEvaluation'])->name('hr.reports.export.evaluation');

    // ==================== LIBRARY MODULE ====================
    Route::prefix('library')->name('library.')->group(function () {
        Route::get('lendings/get-students/{class_id}', [LendingController::class, 'getStudentsByClass'])->name('lendings.getStudentsByClass');
        Route::get('lendings/get-staff/{role}', [LendingController::class, 'getStaffByRole'])->name('lendings.getStaffByRole');
        Route::resource('books', BookController::class);
        Route::resource('categories', CategoryController::class);
        Route::post('lendings/{lending}/return', [LendingController::class, 'returnBook'])->name('lendings.return');
        Route::resource('lendings', LendingController::class);
    });

    // ==================== COUNSELING MODULE ====================
    // Intake Forms
    Route::prefix('counseling/intake')->name('counseling.intake.')->group(function () {
        Route::get('/', [CounselingIntakeFormController::class, 'index'])->name('index');
        Route::get('/create', [CounselingIntakeFormController::class, 'create'])->name('create');
        Route::post('/store', [CounselingIntakeFormController::class, 'store'])->name('store');
        Route::get('/{form}/edit', [CounselingIntakeFormController::class, 'edit'])->name('edit');
        Route::put('/{form}', [CounselingIntakeFormController::class, 'update'])->name('update');
        Route::delete('/{form}', [CounselingIntakeFormController::class, 'destroy'])->name('destroy');
        Route::get('/{form}', [CounselingIntakeFormController::class, 'show'])->name('show');
    });

    // Individual Session Reports
    Route::prefix('counseling/individual')->name('counseling.individual.')->group(function () {
        Route::get('/', [IndividualSessionReportController::class, 'index'])->name('index');
        Route::get('/create', [IndividualSessionReportController::class, 'create'])->name('create');
        Route::post('/store', [IndividualSessionReportController::class, 'store'])->name('store');
        Route::get('/{report}', [IndividualSessionReportController::class, 'show'])->name('show');
        Route::get('/{report}/edit', [IndividualSessionReportController::class, 'edit'])->name('edit');
        Route::put('/{report}', [IndividualSessionReportController::class, 'update'])->name('update');
        Route::delete('/{report}', [IndividualSessionReportController::class, 'destroy'])->name('destroy');
    });

    // Group Counseling Sessions
    Route::resource('counseling/group', GroupCounselingSessionReportController::class)
        ->names([
            'index' => 'counseling.group.index',
            'create' => 'counseling.group.create',
            'store' => 'counseling.group.store',
            'show' => 'counseling.group.show',
            'edit' => 'counseling.group.edit',
            'update' => 'counseling.group.update',
            'destroy' => 'counseling.group.destroy',
        ]);

    // Classroom Guidance
    Route::resource('classroom-guidances', ClassroomGuidanceController::class);

    // Interest Inventories
    Route::resource('interest-inventories', InterestInventoryController::class);
    Route::get('interest-inventories/{inventory}/export', [InterestInventoryController::class, 'exportPdf'])->name('interest-inventories.export');

    // Psychometric/Aptitude Tests
    Route::prefix('counseling/psychometric/aptitude')->name('counseling.psychometric.aptitude.')->group(function() {
        Route::get('/', [AptitudeTestController::class, 'index'])->name('index');
        Route::get('/create', [AptitudeTestController::class, 'create'])->name('create');
        Route::post('/store', [AptitudeTestController::class, 'store'])->name('store');
        Route::get('/{attempt}', [AptitudeTestController::class, 'show'])->name('show');
        Route::get('/{attempt}/pdf', [AptitudeTestController::class, 'pdf'])->name('pdf');
    });

    // Aptitude Questions
    Route::prefix('aptitude/questions')->name('aptitude.questions.')->group(function() {
        Route::get('/', [AptitudeQuestionController::class, 'index'])->name('index');
        Route::get('/create', [AptitudeQuestionController::class, 'create'])->name('create');
        Route::post('/store', [AptitudeQuestionController::class, 'store'])->name('store');
        Route::get('/{question}/edit', [AptitudeQuestionController::class, 'edit'])->name('edit');
        Route::put('/{question}/update', [AptitudeQuestionController::class, 'update'])->name('update');
        Route::delete('/{question}/delete', [AptitudeQuestionController::class, 'destroy'])->name('destroy');
    });


    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/dashboard', [AIAnalysisController::class, 'index'])->name('dashboard');
        Route::post('/analyze-student', [AIAnalysisController::class, 'analyzeStudent'])->name('analyze.student');
        Route::post('/analyze-class', [AIAnalysisController::class, 'analyzeClass'])->name('analyze.class');
        Route::post('/suggest-interventions', [AIAnalysisController::class, 'suggestInterventions'])->name('suggest.interventions');
        Route::post('/finance-insights', [AIAnalysisController::class, 'financeInsights'])->name('finance.insights');
        Route::post('/clear-cache', [AIAnalysisController::class, 'clearCache'])->name('clear.cache');
    });

    Route::prefix('ai-assistant')->name('ai.assistant.')->group(function () {
        Route::get('/', [AIAssistantController::class, 'index'])->name('index');
        Route::post('/send', [AIAssistantController::class, 'sendMessage'])->name('send');
        Route::get('/conversation/{id}', [AIAssistantController::class, 'getConversation'])->name('conversation');
        Route::delete('/conversation/{id}', [AIAssistantController::class, 'deleteConversation'])->name('conversation.delete');
    });

    // ── Daily Reports ─────────────────────────────────────────────────────────
    Route::prefix('daily-reports')->name('daily-reports.')->group(function () {
        Route::get('/',                [\App\Http\Controllers\DailyReportController::class, 'index'])->name('index');
        Route::get('/create',          [\App\Http\Controllers\DailyReportController::class, 'create'])->name('create');
        Route::post('/',               [\App\Http\Controllers\DailyReportController::class, 'store'])->name('store');
        Route::get('/hod',             [\App\Http\Controllers\DailyReportController::class, 'hodIndex'])->name('hod');
        Route::get('/{dailyReport}',   [\App\Http\Controllers\DailyReportController::class, 'show'])->name('show');
        Route::get('/{dailyReport}/edit', [\App\Http\Controllers\DailyReportController::class, 'edit'])->name('edit');
    });

    // ── HOD Dashboard ────────────────────────────────────────────────────────
    Route::get('/hod/dashboard', [\App\Http\Controllers\HODDashboardController::class, 'index'])
        ->name('hod.dashboard');

    // ── Inventory Management ──────────────────────────────────────────────────
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/',                                    [\App\Http\Controllers\InventoryController::class, 'index'])->name('index');

        // Categories
        Route::get('/categories',                          [\App\Http\Controllers\InventoryController::class, 'categories'])->name('categories');
        Route::post('/categories',                         [\App\Http\Controllers\InventoryController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}',               [\App\Http\Controllers\InventoryController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}',            [\App\Http\Controllers\InventoryController::class, 'destroyCategory'])->name('categories.destroy');

        // Items
        Route::get('/items',                               [\App\Http\Controllers\InventoryController::class, 'items'])->name('items');
        Route::get('/items/create',                        [\App\Http\Controllers\InventoryController::class, 'createItem'])->name('items.create');
        Route::post('/items',                              [\App\Http\Controllers\InventoryController::class, 'storeItem'])->name('items.store');
        Route::get('/items/{item}',                        [\App\Http\Controllers\InventoryController::class, 'showItem'])->name('items.show');
        Route::get('/items/{item}/edit',                   [\App\Http\Controllers\InventoryController::class, 'editItem'])->name('items.edit');
        Route::put('/items/{item}',                        [\App\Http\Controllers\InventoryController::class, 'updateItem'])->name('items.update');
        Route::delete('/items/{item}',                     [\App\Http\Controllers\InventoryController::class, 'destroyItem'])->name('items.destroy');

        // Transactions
        Route::get('/transactions',                        [\App\Http\Controllers\InventoryController::class, 'transactions'])->name('transactions');
        Route::get('/transactions/create',                 [\App\Http\Controllers\InventoryController::class, 'createTransaction'])->name('transactions.create');
        Route::post('/transactions',                       [\App\Http\Controllers\InventoryController::class, 'storeTransaction'])->name('transactions.store');
    });

    // Document Library
    Route::get('documents/{document}/download',         [DocumentController::class, 'download'])->name('documents.download');
    Route::post('documents/{document}/toggle-featured', [DocumentController::class, 'toggleFeatured'])->name('documents.toggle-featured');
    Route::post('documents/{document}/toggle-restricted',[DocumentController::class, 'toggleRestricted'])->name('documents.toggle-restricted');
    Route::resource('documents', DocumentController::class)->except(['edit', 'update']);

    // ==================== SUPER ADMIN ====================
    Route::middleware(['super_admin'])->prefix('super')->name('super.')->group(function () {
        Route::get('/', fn() => redirect()->route('super.schools.index'));

        Route::resource('schools', SuperSchoolController::class)
            ->names([
                'index'   => 'schools.index',
                'create'  => 'schools.create',
                'store'   => 'schools.store',
                'show'    => 'schools.show',
                'edit'    => 'schools.edit',
                'update'  => 'schools.update',
                'destroy' => 'schools.destroy',
            ]);

        Route::post('schools/{school}/add-user', [SuperSchoolController::class, 'addUser'])
            ->name('schools.add-user');

        Route::post('schools/{school}/renew', [SuperSchoolController::class, 'renewSubscription'])
            ->name('schools.renew');

        Route::post('schools/{school}/status', [SuperSchoolController::class, 'setSubscriptionStatus'])
            ->name('schools.set-status');

        Route::post('schools/{school}/users/{user}/reset-password', [SuperSchoolController::class, 'resetUserPassword'])
            ->name('schools.reset-password');

        Route::get('accounts', [SuperAccountController::class, 'index'])
            ->name('accounts.index');

        Route::post('accounts/{user}/change-school', [SuperAccountController::class, 'changeSchool'])
            ->name('accounts.change-school');
    });

});
