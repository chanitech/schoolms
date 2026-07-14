<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\AcademicSession;
use App\Models\User;
use App\Notifications\ExamStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view exams')->only(['index']);
        $this->middleware('permission:create exams')->only(['create', 'store']);
        $this->middleware('permission:edit exams')->only(['edit', 'update']);
        $this->middleware('permission:delete exams')->only(['destroy']);
    }

    /**
     * Display a listing of exams.
     */
    public function index(Request $request)
    {
        $query = Exam::with('academicSession');

        // Optional search by name or term
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('term', 'like', "%{$search}%")
                  ->orWhereHas('academicSession', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $exams = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('exams.index', compact('exams'));
    }

    /**
     * Show form to create a new exam.
     */
    public function create()
    {
        $sessions = AcademicSession::all();
        return view('exams.create', compact('sessions'));
    }

    /**
     * Store a newly created exam in database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'term' => 'required|string|max:50',
            'academic_session_id' => 'required|exists:academic_sessions,id',
        ]);

        $data = $request->only('name', 'term', 'academic_session_id');

        // Handle checkboxes
        $data['include_in_term_final'] = $request->has('include_in_term_final');
        $data['include_in_year_final'] = $request->has('include_in_year_final');
        $data['is_terminal_exam'] = $request->has('is_terminal_exam');
        $data['is_annual_exam'] = $request->has('is_annual_exam'); // new

        Exam::create($data);

        return redirect()->route('exams.index')->with('success', 'Exam created successfully.');
    }

    /**
     * Show form to edit an existing exam.
     */
    public function edit(Exam $exam)
    {
        if ($exam->isPublished()) {
            return redirect()->route('exams.index')
                ->with('error', 'Published exams are locked. Unpublish first to edit.');
        }

        $sessions = AcademicSession::all();
        return view('exams.edit', compact('exam', 'sessions'));
    }

    /**
     * Update an existing exam.
     */
    public function update(Request $request, Exam $exam)
    {
        if ($exam->isPublished()) {
            return redirect()->route('exams.index')
                ->with('error', 'Published exams are locked. Unpublish first to edit.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'term' => 'required|string|max:50',
            'academic_session_id' => 'required|exists:academic_sessions,id',
        ]);

        $data = $request->only('name', 'term', 'academic_session_id');

        // Handle checkboxes
        $data['include_in_term_final'] = $request->has('include_in_term_final');
        $data['include_in_year_final'] = $request->has('include_in_year_final');
        $data['is_terminal_exam'] = $request->has('is_terminal_exam');
        $data['is_annual_exam'] = $request->has('is_annual_exam'); // new

        $exam->update($data);

        return redirect()->route('exams.index')->with('success', 'Exam updated successfully.');
    }

    /**
     * Delete an exam.
     */
    public function destroy(Exam $exam)
    {
        // Match the UI rule: only draft exams can be deleted.
        if (!$exam->isDraft()) {
            return redirect()->route('exams.index')
                ->with('error', 'Only draft exams can be deleted.');
        }

        $exam->delete();
        return redirect()->route('exams.index')->with('success', 'Exam deleted successfully.');
    }

    // ── Step 1 → 2: Academic/HOD marks results as reviewed ────────────────
    public function review(Exam $exam)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->hasAnyRole(['Admin', 'Academic', 'HOD']), 403);
        abort_unless($exam->isDraft(), 403, 'Only draft exams can be submitted for review.');

        $exam->update([
            'status'      => 'reviewed',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Notify Principal and Admin that exam is ready to publish
        $notify = new ExamStatusChanged($exam, 'submitted_for_review', $user->name);
        User::role(['Admin', 'Principal'])->where('id', '!=', Auth::id())->each(
            fn($u) => $u->notify($notify)
        );

        return back()->with('success', "Results for \"{$exam->name}\" submitted for principal approval.");
    }

    // ── Step 2 → 3: Principal/Admin publishes results ─────────────────────
    public function publish(Exam $exam)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->hasAnyRole(['Admin', 'Principal']), 403);
        abort_unless($exam->isReviewed(), 403, 'Only reviewed exams can be published.');

        $exam->update([
            'status'       => 'published',
            'published_by' => Auth::id(),
            'published_at' => now(),
        ]);

        // Notify all staff that results are live (including the publisher as confirmation)
        $notify = new ExamStatusChanged($exam, 'published', $user->name);
        User::role(['Admin', 'Principal', 'Academic', 'HOD', 'Teacher'])->each(
            fn($u) => $u->notify($notify)
        );

        return back()->with('success', "Results for \"{$exam->name}\" are now published and visible to parents.");
    }

    // ── Unpublish (Admin only — sends back to reviewed) ───────────────────
    public function unpublish(Exam $exam)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->hasAnyRole(['Admin']), 403);
        abort_unless($exam->isPublished(), 403, 'Only published exams can be unpublished.');

        $exam->update([
            'status'       => 'reviewed',
            'published_by' => null,
            'published_at' => null,
        ]);

        // Notify Academic and HOD
        $notify = new ExamStatusChanged($exam, 'unpublished', $user->name);
        User::role(['Academic', 'HOD'])->where('id', '!=', Auth::id())->each(
            fn($u) => $u->notify($notify)
        );

        return back()->with('success', "Results for \"{$exam->name}\" have been unpublished.");
    }

    // ── Reject review (Admin/Academic — sends back to draft) ──────────────
    public function rejectReview(Exam $exam)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->hasAnyRole(['Admin', 'Academic']), 403);
        abort_unless($exam->isReviewed(), 403, 'Only reviewed exams can be rejected back to draft.');

        // Notify the reviewer (person who submitted) before clearing reviewed_by
        if ($exam->reviewed_by && $exam->reviewed_by !== Auth::id()) {
            $reviewer = User::find($exam->reviewed_by);
            $reviewer?->notify(new ExamStatusChanged($exam, 'rejected', $user->name));
        }

        $exam->update([
            'status'      => 'draft',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        return back()->with('success', "Results for \"{$exam->name}\" sent back to draft for corrections.");
    }

    /**
     * AJAX: Get exams for a specific academic session (for dynamic dropdown in marks form)
     */
    public function getExamsBySession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
        ]);

        $exams = Exam::where('academic_session_id', $request->session_id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name']);

        return response()->json($exams);
    }
}