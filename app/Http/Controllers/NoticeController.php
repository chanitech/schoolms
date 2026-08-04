<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoticeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage notices')->except(['index']);
    }

    /** Full list — open to view for anyone (matches suggestion box's open-viewing pattern). */
    public function index()
    {
        $notices = Notice::with('poster')->orderByDesc('pinned')->latest()->paginate(20);
        return view('notices.index', compact('notices'));
    }

    public function create()
    {
        return view('notices.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:200',
            'body'       => 'required|string|max:3000',
            'audience'   => 'required|in:all,staff,guardians',
            'pinned'     => 'nullable|boolean',
            'expires_at' => 'nullable|date|after_or_equal:today',
        ]);

        Notice::create([
            'title'      => $data['title'],
            'body'       => $data['body'],
            'audience'   => $data['audience'],
            'pinned'     => $request->boolean('pinned'),
            'expires_at' => $data['expires_at'] ?? null,
            'posted_by'  => Auth::id(),
        ]);

        return redirect()->route('notices.index')->with('success', 'Notice posted.');
    }

    public function edit(Notice $notice)
    {
        return view('notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:200',
            'body'       => 'required|string|max:3000',
            'audience'   => 'required|in:all,staff,guardians',
            'pinned'     => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ]);

        $notice->update([
            'title'      => $data['title'],
            'body'       => $data['body'],
            'audience'   => $data['audience'],
            'pinned'     => $request->boolean('pinned'),
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return redirect()->route('notices.index')->with('success', 'Notice updated.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return back()->with('success', 'Notice removed.');
    }
}
