<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Mark;
use App\Models\Exam;
use App\Models\AcademicSession;
use App\Models\PocketTransaction;
use App\Models\Staff;
use App\Models\Loan;
use App\Models\Book;
use App\Models\Lending;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Department;
use App\Models\StudentBill;
use App\Models\Payment;
use App\Models\StudentResult;
use App\Models\Leave;
use App\Models\Dormitory;
use App\Models\Enrollment;

class AIAssistantService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://api.deepseek.com/v1/chat/completions';
    protected int $maxRounds = 5;

    public function __construct()
    {
        $this->apiKey = config('services.deepseek.key', env('DEEPSEEK_API_KEY'));
        $this->model  = config('services.deepseek.model', env('DEEPSEEK_MODEL', 'deepseek-chat'));
    }

    // ─────────────────────────────────────────────────────────────
    // Public entry point
    // ─────────────────────────────────────────────────────────────

    public function chat(string $userMessage, array $conversationHistory = []): array
    {
        if (empty($this->apiKey)) {
            return [
                'reply' => "⚠️ **DeepSeek API key not configured.**\n\nAdd `DEEPSEEK_API_KEY=your_key` to your `.env` file.\n\nGet a key at **platform.deepseek.com**",
                'function_calls' => [],
            ];
        }

        $systemPrompt = $this->buildSystemPrompt();
        $messages     = $this->buildMessages($systemPrompt, $conversationHistory, $userMessage);
        $functions    = $this->getFunctions();
        $toolCalls    = [];

        try {
            $response = $this->callApi($messages, $functions);
            $rounds   = 0;

            while (!empty($response['choices'][0]['message']['tool_calls']) && $rounds < $this->maxRounds) {
                $roundToolCalls = $response['choices'][0]['message']['tool_calls'];
                $toolCalls      = array_merge($toolCalls, $roundToolCalls);

                $messages[] = [
                    'role'       => 'assistant',
                    'content'    => null,
                    'tool_calls' => $roundToolCalls,
                ];

                foreach ($roundToolCalls as $toolCall) {
                    $functionName = $toolCall['function']['name'];
                    $arguments    = json_decode($toolCall['function']['arguments'], true) ?? [];

                    Log::info('[Chani AI] tool call', ['fn' => $functionName, 'args' => $arguments]);

                    $result     = $this->executeFunction($functionName, $arguments);
                    $messages[] = [
                        'role'         => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'content'      => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                    ];
                }

                $response = $this->callApi($messages);
                $rounds++;
            }

            $reply = $response['choices'][0]['message']['content']
                ?? 'I was unable to generate a response. Please try again.';

            return ['reply' => $reply, 'function_calls' => $toolCalls];

        } catch (\Throwable $e) {
            Log::error('[Chani AI] error: ' . $e->getMessage());
            return ['reply' => $this->friendlyError($e->getMessage()), 'function_calls' => []];
        }
    }

    // ─────────────────────────────────────────────────────────────
    // System prompt – enforces _exact_summary
    // ─────────────────────────────────────────────────────────────

    protected function buildSystemPrompt(): string
    {
        $classes     = SchoolClass::orderBy('name')->pluck('name')->implode(', ') ?: 'None';
        $exams       = Exam::orderBy('name')->pluck('name')->implode(', ') ?: 'None';
        $sessions    = AcademicSession::orderBy('name')->pluck('name')->implode(', ') ?: 'None';
        $currentSession = AcademicSession::where('is_current', true)->value('name') ?? 'Not set';
        $departments = Department::orderBy('name')->pluck('name')->implode(', ') ?: 'None';
        $subjects    = Subject::orderBy('name')->pluck('name')->implode(', ') ?: 'None';
        $students    = Student::where('status', 'active')->count();
        $staff       = Staff::count();
        $dormitories = Dormitory::pluck('name')->implode(', ') ?: 'None';

        return <<<PROMPT
You are **Chani Technologies AI**, the intelligent assistant for this School Management System, powered by Chani Technologies.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 LIVE SCHOOL SNAPSHOT (read‑only context)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
• Active students   : {$students}
• Total staff       : {$staff}
• Current session   : {$currentSession}
• All sessions      : {$sessions}
• Classes           : {$classes}
• Exams             : {$exams}
• Departments       : {$departments}
• Subjects          : {$subjects}
• Dormitories       : {$dormitories}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 KEY DATABASE FACTS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
• Student marks are in `marks` table with column `mark` (NOT `score`)
• Student full name = first_name + middle_name + last_name
• Staff full name = first_name + last_name
• Loan statuses: pending, approved, active, rejected, closed
• Leave statuses: pending, approved, rejected
• PocketTransaction types: deposit, withdrawal
• Lending: `returned` boolean (true = returned)
• StudentBill statuses: unpaid, partial, paid, overpaid
• StudentResult has pre‑computed `gpa`, `total_points`, `division`
• Teacher‑subject assignments are stored in the `subject_class` table (subject_id, class_id, teacher_id).

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 ⚠️  CRITICAL BEHAVIOUR RULES (MANDATORY)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. **ALWAYS call a tool** to fetch live data before answering. Never guess numbers.
2. **If a tool response contains a field named `_exact_summary`, you MUST output that exact text as your final answer**, without adding any extra commentary or interpretation.
3. If a tool returns an error or empty result, say exactly: “No data found for that query.”
4. If the user’s question is ambiguous (e.g., missing class/exam), call the `ask_clarification` tool before fetching data.
5. Format replies with clear line breaks. Use **bold** with `**text**`.
6. Respond in the same language the user uses (English or Swahili).
7. **Never** invent numbers, averages, or comparisons that are not explicitly present in the tool output.
8. For queries about "which subjects each teacher teaches", use the `get_teacher_subjects` tool.
9. For queries about attendance, enrollments, or other models, use the `fetch_data` tool with appropriate filters.
10. **NEVER** infer subject assignments from department names. Only use data from `get_teacher_subjects`. If no data exists, say exactly: “No subject‑teacher assignments found.”
PROMPT;
    }

    // ─────────────────────────────────────────────────────────────
    // Message builder – includes full conversation history
    // ─────────────────────────────────────────────────────────────

    protected function buildMessages(string $systemPrompt, array $history, string $userMessage): array
    {
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        // Include last 20 prior turns to maintain context without exceeding token limits
        $history = array_slice($history, -20);
        foreach ($history as $msg) {
            $role    = $msg['role'] ?? '';
            $content = trim($msg['content'] ?? '');
            if (in_array($role, ['user', 'assistant']) && $content !== '') {
                $messages[] = ['role' => $role, 'content' => $content];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];
        return $messages;
    }

    // ─────────────────────────────────────────────────────────────
    // Function definitions
    // ─────────────────────────────────────────────────────────────

    protected function getFunctions(): array
    {
        return [
            // Clarification
            [
                'name'        => 'ask_clarification',
                'description' => 'Ask the user for clarification when their query is ambiguous.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'question' => ['type' => 'string', 'description' => 'The clarification question to ask.'],
                    ],
                    'required'   => ['question'],
                ],
            ],

            // Students
            [
                'name'        => 'search_student',
                'description' => 'Search a student by any part of name, admission number, or ID. Returns profile, class, dormitory, marks, and pocket balance.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => ['query' => ['type' => 'string', 'description' => 'Name, admission number, or ID']],
                    'required'   => ['query'],
                ],
            ],
            [
                'name'        => 'list_students_in_class',
                'description' => 'List all students in a specific class with gender and admission numbers.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => ['class_name' => ['type' => 'string', 'description' => 'Class name']],
                    'required'   => ['class_name'],
                ],
            ],
            [
                'name'        => 'get_top_students',
                'description' => 'Get top performing students by average mark, optionally filtered by class and/or exam.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'class_name' => ['type' => 'string', 'description' => 'Filter by class (optional)'],
                        'exam_name'  => ['type' => 'string', 'description' => 'Filter by exam (optional)'],
                        'limit'      => ['type' => 'integer', 'description' => 'Number of students (default 5, max 20)'],
                    ],
                    'required'   => [],
                ],
            ],
            [
                'name'        => 'get_student_bills',
                'description' => 'Get fee/bill status for a student: total billed, paid, balance, and breakdown.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => ['query' => ['type' => 'string', 'description' => 'Student name or admission no']],
                    'required'   => ['query'],
                ],
            ],
            [
                'name'        => 'get_fee_defaulters',
                'description' => 'List students with unpaid or partial fee bills, optionally filtered by class.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'class_name' => ['type' => 'string', 'description' => 'Filter by class (optional)'],
                        'status'     => ['type' => 'string', 'description' => 'unpaid, partial, or all (default all)'],
                    ],
                    'required'   => [],
                ],
            ],

            // Classes & Academics
            [
                'name'        => 'list_classes',
                'description' => 'List all classes with student counts, capacity, and class teacher.',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],
            [
                'name'        => 'list_exams',
                'description' => 'List all exams with term, session, and type (terminal/annual).',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],
            [
                'name'        => 'get_class_performance',
                'description' => 'Get subject‑by‑subject performance for a class: average, pass rate, highest, lowest.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'class_name' => ['type' => 'string', 'description' => 'Class name'],
                        'exam_name'  => ['type' => 'string', 'description' => 'Exam name (optional)'],
                    ],
                    'required'   => ['class_name'],
                ],
            ],
            [
                'name'        => 'get_student_results',
                'description' => 'Get pre‑computed exam results (GPA, division, total points) for a student.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'query'     => ['type' => 'string', 'description' => 'Student name or admission no'],
                        'exam_name' => ['type' => 'string', 'description' => 'Exam name (optional)'],
                    ],
                    'required'   => ['query'],
                ],
            ],
            [
                'name'        => 'list_subjects',
                'description' => 'List all subjects with code, type, and department.',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],

            // Staff
            [
                'name'        => 'get_staff_summary',
                'description' => 'Summary of all staff: count, department breakdown, loans, leaves.',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],
            [
                'name'        => 'search_staff',
                'description' => 'Search a staff member by name or email, returns position, department, salary, loan info.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => ['query' => ['type' => 'string', 'description' => 'Staff name or email']],
                    'required'   => ['query'],
                ],
            ],
            [
                'name'        => 'get_staff_on_leave',
                'description' => 'List staff members currently on leave (end_date >= today).',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => ['status' => ['type' => 'string', 'description' => 'approved, pending, or all (default approved)']],
                    'required'   => [],
                ],
            ],
            [
                'name'        => 'get_loan_summary',
                'description' => 'Summary of all staff loans: counts by status, total applied, approved, active value.',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],

            // ─── NEW: get_teacher_subjects (uses subject_class) ───
            [
                'name'        => 'get_teacher_subjects',
                'description' => 'Get the list of subjects each teacher teaches. Uses the `subject_class` table to find teacher‑subject assignments.',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],

            // Finance
            [
                'name'        => 'get_finance_summary',
                'description' => 'Overall finance summary: school fees (billed, collected, outstanding) and pocket money totals.',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],
            [
                'name'        => 'get_student_pocket_money',
                'description' => 'Get pocket money balance and recent transactions for a student.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => ['query' => ['type' => 'string', 'description' => 'Student name or admission no']],
                    'required'   => ['query'],
                ],
            ],

            // Library
            [
                'name'        => 'get_library_summary',
                'description' => 'Library stats: total titles, copies, borrowed, available.',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],
            [
                'name'        => 'search_book',
                'description' => 'Search for books by title or author.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => ['query' => ['type' => 'string', 'description' => 'Book title or author']],
                    'required'   => ['query'],
                ],
            ],

            // Dormitories
            [
                'name'        => 'get_dormitory_summary',
                'description' => 'Overview of dormitories: capacity, occupancy, dorm master.',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],

            // Generic data fetch (whitelisted models)
            [
                'name'        => 'fetch_data',
                'description' => 'Fetch data from any allowed model. Use for queries not covered by other tools.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'model'   => ['type' => 'string', 'description' => 'Model name (Student, Staff, etc.)'],
                        'columns' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Columns to select (optional)'],
                        'filters' => ['type' => 'object', 'description' => 'Key‑value WHERE conditions'],
                        'limit'   => ['type' => 'integer', 'description' => 'Max records (default 20)'],
                    ],
                    'required' => ['model'],
                ],
            ],

            // Overview
            [
                'name'        => 'get_school_overview',
                'description' => 'Full school overview: students, staff, classes, finances, library, dormitories.',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Dispatcher
    // ─────────────────────────────────────────────────────────────

    protected function executeFunction(string $name, array $args): array
    {
        try {
            return match ($name) {
                'ask_clarification'      => $this->askClarification($args['question'] ?? 'Please provide more details.'),
                'search_student'         => $this->searchStudent($args['query'] ?? ''),
                'list_students_in_class' => $this->listStudentsInClass($args['class_name'] ?? ''),
                'get_top_students'       => $this->getTopStudents($args['class_name'] ?? null, $args['exam_name'] ?? null, (int)($args['limit'] ?? 5)),
                'get_student_bills'      => $this->getStudentBills($args['query'] ?? ''),
                'get_fee_defaulters'     => $this->getFeeDefaulters($args['class_name'] ?? null, $args['status'] ?? 'all'),
                'list_classes'           => $this->listClasses(),
                'list_exams'             => $this->listExams(),
                'get_class_performance'  => $this->getClassPerformance($args['class_name'] ?? '', $args['exam_name'] ?? null),
                'get_student_results'    => $this->getStudentResults($args['query'] ?? '', $args['exam_name'] ?? null),
                'list_subjects'          => $this->listSubjects(),
                'get_staff_summary'      => $this->getStaffSummary(),
                'search_staff'           => $this->searchStaff($args['query'] ?? ''),
                'get_staff_on_leave'     => $this->getStaffOnLeave($args['status'] ?? 'approved'),
                'get_loan_summary'       => $this->getLoanSummary(),
                'get_teacher_subjects'   => $this->getTeacherSubjects(),
                'get_finance_summary'    => $this->getFinanceSummary(),
                'get_student_pocket_money' => $this->getStudentPocketMoney($args['query'] ?? ''),
                'get_library_summary'    => $this->getLibrarySummary(),
                'search_book'            => $this->searchBook($args['query'] ?? ''),
                'get_dormitory_summary'  => $this->getDormitorySummary(),
                'fetch_data'             => $this->fetchData($args['model'] ?? '', $args['columns'] ?? null, $args['filters'] ?? null, (int)($args['limit'] ?? 20)),
                'get_school_overview'    => $this->getSchoolOverview(),
                default                  => ['error' => "Unknown function: {$name}"],
            };
        } catch (\Throwable $e) {
            Log::error("[Chani AI] {$name} failed: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Tool implementations (all return _exact_summary)
    // ─────────────────────────────────────────────────────────────

    protected function askClarification(string $question): array
    {
        return ['_exact_summary' => '❓ ' . $question];
    }

    // ─── Students ────────────────────────────────────────────────

    protected function searchStudent(string $query): array
    {
        $query = trim($query);
        if (empty($query)) {
            return ['_exact_summary' => 'Please provide a name or admission number.', 'found' => false];
        }

        $words = array_filter(explode(' ', $query));
        $students = Student::where(function ($q) use ($query, $words) {
            $q->where('admission_no', $query)
              ->orWhere('id', is_numeric($query) ? (int)$query : 0)
              ->orWhere(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name)"), 'LIKE', "%{$query}%");
            foreach ($words as $word) {
                $q->orWhere('first_name',  'LIKE', "%{$word}%")
                  ->orWhere('middle_name', 'LIKE', "%{$word}%")
                  ->orWhere('last_name',   'LIKE', "%{$word}%");
            }
        })->with(['schoolClass', 'dormitory', 'academicSession'])->limit(10)->get();

        if ($students->isEmpty()) {
            $sample = Student::active()->limit(6)->get()
                ->map(fn($s) => $s->full_name . ' (' . $s->admission_no . ')')
                ->implode(', ');
            return ['_exact_summary' => "No student found for '{$query}'. Sample active students: {$sample}", 'found' => false];
        }

        $summary = "Found " . $students->count() . " student(s):\n";
        foreach ($students as $s) {
            $marks = Mark::where('student_id', $s->id)->with(['subject','exam','grade'])->latest()->limit(12)->get();
            $avg = $marks->isNotEmpty() ? round($marks->avg('mark'), 1) : null;
            $pocket = PocketTransaction::where('student_id', $s->id)
                ->selectRaw("SUM(CASE WHEN type='deposit' THEN amount ELSE -amount END) as balance")
                ->value('balance');
            $pocket = is_null($pocket) ? 0 : (float)$pocket;

            $class   = isset($s->schoolClass) ? $s->schoolClass->name : 'Unassigned';
            $dorm    = isset($s->dormitory) ? $s->dormitory->name : 'Day scholar';
            $avgMark = is_null($avg) ? 'N/A' : $avg;

            $summary .= "• {$s->full_name} (Adm: {$s->admission_no}) – Class: {$class}, Gender: {$s->gender}, Status: {$s->status}, Age: {$s->age}, Dorm: {$dorm}, Pocket: {$pocket}, Avg Mark: {$avgMark}";
            if ($marks->isNotEmpty()) {
                $recent = $marks->take(5)->map(fn($m) => $m->subject->name . ':' . $m->mark)->implode(', ');
                $summary .= ", Recent marks: {$recent}";
            }
            $summary .= "\n";
        }
        $summary = rtrim($summary);

        return ['_exact_summary' => $summary, 'found' => true];
    }

    protected function listStudentsInClass(string $className): array
    {
        $class = $this->findClass($className);
        if (!$class) {
            $available = SchoolClass::pluck('name')->implode(', ');
            return ['_exact_summary' => "Class '{$className}' not found. Available: {$available}"];
        }

        $students = Student::where('class_id', $class->id)->orderBy('first_name')->get();
        $teacher = isset($class->teacher) ? $class->teacher->first_name . ' ' . $class->teacher->last_name : 'Not assigned';
        $summary = "Class: {$class->name}, Teacher: {$teacher}, Total: {$students->count()} (Male: {$students->where('gender','male')->count()}, Female: {$students->where('gender','female')->count()})\n";
        foreach ($students as $s) {
            $summary .= "• {$s->full_name} (Adm: {$s->admission_no}, Gender: {$s->gender}, Status: {$s->status})\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    protected function getTopStudents(?string $className, ?string $examName, int $limit = 5): array
    {
        $limit = min(max($limit, 1), 20);
        $query = DB::table('marks as m')
            ->join('students as s', 's.id', '=', 'm.student_id')
            ->join('school_classes as c', 'c.id', '=', 's.class_id')
            ->whereNull('m.deleted_at')
            ->whereNull('s.deleted_at')
            ->when($this->currentSchoolId(), fn($q, $id) => $q->where('s.school_id', $id))
            ->select(
                's.id',
                DB::raw("TRIM(CONCAT(s.first_name, ' ', COALESCE(s.middle_name,''), ' ', s.last_name)) as name"),
                's.admission_no',
                'c.name as class',
                DB::raw('ROUND(AVG(m.mark), 2) as average_mark'),
                DB::raw('COUNT(DISTINCT m.subject_id) as subjects_sat')
            )
            ->groupBy('s.id', 's.first_name', 's.middle_name', 's.last_name', 's.admission_no', 'c.name')
            ->orderByDesc('average_mark')
            ->limit($limit);

        if ($className) {
            $class = $this->findClass($className);
            if ($class) $query->where('s.class_id', $class->id);
            else return ['_exact_summary' => "Class '{$className}' not found."];
        }

        if ($examName) {
            $exam = $this->findExam($examName);
            if ($exam) $query->where('m.exam_id', $exam->id);
            else return ['_exact_summary' => "Exam '{$examName}' not found."];
        }

        $results = $query->get();
        if ($results->isEmpty()) {
            $scope = $className ?? 'Whole School';
            $examPart = $examName ? " for exam {$examName}" : '';
            return ['_exact_summary' => "No marks found for top students in {$scope}{$examPart}."];
        }

        $summary = "Top " . $limit . " student(s)";
        if ($className) $summary .= " in class " . $className;
        if ($examName) $summary .= " for exam " . $examName;
        $summary .= ":\n";
        $rank = 1;
        foreach ($results as $r) {
            $summary .= $rank . ". " . trim($r->name) . " (Adm: {$r->admission_no}) – Class: {$r->class}, Avg: {$r->average_mark}%, Subjects: {$r->subjects_sat}\n";
            $rank++;
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    protected function getStudentBills(string $query): array
    {
        $student = $this->findStudent($query);
        if (!$student) {
            return ['_exact_summary' => "Student '{$query}' not found."];
        }

        $bills = StudentBill::where('student_id', $student->id)->with('bill')->get();
        $totalBilled = (float)$bills->sum('total_amount');
        $totalPaid   = (float)$bills->sum('amount_paid');
        $totalBalance = (float)$bills->sum('balance');

        $summary = "Student: {$student->full_name} (Adm: {$student->admission_no}), Class: " . (isset($student->schoolClass) ? $student->schoolClass->name : '—') . "\n";
        $summary .= "Total Billed: {$totalBilled}, Paid: {$totalPaid}, Balance: {$totalBalance}\n";
        if ($bills->isNotEmpty()) {
            $summary .= "Bills:\n";
            foreach ($bills as $b) {
                $title = isset($b->bill) ? $b->bill->title : '—';
                $due = $b->due_date ? ", Due: {$b->due_date->format('Y-m-d')}" : '';
                $summary .= "• {$title}: Total: {$b->total_amount}, Paid: {$b->amount_paid}, Balance: {$b->balance}, Status: {$b->status}{$due}\n";
            }
        } else {
            $summary .= "No bills found.";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    protected function getFeeDefaulters(?string $className, string $status = 'all'): array
    {
        $statuses = $status === 'all' ? ['unpaid', 'partial'] : [$status];
        $query = StudentBill::with(['student.schoolClass', 'bill'])
            ->whereIn('status', $statuses);

        if ($className) {
            $class = $this->findClass($className);
            if ($class) {
                $query->whereHas('student', fn($q) => $q->where('class_id', $class->id));
            }
        }

        $bills = $query->orderByDesc('balance')->get();
        if ($bills->isEmpty()) {
            $filter = $className ?? 'All Classes';
            return ['_exact_summary' => "No fee defaulters found for {$filter} with status '{$status}'."];
        }

        $totalOutstanding = (float)$bills->sum('balance');
        $summary = "Fee defaulters (" . ($className ?? 'All Classes') . ", status: {$status}): {$bills->count()} defaulters, Total Outstanding: {$totalOutstanding}\n";
        foreach ($bills->take(30) as $b) {
            $name  = isset($b->student) ? $b->student->full_name : '—';
            $adm   = isset($b->student) ? $b->student->admission_no : '—';
            $class = isset($b->student->schoolClass) ? $b->student->schoolClass->name : '—';
            $title = isset($b->bill) ? $b->bill->title : '—';
            $summary .= "• {$name} (Adm: {$adm}, Class: {$class}) – Bill: {$title}, Balance: {$b->balance}, Status: {$b->status}\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    // ─── Classes & Academics ────────────────────────────────────

    protected function listClasses(): array
    {
        $classes = SchoolClass::withCount('students')->with('teacher')->orderBy('name')->get();
        if ($classes->isEmpty()) {
            return ['_exact_summary' => "No classes found."];
        }
        $summary = "Total Classes: {$classes->count()}\n";
        foreach ($classes as $c) {
            $teacher = isset($c->teacher) ? $c->teacher->first_name . ' ' . $c->teacher->last_name : 'Not assigned';
            $summary .= "• {$c->name} (Level: {$c->level}, Section: {$c->section}, Capacity: {$c->capacity}, Students: {$c->students_count}, Teacher: {$teacher})\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    protected function listExams(): array
    {
        $exams = Exam::with('academicSession')->orderBy('name')->get();
        if ($exams->isEmpty()) {
            return ['_exact_summary' => "No exams found."];
        }
        $summary = "Total Exams: {$exams->count()}\n";
        foreach ($exams as $e) {
            $session = isset($e->academicSession) ? $e->academicSession->name : '—';
            $summary .= "• {$e->name} (Term: {$e->term}, Session: {$session}, Terminal: " . ($e->is_terminal_exam ? 'Yes' : 'No') . ", Annual: " . ($e->is_annual_exam ? 'Yes' : 'No') . ")\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    protected function getClassPerformance(string $className, ?string $examName = null): array
    {
        $class = $this->findClass($className);
        if (!$class) {
            $available = SchoolClass::pluck('name')->implode(', ');
            return ['_exact_summary' => "Class '{$className}' not found. Available: {$available}"];
        }

        $query = DB::table('marks as m')
            ->join('students as s', 's.id', '=', 'm.student_id')
            ->join('subjects as sub', 'sub.id', '=', 'm.subject_id')
            ->whereNull('m.deleted_at')
            ->whereNull('s.deleted_at')
            ->where('s.class_id', $class->id)
            ->when($this->currentSchoolId(), fn($q, $id) => $q->where('s.school_id', $id))
            ->select(
                'sub.name as subject',
                DB::raw('ROUND(AVG(m.mark), 2) as average'),
                DB::raw('MAX(m.mark) as highest'),
                DB::raw('MIN(m.mark) as lowest'),
                DB::raw('COUNT(m.id) as entries'),
                DB::raw('SUM(CASE WHEN m.mark >= 50 THEN 1 ELSE 0 END) as passed')
            )
            ->groupBy('sub.id', 'sub.name')
            ->orderBy('sub.name');

        if ($examName) {
            $exam = $this->findExam($examName);
            if ($exam) $query->where('m.exam_id', $exam->id);
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            $examPart = $examName ? " in {$examName}" : '';
            return ['_exact_summary' => "No marks entered for class {$className}{$examPart}."];
        }

        $overallAvg = round($rows->avg('average'), 2);
        $summary = "Performance for class {$className}";
        if ($examName) $summary .= " in exam {$examName}";
        $summary .= ":\n• Overall average: {$overallAvg}%\n• Total students: {$class->students()->count()}\n• Subjects:\n";
        foreach ($rows as $r) {
            $passRate = $r->entries > 0 ? round($r->passed / $r->entries * 100, 1) . '%' : '0%';
            $summary .= "  - {$r->subject}: avg {$r->average}%, highest {$r->highest}, lowest {$r->lowest}, pass rate {$passRate}, entries {$r->entries}\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    protected function getStudentResults(string $query, ?string $examName = null): array
    {
        $student = $this->findStudent($query);
        if (!$student) {
            return ['_exact_summary' => "Student '{$query}' not found."];
        }

        $q = StudentResult::where('student_id', $student->id)->with('exam');
        if ($examName) {
            $exam = $this->findExam($examName);
            if ($exam) $q->where('exam_id', $exam->id);
        }
        $results = $q->get();

        if ($results->isEmpty()) {
            $examPart = $examName ? " for exam {$examName}" : '';
            return ['_exact_summary' => "No results found for student {$student->full_name}{$examPart}."];
        }

        $summary = "Results for {$student->full_name} (Adm: {$student->admission_no}, Class: " . (isset($student->schoolClass) ? $student->schoolClass->name : '—') . "):\n";
        foreach ($results as $r) {
            $examNameDisplay = isset($r->exam) ? $r->exam->name : '—';
            $summary .= "• Exam: {$examNameDisplay}, GPA: {$r->gpa}, Total Points: {$r->total_points}, Division: {$r->division}\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    protected function listSubjects(): array
    {
        $subjects = Subject::with('department')->orderBy('name')->get();
        if ($subjects->isEmpty()) {
            return ['_exact_summary' => "No subjects found."];
        }
        $summary = "Total Subjects: {$subjects->count()}\n";
        foreach ($subjects as $s) {
            $dept = isset($s->department) ? $s->department->name : '—';
            $summary .= "• {$s->name} (Code: {$s->code}, Type: {$s->type}, Dept: {$dept})\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    // ─── Staff ──────────────────────────────────────────────────

    protected function getStaffSummary(): array
    {
        $staff = Staff::with('department')->get();
        $byDept = $staff->groupBy(fn($s) => $s->department->name ?? 'No Department')
            ->map(fn($g) => $g->count())->toArray();

        $totalStaff = $staff->count();
        $activeLoans = Loan::where('status', 'active')->count();
        $pendingLoans = Loan::where('status', 'pending')->count();
        $approvedLoans = Loan::where('status', 'approved')->count();
        $pendingLeaves = Leave::where('status', 'pending')->count();
        $approvedLeaves = Leave::where('status', 'approved')->where('end_date', '>=', now())->count();

        $summary = "Staff Summary:\n• Total Staff: {$totalStaff}\n• Department breakdown:\n";
        foreach ($byDept as $dept => $count) {
            $summary .= "  - {$dept}: {$count}\n";
        }
        $summary .= "• Loans: Active: {$activeLoans}, Pending: {$pendingLoans}, Approved: {$approvedLoans}\n";
        $summary .= "• Leaves: Pending: {$pendingLeaves}, Currently Approved: {$approvedLeaves}";
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    protected function searchStaff(string $query): array
    {
        $staff = Staff::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name',  'like', "%{$query}%")
            ->orWhere('email',      'like', "%{$query}%")
            ->with(['department', 'loans'])
            ->limit(5)->get();

        if ($staff->isEmpty()) {
            return ['_exact_summary' => "No staff found for '{$query}'."];
        }

        $summary = "Found " . $staff->count() . " staff member(s):\n";
        foreach ($staff as $s) {
            $activeLoanBalance = $s->loans->where('status', 'active')->sum('remaining_balance');
            $dept = isset($s->department) ? $s->department->name : '—';
            $hireDate = $s->hire_date ? $s->hire_date->format('Y-m-d') : 'N/A';
            $summary .= "• {$s->first_name} {$s->last_name} (Email: {$s->email}, Phone: {$s->phone}, Position: {$s->position}, Dept: {$dept}, Salary: {$s->basic_salary}, Hired: {$hireDate}, Years: {$s->years_employed}, Active Loan Balance: {$activeLoanBalance})\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    protected function getStaffOnLeave(string $status = 'approved'): array
    {
        $q = Leave::with(['staff'])->where('end_date', '>=', now());
        if ($status !== 'all') {
            $q->where('status', $status);
        }
        $leaves = $q->get();

        if ($leaves->isEmpty()) {
            return ['_exact_summary' => "No staff on leave with status '{$status}'."];
        }

        $summary = "Staff on leave (status: {$status}): {$leaves->count()} records\n";
        foreach ($leaves as $l) {
            $staffName = isset($l->staff) ? $l->staff->first_name . ' ' . $l->staff->last_name : 'Unknown';
            $start = $l->start_date ? $l->start_date->format('Y-m-d') : 'N/A';
            $end   = $l->end_date ? $l->end_date->format('Y-m-d') : 'N/A';
            $reason = $l->reason ? ", Reason: {$l->reason}" : '';
            $summary .= "• {$staffName} – Type: {$l->type}, From: {$start} to {$end}, Status: {$l->status}{$reason}\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    protected function getLoanSummary(): array
    {
        $loans = Loan::with('staff')->get();
        if ($loans->isEmpty()) {
            return ['_exact_summary' => "No loan records found."];
        }

        $total = $loans->count();
        $byStatus = $loans->groupBy('status')->map(fn($g) => $g->count())->toArray();
        $totalApplied = (float)$loans->sum('amount_applied');
        $totalApproved = (float)$loans->whereIn('status', ['approved','active','closed'])->sum('amount_approved');
        $activeCount = $loans->where('status', 'active')->count();
        $activeValue = (float)$loans->where('status', 'active')->sum('amount_approved');

        $summary = "Loan Summary:\n• Total Loans: {$total}\n• By Status:\n";
        foreach ($byStatus as $st => $cnt) {
            $summary .= "  - {$st}: {$cnt}\n";
        }
        $summary .= "• Total Applied: {$totalApplied}\n• Total Approved: {$totalApproved}\n• Active Loans: {$activeCount} (Value: {$activeValue})";
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    // ─── getTeacherSubjects – uses subject_class ────────────────

    protected function getTeacherSubjects(): array
    {
        $query = DB::table('subject_class')
            ->join('subjects', 'subjects.id', '=', 'subject_class.subject_id')
            ->join('staff', 'staff.id', '=', 'subject_class.teacher_id')
            ->select('staff.id', 'staff.first_name', 'staff.last_name', 'subjects.name as subject')
            ->whereNotNull('subject_class.teacher_id')
            ->orderBy('staff.first_name');

        if ($schoolId = $this->currentSchoolId()) {
            $query->where('staff.school_id', $schoolId);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            return ['_exact_summary' => 'No subject-teacher assignments are currently recorded in the system.'];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $teacherId = $row->id;
            if (!isset($grouped[$teacherId])) {
                $grouped[$teacherId] = [
                    'teacher_name' => $row->first_name . ' ' . $row->last_name,
                    'subjects' => [],
                ];
            }
            $grouped[$teacherId]['subjects'][] = $row->subject;
        }

        $summary = "Teacher – Subject Assignments:\n";
        foreach ($grouped as $teacher) {
            $summary .= "• " . $teacher['teacher_name'] . " – " . implode(', ', array_unique($teacher['subjects'])) . "\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    // ─── Finance ─────────────────────────────────────────────────

    protected function getFinanceSummary(): array
    {
        $deposits    = (float)PocketTransaction::where('type', 'deposit')->sum('amount');
        $withdrawals = (float)PocketTransaction::where('type', 'withdrawal')->sum('amount');
        $totalBilled  = (float)StudentBill::sum('total_amount');
        $totalPaid    = (float)StudentBill::sum('amount_paid');
        $totalBalance = (float)StudentBill::sum('balance');

        $byStatus = StudentBill::selectRaw('status, COUNT(*) as count, SUM(balance) as balance_total')
            ->groupBy('status')->get()
            ->mapWithKeys(fn($r) => [$r->status => ['count' => $r->count, 'balance' => (float)$r->balance_total]]);

        $summary = "Finance Summary:\n";
        $summary .= "Fees:\n• Total Billed: {$totalBilled}\n• Total Collected: {$totalPaid}\n• Outstanding: {$totalBalance}\n";
        if ($byStatus->isNotEmpty()) {
            $summary .= "  By status:\n";
            foreach ($byStatus as $st => $data) {
                $summary .= "    - {$st}: {$data['count']} bills, Balance: {$data['balance']}\n";
            }
        }
        $summary .= "Pocket Money:\n• Total Deposits: {$deposits}\n• Total Withdrawals: {$withdrawals}\n• Net Balance: " . ($deposits - $withdrawals) . "\n• Total Transactions: " . PocketTransaction::count();
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    protected function getStudentPocketMoney(string $query): array
    {
        $student = $this->findStudent($query);
        if (!$student) {
            return ['_exact_summary' => "Student '{$query}' not found."];
        }

        $balance = (float)(PocketTransaction::where('student_id', $student->id)
            ->selectRaw("SUM(CASE WHEN type='deposit' THEN amount ELSE -amount END) as balance")
            ->value('balance') ?? 0);

        $txns = PocketTransaction::where('student_id', $student->id)
            ->orderByDesc('created_at')->limit(10)->get();

        $summary = "Pocket Money for {$student->full_name} (Adm: {$student->admission_no}): Current Balance: {$balance}\n";
        if ($txns->isNotEmpty()) {
            $summary .= "Recent transactions (last " . $txns->count() . "):\n";
            foreach ($txns as $t) {
                $note = $t->note ? ", Note: {$t->note}" : '';
                $summary .= "• {$t->created_at->format('Y-m-d H:i')} – {$t->type}: {$t->amount}, Balance after: {$t->balance_after}{$note}\n";
            }
        } else {
            $summary .= "No transactions found.";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    // ─── Library ──────────────────────────────────────────────────

    protected function getLibrarySummary(): array
    {
        $totalCopies = (int)Book::sum('quantity');
        $borrowed = (int)Lending::where('returned', false)->sum('quantity');

        $summary = "Library Summary:\n• Total Book Titles: " . Book::count() . "\n• Total Copies: {$totalCopies}\n• Currently Borrowed: {$borrowed}\n• Available Copies: " . ($totalCopies - $borrowed) . "\n• Total Lendings: " . Lending::count() . "\n• Not Returned: " . Lending::where('returned', false)->count();
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    protected function searchBook(string $query): array
    {
        $books = Book::where('title', 'like', "%{$query}%")
            ->orWhere('author', 'like', "%{$query}%")
            ->with('category')->limit(10)->get();

        if ($books->isEmpty()) {
            return ['_exact_summary' => "No books found for '{$query}'."];
        }

        $summary = "Found " . $books->count() . " book(s):\n";
        foreach ($books as $b) {
            $borrowedCount = (int)Lending::where('book_id', $b->id)->where('returned', false)->sum('quantity');
            $available = $b->quantity - $borrowedCount;
            $category = isset($b->category) ? $b->category->name : '—';
            $summary .= "• {$b->title} by {$b->author} (Category: {$category}, ISBN: {$b->isbn}, Total: {$b->quantity}, Available: {$available})\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    // ─── Dormitories ─────────────────────────────────────────────

    protected function getDormitorySummary(): array
    {
        $dorms = Dormitory::with('dormMaster')->get();
        if ($dorms->isEmpty()) {
            return ['_exact_summary' => "No dormitories found."];
        }

        $summary = "Dormitory Summary:\n";
        foreach ($dorms as $d) {
            $master = isset($d->dormMaster) ? $d->dormMaster->first_name . ' ' . $d->dormMaster->last_name : 'Not assigned';
            $summary .= "• {$d->name} (Gender: {$d->gender}, Capacity: {$d->capacity}, Occupied: {$d->occupied_beds_count}, Available: {$d->available_beds_count}, Occupancy: {$d->occupancy_rate}%, Dorm Master: {$master})\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    // ─── Generic data fetch ─────────────────────────────────────

    protected function fetchData(string $model, ?array $columns = null, ?array $filters = null, int $limit = 20): array
    {
        $allowedModels = [
            'Student', 'Staff', 'SchoolClass', 'Exam', 'Subject', 'Mark',
            'StudentBill', 'Payment', 'Loan', 'Leave', 'Book', 'Lending',
            'Dormitory', 'Attendance', 'Enrollment', 'AcademicSession',
        ];

        $modelClass = 'App\\Models\\' . $model;
        if (!class_exists($modelClass) || !in_array($model, $allowedModels)) {
            return ['_exact_summary' => "Model '{$model}' is not allowed or does not exist."];
        }

        $query = $modelClass::query();
        if ($filters && is_array($filters)) {
            foreach ($filters as $key => $value) {
                $query->where($key, $value);
            }
        }
        if ($columns && is_array($columns)) {
            $query->select($columns);
        }
        $records = $query->limit($limit)->get();

        if ($records->isEmpty()) {
            return ['_exact_summary' => "No records found for model '{$model}' with the given filters."];
        }

        $summary = "Found " . $records->count() . " record(s) from {$model}:\n";
        foreach ($records as $record) {
            $fields = $record->toArray();
            unset($fields['created_at'], $fields['updated_at'], $fields['deleted_at']);
            $line = [];
            foreach ($fields as $key => $value) {
                if (is_scalar($value) || is_null($value)) {
                    $line[] = "{$key}: " . ($value ?? 'null');
                }
            }
            $summary .= "• " . implode(', ', $line) . "\n";
        }
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    // ─── Overview ─────────────────────────────────────────────────

    protected function getSchoolOverview(): array
    {
        $currentSession = AcademicSession::where('is_current', true)->first();
        $finance = $this->getFinanceSummary();
        $library = $this->getLibrarySummary();
        $dorms = $this->getDormitorySummary();
        $loans = $this->getLoanSummary();

        $summary = "🏫 School Overview:\n";
        $summary .= "Students: Active: " . Student::where('status','active')->count() . " (M: " . Student::where('gender','male')->where('status','active')->count() . ", F: " . Student::where('gender','female')->where('status','active')->count() . "), Total: " . Student::count() . "\n";
        $summary .= "Classes: " . SchoolClass::count() . "\n";
        $summary .= "Staff: " . Staff::count() . "\n";
        $summary .= "Current Session: " . ($currentSession ? $currentSession->name : 'Not set') . "\n";
        $summary .= $finance['_exact_summary'] . "\n";
        $summary .= $library['_exact_summary'] . "\n";
        $summary .= $dorms['_exact_summary'] . "\n";
        $summary .= $loans['_exact_summary'];
        $summary = rtrim($summary);
        return ['_exact_summary' => $summary];
    }

    // ─────────────────────────────────────────────────────────────
    // DeepSeek API call
    // ─────────────────────────────────────────────────────────────

    protected function callApi(array $messages, ?array $functions = null): array
    {
        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'temperature' => 0.0,
            'top_p'       => 0.1,
            'max_tokens'  => 1024,
        ];

        if ($functions) {
            $payload['tools'] = array_map(fn($f) => ['type' => 'function', 'function' => $f], $functions);
            $payload['tool_choice'] = 'auto';
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->post($this->baseUrl, $payload);

        if (!$response->successful()) {
            Log::error('[Chani AI] DeepSeek error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('AI service error (HTTP ' . $response->status() . '): ' . $response->body());
        }

        return $response->json();
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    protected function currentSchoolId(): ?int
    {
        return app()->bound('currentSchool') ? (int) app('currentSchool')->id : null;
    }

    protected function findStudent(string $query): ?Student
    {
        $query = trim($query);
        if (empty($query)) return null;

        $words = array_filter(explode(' ', $query));

        return Student::where(function ($q) use ($query, $words) {
            $q->where('admission_no', $query)
              ->orWhere('id', is_numeric($query) ? (int)$query : 0)
              ->orWhere(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name)"), 'LIKE', "%{$query}%");
            foreach ($words as $word) {
                $q->orWhere('first_name',  'LIKE', "%{$word}%")
                  ->orWhere('middle_name', 'LIKE', "%{$word}%")
                  ->orWhere('last_name',   'LIKE', "%{$word}%");
            }
        })->with(['schoolClass', 'dormitory'])->first();
    }

    protected function findClass(string $name): ?SchoolClass
    {
        return SchoolClass::where('name', $name)->first()
            ?? SchoolClass::where('name', 'like', "%{$name}%")->first();
    }

    protected function findExam(string $name): ?Exam
    {
        return Exam::where('name', $name)->first()
            ?? Exam::where('name', 'like', "%{$name}%")->first();
    }

    protected function friendlyError(string $detail): string
    {
        return "⚠️ Chani Technologies AI ran into a problem fetching that data.\n\n"
             . "Try asking:\n"
             . "• \"Show school overview\"\n"
             . "• \"List all classes\"\n"
             . "• \"Finance summary\"\n"
             . "• \"Top 5 students\"\n\n"
             . "Technical: {$detail}";
    }
}