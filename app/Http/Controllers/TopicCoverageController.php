<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\LessonPlan;
use App\Models\LessonSubtopic;
use App\Models\LessonTopic;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\TimetableSessionLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TopicCoverageController extends Controller
{
    private function canManage(LessonPlan $plan): bool
    {
        $user = Auth::user();
        if ($user->hasAnyRole(['Admin', 'Academic'])) return true;
        if ($user->hasRole('HOD')) {
            $staff = Staff::where('user_id', $user->id)->first();
            return $staff && $plan->subject->department_id === $staff->department_id;
        }
        return $plan->teacher_id === $user->id;
    }

    // ── Evaluation Dashboard ──────────────────────────────────────────────
    public function evaluation(Request $request)
    {
        $user         = Auth::user();
        $isManagement = $user->hasAnyRole(['Admin', 'Academic', 'HOD']);
        $isTeacher    = $user->hasRole('Teacher');

        $sessions        = AcademicSession::orderBy('name', 'desc')->get();
        $currentSession  = AcademicSession::where('is_current', true)->first();
        $selectedSession = $request->filled('session_id')
            ? AcademicSession::find($request->session_id)
            : $currentSession;

        $departments = Department::orderBy('name')->get();
        $selectedDept = $request->filled('department_id') ? (int) $request->department_id : null;

        // Determine which teacher IDs to evaluate
        if (!$isManagement) {
            // Pure teacher — only self
            $teacherIds = [$user->id];
        } elseif ($user->hasRole('HOD') && !$user->hasAnyRole(['Admin', 'Academic'])) {
            $hodStaff = Staff::where('user_id', $user->id)->first();
            $teacherIds = $hodStaff
                ? User::role('Teacher')
                    ->whereHas('staff', fn($q) => $q->where('department_id', $hodStaff->department_id))
                    ->pluck('id')->toArray()
                : [$user->id];
        } else {
            $tQuery = User::role('Teacher');
            if ($selectedDept) {
                $tQuery->whereHas('staff', fn($q) => $q->where('department_id', $selectedDept));
            }
            $teacherIds = $tQuery->pluck('id')->toArray();
        }

        $teachers = User::with(['staff.department'])
            ->whereIn('id', $teacherIds)
            ->orderBy('name')
            ->get();

        $teacherData = [];

        foreach ($teachers as $teacher) {
            // ── Lesson plan coverage ───────────────────────────────────
            $planQuery = LessonPlan::with(['topics.subtopics', 'subject', 'schoolClass'])
                ->where('teacher_id', $teacher->id);
            if ($selectedSession) {
                $planQuery->where('academic_session_id', $selectedSession->id);
            }
            $plans = $planQuery->get();

            $totalTopics = 0; $coveredTopics = 0;
            $totalSubs   = 0; $coveredSubs   = 0;

            foreach ($plans as $plan) {
                foreach ($plan->topics as $topic) {
                    $totalTopics++;
                    $subs = $topic->subtopics;
                    $cov  = $subs->where('status', 'covered')->count();
                    if ($subs->count() > 0 && $cov === $subs->count()) $coveredTopics++;
                    $totalSubs += $subs->count();
                    $coveredSubs += $cov;
                }
            }

            $coveragePct = $totalSubs > 0 ? round($coveredSubs / $totalSubs * 100, 1) : 0;

            // ── Session logs ───────────────────────────────────────────
            $logs        = TimetableSessionLog::where('teacher_id', $teacher->id)->get();
            $sessTotal   = $logs->count();
            $attended    = $logs->where('status', 'attended')->count();
            $late        = $logs->where('status', 'late')->count();
            $absent      = $logs->where('status', 'absent')->count();
            $other       = $logs->where('status', 'other')->count();
            $attRate     = $sessTotal > 0 ? round(($attended + $late) / $sessTotal * 100, 1) : null;
            $topicsLogged = $logs->whereNotNull('lesson_topic_id')->count();
            $recordRate  = $sessTotal > 0 ? round($topicsLogged / $sessTotal * 100) : null;

            // ── Rating ─────────────────────────────────────────────────
            if ($plans->count() === 0) {
                $rating = 'no-plan';
            } elseif ($coveragePct >= 80 && ($attRate === null || $attRate >= 85)) {
                $rating = 'excellent';
            } elseif ($coveragePct >= 50 && ($attRate === null || $attRate >= 70)) {
                $rating = 'good';
            } elseif ($coveragePct >= 25) {
                $rating = 'fair';
            } else {
                $rating = 'poor';
            }

            // ── Recommendations ────────────────────────────────────────
            $recs = [];
            if ($plans->count() === 0) {
                $recs[] = ['type' => 'danger',
                    'msg' => 'No lesson plan on file for this session. Teacher must submit a curriculum plan immediately.'];
            } else {
                if ($coveragePct < 25) {
                    $recs[] = ['type' => 'danger',
                        'msg' => "Curriculum coverage at {$coveragePct}% — significantly behind. Immediate HOD intervention required."];
                } elseif ($coveragePct < 50) {
                    $recs[] = ['type' => 'warning',
                        'msg' => "Coverage at {$coveragePct}% — below expected. HOD should schedule a progress review meeting."];
                } elseif ($coveragePct < 80) {
                    $recs[] = ['type' => 'info',
                        'msg' => "Coverage at {$coveragePct}% — on track. Continue current delivery pace."];
                } else {
                    $recs[] = ['type' => 'success',
                        'msg' => "Coverage at {$coveragePct}% — excellent curriculum delivery. Commend and maintain."];
                }
            }

            if ($attRate !== null && $attRate < 70) {
                $recs[] = ['type' => 'danger',
                    'msg' => "Attendance rate {$attRate}% is critically low. HOD must formally review with teacher."];
            } elseif ($attRate !== null && $attRate < 85) {
                $recs[] = ['type' => 'warning',
                    'msg' => "Attendance rate {$attRate}% — below the 85% benchmark. Monitor closely."];
            }

            if ($recordRate !== null && $recordRate < 50 && $sessTotal >= 3) {
                $recs[] = ['type' => 'warning',
                    'msg' => "Only {$recordRate}% of sessions have topic records. Encourage consistent session logging."];
            }

            $teacherData[] = [
                'teacher'        => $teacher,
                'dept'           => $teacher->staff?->department,
                'plans'          => $plans,
                'total_plans'    => $plans->count(),
                'total_topics'   => $totalTopics,
                'covered_topics' => $coveredTopics,
                'total_subs'     => $totalSubs,
                'covered_subs'   => $coveredSubs,
                'coverage_pct'   => $coveragePct,
                'sessions_total' => $sessTotal,
                'attended'       => $attended,
                'late'           => $late,
                'absent'         => $absent,
                'other'          => $other,
                'att_rate'       => $attRate,
                'topics_logged'  => $topicsLogged,
                'record_rate'    => $recordRate,
                'rating'         => $rating,
                'recommendations'=> $recs,
            ];
        }

        // Worst performers first for management; teacher sees own record
        if ($isManagement) {
            usort($teacherData, fn($a, $b) => $a['coverage_pct'] <=> $b['coverage_pct']);
        }

        $userStaff = Staff::where('user_id', $user->id)->first();
        $userDept  = $userStaff?->department ?? null;

        return view('topic_coverage.evaluation', compact(
            'teacherData', 'isManagement', 'isTeacher',
            'sessions', 'selectedSession', 'departments',
            'selectedDept', 'userDept', 'user'
        ));
    }

    // ── Index ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user        = Auth::user();
        $sessions    = AcademicSession::orderBy('name')->get();
        $classes     = SchoolClass::orderBy('name')->get();
        $subjects    = Subject::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        $query = LessonPlan::with(['session', 'subject', 'schoolClass', 'teacher']);

        if ($user->hasRole('Teacher')) {
            $query->where('teacher_id', $user->id);
        } elseif ($user->hasRole('HOD')) {
            $staff = Staff::where('user_id', $user->id)->first();
            if ($staff) {
                $query->whereHas('subject', fn($q) => $q->where('department_id', $staff->department_id));
            }
        }

        if ($request->filled('session_id'))    $query->where('academic_session_id', $request->session_id);
        if ($request->filled('class_id'))      $query->where('class_id', $request->class_id);
        if ($request->filled('subject_id'))    $query->where('subject_id', $request->subject_id);
        if ($request->filled('department_id')) {
            $query->whereHas('subject', fn($q) => $q->where('department_id', $request->department_id));
        }

        $plans = $query->latest()->get();
        $plans->each(fn($p) => $p->stats = $p->completionStats());

        return view('topic_coverage.index', compact('plans', 'sessions', 'classes', 'subjects', 'departments'));
    }

    // ── Create / Store ────────────────────────────────────────────────────
    public function create()
    {
        $user     = Auth::user();
        $sessions = AcademicSession::orderBy('name')->get();
        $classes  = SchoolClass::orderBy('name')->get();

        if ($user->hasRole('Teacher')) {
            // subject_class.teacher_id is a foreign key to staff.id, not users.id
            // (unlike LessonPlan.teacher_id above, which is a users.id).
            $staffId = optional(Staff::where('user_id', $user->id)->first())->id;
            $subjects = Subject::whereHas('classes', fn($q) => $q->where('teacher_id', $staffId))
                ->orderBy('name')->get();
        } else {
            $subjects = Subject::orderBy('name')->get();
        }

        return view('topic_coverage.create', compact('sessions', 'classes', 'subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'subject_id'          => 'required|exists:subjects,id',
            'class_id'            => 'required|exists:school_classes,id',
            'title'               => 'nullable|string|max:200',
            'description'         => 'nullable|string|max:1000',
        ]);

        $data['teacher_id'] = Auth::id();

        $plan = LessonPlan::firstOrCreate(
            [
                'academic_session_id' => $data['academic_session_id'],
                'subject_id'          => $data['subject_id'],
                'class_id'            => $data['class_id'],
                'teacher_id'          => $data['teacher_id'],
            ],
            [
                'title'       => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
            ]
        );

        return redirect()->route('topic-coverage.show', $plan)
            ->with('success', 'Topic coverage record created. Now add your topics and subtopics.');
    }

    // ── Show ──────────────────────────────────────────────────────────────
    public function show(LessonPlan $lessonPlan)
    {
        $lessonPlan->load(['session', 'subject', 'schoolClass', 'teacher', 'topics.subtopics.coveredBy']);
        $stats   = $lessonPlan->completionStats();
        $canEdit = $this->canManage($lessonPlan);

        return view('topic_coverage.show', compact('lessonPlan', 'stats', 'canEdit'));
    }

    // ── Update plan meta ──────────────────────────────────────────────────
    public function update(Request $request, LessonPlan $lessonPlan): JsonResponse
    {
        if (!$this->canManage($lessonPlan)) abort(403);

        $data = $request->validate([
            'title'       => 'nullable|string|max:200',
            'description' => 'nullable|string|max:1000',
        ]);

        $lessonPlan->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(LessonPlan $lessonPlan)
    {
        if (!$this->canManage($lessonPlan)) abort(403);
        $lessonPlan->delete();
        return redirect()->route('topic-coverage.index')->with('success', 'Topic coverage record deleted.');
    }

    // ── AJAX: Topics ──────────────────────────────────────────────────────
    public function storeTopic(Request $request, LessonPlan $lessonPlan): JsonResponse
    {
        if (!$this->canManage($lessonPlan)) abort(403);

        $data    = $request->validate(['title' => 'required|string|max:200']);
        $orderNo = $lessonPlan->topics()->max('order_no') + 1;

        $topic = $lessonPlan->topics()->create([
            'title'    => $data['title'],
            'order_no' => $orderNo,
        ]);

        return response()->json([
            'id'       => $topic->id,
            'title'    => $topic->title,
            'order_no' => $topic->order_no,
        ], 201);
    }

    public function updateTopic(Request $request, LessonTopic $topic): JsonResponse
    {
        if (!$this->canManage($topic->lessonPlan)) abort(403);

        $data = $request->validate(['title' => 'required|string|max:200']);
        $topic->update($data);

        return response()->json(['success' => true]);
    }

    public function destroyTopic(LessonTopic $topic): JsonResponse
    {
        if (!$this->canManage($topic->lessonPlan)) abort(403);
        $topic->delete();
        return response()->json(['success' => true]);
    }

    // ── AJAX: Subtopics ───────────────────────────────────────────────────
    public function storeSubtopic(Request $request, LessonTopic $topic): JsonResponse
    {
        if (!$this->canManage($topic->lessonPlan)) abort(403);

        $data    = $request->validate(['title' => 'required|string|max:200']);
        $orderNo = $topic->subtopics()->max('order_no') + 1;

        $sub = $topic->subtopics()->create([
            'title'    => $data['title'],
            'order_no' => $orderNo,
        ]);

        return response()->json([
            'id'    => $sub->id,
            'title' => $sub->title,
        ], 201);
    }

    public function updateSubtopic(Request $request, LessonSubtopic $subtopic): JsonResponse
    {
        if (!$this->canManage($subtopic->topic->lessonPlan)) abort(403);

        $data = $request->validate([
            'title' => 'nullable|string|max:200',
            'notes' => 'nullable|string|max:500',
        ]);
        $subtopic->update(array_filter($data, fn($v) => $v !== null));

        return response()->json(['success' => true]);
    }

    public function destroySubtopic(LessonSubtopic $subtopic): JsonResponse
    {
        if (!$this->canManage($subtopic->topic->lessonPlan)) abort(403);
        $subtopic->delete();
        return response()->json(['success' => true]);
    }

    // ── Lesson Plan Generator ─────────────────────────────────────────────
    public function generateSubtopicPlan(Request $request, LessonSubtopic $subtopic): JsonResponse
    {
        if (!$this->canManage($subtopic->topic->lessonPlan)) abort(403);

        $request->validate([
            'duration'       => 'required|integer|min:20|max:120',
            'num_students'   => 'nullable|integer|min:1|max:200',
            'entry_behavior' => 'nullable|string|max:600',
            'materials'      => 'nullable|string|max:600',
            'extra_notes'    => 'nullable|string|max:600',
        ]);

        $apiKey = config('services.deepseek.key', env('DEEPSEEK_API_KEY'));
        if (empty($apiKey)) {
            return response()->json(['error' => 'AI API key not configured. Add DEEPSEEK_API_KEY to your .env file.'], 503);
        }

        $plan    = $subtopic->topic->lessonPlan;
        $plan->load(['subject.department', 'schoolClass', 'session', 'teacher']);
        $topic   = $subtopic->topic;

        $teacherName = $plan->teacher
            ? ($plan->teacher->name ?? trim($plan->teacher->first_name . ' ' . $plan->teacher->last_name))
            : 'Unknown';

        $prompt = $this->buildLessonPlanPrompt([
            'subject'        => $plan->subject->name,
            'department'     => $plan->subject->department->name ?? '',
            'class'          => $plan->schoolClass->name,
            'year'           => $plan->session->name,
            'teacher'        => $teacherName,
            'topic'          => $topic->title,
            'subtopic'       => $subtopic->title,
            'duration'       => $request->duration,
            'num_students'   => $request->num_students ?? 'Not specified',
            'entry_behavior' => $request->entry_behavior ?? 'Not specified',
            'materials'      => $request->materials ?? 'Chalkboard, textbook',
            'extra_notes'    => $request->extra_notes ?? 'None',
        ]);

        $systemPrompt = <<<'SYS'
You are a Senior Curriculum Specialist and Master Teacher with 20+ years of experience in Tanzania's secondary education system. You hold deep expertise in:

1. NECTA 2023 NEW SYLLABUS — You know the complete Tanzania Institute of Education (TIE) revised Competency-Based Curriculum (CBC) for all secondary school subjects (Form 1–6), including every topic, subtopic, general objective, specific objective, and competency listed in the official 2023 syllabuses.

2. TIE OFFICIAL TEXTBOOKS — You know every TIE-published secondary school textbook by title, edition, and chapter structure:
   • Mathematics: "Secondary Basic Mathematics" Books 1–4 (TIE)
   • Biology: "Secondary Biology" Books 1–4 (TIE)
   • Chemistry: "Secondary Chemistry" Books 1–4 (TIE)
   • Physics: "Secondary Physics" Books 1–4 (TIE)
   • English: "Practical English for Secondary Schools" Books 1–4 (TIE)
   • Kiswahili: "Kiswahili kwa Sekondari" Books 1–4 (TIE)
   • History: "Secondary History" Books 1–4 (TIE)
   • Geography: "Secondary Geography" Books 1–4 (TIE)
   • Civics: "Secondary Civics" Books 1–4 (TIE)
   • Commerce/Business: "Secondary Commerce and Business Studies" Books 1–4 (TIE)
   • Computer Studies: "Secondary Computer Studies" Books 1–4 (TIE)
   • Agriculture: "Secondary Agriculture" Books 1–4 (TIE)
   • Home Economics, Fine Arts, Book Keeping — respective TIE series
   • Form 5–6 (A-Level): Subject-specific TIE Advanced Level textbooks

3. TANZANIA MoEST LESSON PLAN FORMAT — You produce lesson plans that exactly match the official Tanzania Ministry of Education, Science and Technology (MoEST) and TIE format required for school inspection and professional development.

4. NECTA EXAMINATION ALIGNMENT — You know CSEE (Form 4), ACSEE (Form 6), PSLE, and national examination patterns and can embed exam-focused activities and the type of questions NECTA regularly sets.

5. COMPETENCY-BASED TEACHING & LEARNING (CBTL) — Tanzania's 2023 curriculum demands outcomes-based teaching. Every lesson plan you write focuses on demonstrable competences, not just content delivery.

6. CROSS-CUTTING ISSUES — You integrate Tanzania's mandatory cross-cutting issues (Gender & Inclusive Education, HIV/AIDS, Environmental Sustainability, ICT, Financial Literacy, Human Rights) appropriately.

7. TANZANIAN CLASSROOM REALITIES — You design lessons suitable for typical Tanzanian secondary school conditions: large classes (30–60 students), limited equipment, chalkboard as primary tool, mixed-ability learners.

CRITICAL RULES:
- Always cite the exact NECTA 2023 syllabus topic/subtopic reference for the lesson content.
- Always recommend the specific TIE textbook title, book number, and chapter/topic relevant to this lesson.
- Write all learning objectives in the SMART format using Bloom's Taxonomy action verbs (remembering → creating hierarchy).
- Every teacher and student activity must be realistic, culturally appropriate, and achievable in a Tanzanian secondary school.
- Output ONLY clean Bootstrap 4-compatible HTML (no <!DOCTYPE>, no <html>/<head>/<body> tags). Use table.table.table-bordered, h5.font-weight-bold, ul/ol, p classes.
- Write in formal professional English appropriate for official Tanzania school records.
- Be thorough — this is the document the teacher will print and keep in their lesson plan file for inspection.
SYS;

        try {
            $response = Http::withToken($apiKey)
                ->timeout(90)
                ->post('https://api.deepseek.com/v1/chat/completions', [
                    'model'       => env('DEEPSEEK_MODEL', 'deepseek-chat'),
                    'temperature' => 0.3,
                    'max_tokens'  => 4000,
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('DeepSeek lesson plan error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'AI service returned an error. Please try again.'], 502);
            }

            $content = $response->json('choices.0.message.content', '');

            // Strip markdown code fences if AI wrapped in ```html ... ```
            $content = preg_replace('/^```(?:html)?\s*/i', '', trim($content));
            $content = preg_replace('/\s*```$/', '', $content);

            $subtopic->update(['lesson_plan_content' => $content]);

            return response()->json(['success' => true, 'content' => $content]);

        } catch (\Exception $e) {
            Log::error('Lesson plan generation failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to connect to AI service. Check your internet connection.'], 503);
        }
    }

    public function getSubtopicPlan(LessonSubtopic $subtopic): JsonResponse
    {
        if (!$this->canManage($subtopic->topic->lessonPlan)) abort(403);

        if (empty($subtopic->lesson_plan_content)) {
            return response()->json(['content' => null]);
        }

        return response()->json(['content' => $subtopic->lesson_plan_content]);
    }

    private function buildLessonPlanPrompt(array $ctx): string
    {
        $devTime   = (int)$ctx['duration'] - 15;
        $subject   = $ctx['subject'];
        $dept      = $ctx['department'];
        $class     = $ctx['class'];
        $year      = $ctx['year'];
        $teacher   = $ctx['teacher'];
        $topic     = $ctx['topic'];
        $subtopic  = $ctx['subtopic'];
        $duration  = $ctx['duration'];
        $students  = $ctx['num_students'];
        $entry     = $ctx['entry_behavior'];
        $materials = $ctx['materials'];
        $notes     = $ctx['extra_notes'];

        return <<<PROMPT
Produce a COMPLETE, PRINT-READY Tanzania MoEST/TIE standard lesson plan for the lesson below.
This document will be filed in the teacher's official lesson plan folder and reviewed during school inspection.
It must be 100% professional, thorough, and strictly aligned with the NECTA 2023 syllabus and TIE curriculum.

LESSON CONTEXT:
Subject         : $subject  (Department: $dept)
Class / Form    : $class
Academic Year   : $year
Teacher         : $teacher
Chapter / Topic : $topic
Sub-Topic       : $subtopic
Duration        : $duration minutes
No. of Students : $students
Entry Behavior  : $entry
Materials avail : $materials
Special notes   : $notes

OUTPUT: Bootstrap 4 HTML only — no html/head/body tags.
Use <h5 class="font-weight-bold text-uppercase mt-4 border-bottom pb-1"> for section headings.
Use <table class="table table-bordered table-sm"> for all tables.
Produce ALL 13 sections below in full — do not truncate or summarise any section.

SECTION: HEADER TABLE
Two-column table (Label | Value): School Name (blank), Subject, Class/Form, Topic, Sub-Topic,
Date (blank), Day (blank), Duration, Teacher's Name, Number of Students.

SECTION 1: SYLLABUS REFERENCE
State the exact NECTA 2023 $subject syllabus topic and sub-topic reference numbers and titles.
Then state: TIE Textbook: [exact full title], Book/Form [No.], Chapter [No.], Pages [range].
Use your accurate knowledge of the actual TIE $subject textbook for $class.

SECTION 2: GENERAL OBJECTIVES
Write 2-3 General Objectives exactly as stated (or as they should be stated) in the NECTA 2023 TIE
syllabus for Topic: "$topic". These are broad chapter-level outcomes this lesson contributes to.

SECTION 3: SPECIFIC OBJECTIVES / COMPETENCES
Write exactly 5 specific lesson competences, each formatted as:
"By the end of this lesson, students should be able to [Bloom's verb] [specific measurable outcome]."
Progress from lower-order (remember/understand) to higher-order (apply/analyse/evaluate).
All must be achievable within $duration minutes with $students students.

SECTION 4: CORE COMPETENCES (Tanzania CBC 2023)
Select 2-3 core competences from Tanzania CBC that this lesson develops. Choose from:
Communication & Language, Numeracy, Digital Literacy, Creativity & Innovation,
Critical Thinking & Problem Solving, Research Skills, Interpersonal & Self-Management, Citizenship.
For each, write one sentence explaining specifically HOW this lesson develops it.

SECTION 5: CROSS-CUTTING ISSUES
Select 1-2 cross-cutting issues from Tanzania's curriculum:
Gender & Inclusive Education, HIV/AIDS & Life Skills, Environmental Sustainability,
Financial Literacy, Human Rights & Ethics, ICT Integration.
For each, one sentence on how it is embedded in this lesson.

SECTION 6: ENTRY BEHAVIOR
4-5 sentences: specific prior knowledge/skills students need, referencing specific TIE syllabus
topics covered previously, and how the teacher will verify this at the start of the lesson.

SECTION 7: TEACHING / LEARNING MATERIALS
Two labelled sublists:
Main Resources: full TIE textbook title, chalkboard, chalk, exercise books.
Supplementary: charts, models, specimens, locally sourced materials realistic for a Tanzanian school.

SECTION 8: REFERENCE BOOKS
Bibliography format, 4-5 items:
1. Primary TIE textbook (full title, Book No., TIE Dar es Salaam, year, relevant chapter)
2. Teacher's TIE Syllabus / Guide document
3. NECTA past examination papers ($subject, CSEE 2019-2023)
4-5. Additional TIE-approved references for this subject and $class level.

SECTION 9: INTRODUCTION / SET INDUCTION (5 minutes)
5-7 sentences: how the teacher settles the class, a specific Tanzania-relevant real-world scenario
or question that creates curiosity, how it links to "$subtopic", transition into lesson development.

SECTION 10: LESSON DEVELOPMENT ($devTime minutes)
Table with columns: Step No. | Teacher Activities | Students Activities | Time (min)
Exactly 6 steps totalling $devTime minutes:
Step 1 (5 min): Review 2-3 specific questions checking entry behavior.
Steps 2-4 (main content): Teach "$subtopic" progressively. Each step must specify exact teacher
language/questions, specific board work, and meaningful active student responses (not just "listen/copy").
Step 5 (guided practice): Students solve problems or complete a specific activity.
Step 6 (consolidation): Teacher-led discussion addressing misconceptions.
Vary methods: exposition, Socratic questioning, pair/group work, demonstration, problem-solving.
All activities realistic for $students students in a Tanzanian secondary school.

SECTION 11: SUMMARY / CONCLUSION (5 minutes)
3-4 specific key takeaways from this lesson. A consolidating question for the whole class.
HOMEWORK: specific assignment referencing the exact TIE textbook exercise number and page.

SECTION 12: ASSESSMENT / EVALUATION
Exactly 5 NECTA-style questions:
Q1. Short answer, knowledge/recall, 1 mark.
Q2. Short answer, comprehension, 2 marks.
Q3. Application/problem-solving with real-life Tanzania context, 3 marks.
Q4. Structured question, 3 marks.
Q5. Higher-order (analysis/evaluation/synthesis), 4 marks.
Style must match actual NECTA CSEE/ACSEE past paper questions for $subject.

SECTION 13: TEACHER'S SELF-ASSESSMENT
Table: 2 columns (Reflection Question | Teacher's Response), 5 rows:
1. Were all specific objectives achieved? If not, which ones and why?
2. Which teaching methods/activities were most effective?
3. What difficulties did students face? What misconceptions arose?
4. What would I do differently if I taught this lesson again?
5. Any students needing follow-up support or enrichment?
Response cells: blank with style="height:45px;" for handwriting.

REQUIREMENTS:
- TIE textbook titles, chapters, and NECTA 2023 syllabus references must be ACCURATE.
- Content must be correct and appropriate for $class level in Tanzania.
- Do NOT include any commentary or text outside the lesson plan HTML.
- Complete all 13 sections in full — no truncation.
PROMPT;
    }

    public function toggleSubtopic(Request $request, LessonSubtopic $subtopic): JsonResponse
    {
        if (!$this->canManage($subtopic->topic->lessonPlan)) abort(403);

        $request->validate([
            'status'       => 'required|in:pending,covered',
            'date_covered' => 'nullable|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        $subtopic->update([
            'status'       => $request->status,
            'date_covered' => $request->status === 'covered' ? ($request->date_covered ?? now()->toDateString()) : null,
            'notes'        => $request->notes,
            'covered_by'   => $request->status === 'covered' ? Auth::id() : null,
        ]);

        $plan  = $subtopic->topic->lessonPlan;
        $stats = $plan->completionStats();

        return response()->json([
            'success'      => true,
            'status'       => $subtopic->status,
            'date_covered' => $subtopic->date_covered?->format('d M Y'),
            'stats'        => $stats,
        ]);
    }
}
