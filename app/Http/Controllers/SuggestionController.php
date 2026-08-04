<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use App\Models\User;
use App\Notifications\SuggestionRespondedNotification;
use App\Notifications\SuggestionSubmittedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuggestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage suggestions')->only(['manage', 'respond']);
    }

    private function baseRoute(): string
    {
        return Auth::user()->hasRole('guardian') ? 'guardian.suggestions' : 'suggestions';
    }

    /** My own submissions — open to any authenticated user. */
    public function index()
    {
        $mine = Suggestion::where('submitted_by', Auth::id())->latest()->get();
        $canManage = Auth::user()->can('manage suggestions');
        $baseRoute = $this->baseRoute();

        return view('suggestions.index', compact('mine', 'canManage', 'baseRoute'));
    }

    public function create()
    {
        $baseRoute = $this->baseRoute();
        return view('suggestions.create', compact('baseRoute'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category'     => 'required|in:suggestion,complaint,compliment,opinion,other',
            'subject'      => 'required|string|max:200',
            'message'      => 'required|string|max:3000',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $isAnonymous = $request->boolean('is_anonymous');

        $suggestion = Suggestion::create([
            'submitted_by'   => $isAnonymous ? null : $user->id,
            // Captured even when anonymous, purely for triage context
            // ("a parent raised this") — never enough to identify anyone.
            'submitter_role' => $user->hasRole('guardian') ? 'Guardian' : ($user->getRoleNames()->first() ?? 'Staff'),
            'is_anonymous'   => $isAnonymous,
            'category'       => $data['category'],
            'subject'        => $data['subject'],
            'message'        => $data['message'],
        ]);

        // Alert everyone who can actually act on it, resolved by permission
        // rather than a hardcoded role list — stays correct if a school
        // later delegates this to a different/custom role.
        $notify = new SuggestionSubmittedNotification($suggestion);
        User::permission('manage suggestions')->get()->each(fn($u) => $u->notify($notify));

        return redirect()->route($this->baseRoute() . '.index')
            ->with('success', $isAnonymous
                ? 'Thank you — your anonymous submission has been sent to the administration.'
                : 'Thank you — your submission has been sent to the administration.');
    }

    /** Administration inbox — every submission, gated by permission. */
    public function manage(Request $request)
    {
        $query = Suggestion::with(['submitter', 'responder'])->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('category', $request->category);

        $suggestions = $query->paginate(20)->withQueryString();
        $counts = Suggestion::selectRaw('status, COUNT(*) as cnt')->groupBy('status')->pluck('cnt', 'status');

        return view('suggestions.manage', compact('suggestions', 'counts'));
    }

    public function respond(Request $request, Suggestion $suggestion)
    {
        $data = $request->validate([
            'admin_response' => 'required|string|max:3000',
            'status'         => 'required|in:in_review,resolved,dismissed',
        ]);

        $suggestion->update([
            'admin_response' => $data['admin_response'],
            'status'         => $data['status'],
            'responded_by'   => Auth::id(),
            'responded_at'   => now(),
        ]);

        if (!$suggestion->is_anonymous && $suggestion->submitter) {
            $suggestion->submitter->notify(new SuggestionRespondedNotification($suggestion));
        }

        return back()->with('success', 'Response saved.');
    }
}
