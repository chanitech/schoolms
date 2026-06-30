<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Document;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:upload documents')->only(['create', 'store']);
        $this->middleware('permission:delete documents')->only(['destroy']);
        $this->middleware('permission:upload documents')->only(['toggleFeatured', 'toggleRestricted']);
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser  = Auth::user();
        $isStaff   = $authUser->hasAnyRole(['Admin', 'Academic', 'HOD', 'Teacher', 'HR', 'Principal']);

        $query = Document::with(['uploader', 'academicSession', 'schoolClass'])->latest();

        // Non-staff can't see restricted documents
        if (!$isStaff) {
            $query->where('is_restricted', false);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('session_id')) {
            $query->where('academic_session_id', $request->session_id);
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('subject')) {
            $query->where('subject', 'like', '%' . $request->subject . '%');
        }
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('original_name', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest'    => $query->oldest(),
            'title'     => $query->orderBy('title'),
            'downloads' => $query->orderByDesc('download_count'),
            'size'      => $query->orderByDesc('file_size'),
            default     => $query->latest(),
        };

        $featured        = Document::featured()->when(!$isStaff, fn($q) => $q->where('is_restricted', false))->latest()->get();
        $documents       = $query->paginate(12)->withQueryString();
        $categories      = Document::categories();
        $languages       = Document::languages();
        $academicSessions = AcademicSession::orderByDesc('id')->get();
        $classes         = SchoolClass::orderBy('name')->get();

        return view('documents.index', compact(
            'documents', 'featured', 'categories', 'languages',
            'academicSessions', 'classes', 'isStaff'
        ));
    }

    public function create()
    {
        $categories      = Document::categories();
        $languages       = Document::languages();
        $academicSessions = AcademicSession::orderByDesc('id')->get();
        $classes         = SchoolClass::orderBy('name')->get();
        $subjects        = Subject::orderBy('name')->pluck('name')->unique()->values();

        return view('documents.create', compact('categories', 'languages', 'academicSessions', 'classes', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string|max:2000',
            'category'            => 'required|in:' . implode(',', array_keys(Document::categories())),
            'academic_session_id' => 'nullable|exists:academic_sessions,id',
            'class_id'            => 'nullable|exists:school_classes,id',
            'subject'             => 'nullable|string|max:100',
            'language'            => 'nullable|string|max:50',
            'document_date'       => 'nullable|date',
            'author'              => 'nullable|string|max:150',
            'tags'                => 'nullable|string',
            'is_featured'         => 'nullable|boolean',
            'is_restricted'       => 'nullable|boolean',
            'file'                => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar,txt',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Parse comma-separated tags into array
        $tags = $request->filled('tags')
            ? array_filter(array_map('trim', explode(',', $request->tags)))
            : null;

        Document::create([
            'title'               => $request->title,
            'description'         => $request->description,
            'category'            => $request->category,
            'academic_session_id' => $request->academic_session_id,
            'class_id'            => $request->class_id,
            'subject'             => $request->subject,
            'language'            => $request->language ?? 'English',
            'document_date'       => $request->document_date,
            'author'              => $request->author,
            'tags'                => $tags ?: null,
            'is_featured'         => $request->boolean('is_featured'),
            'is_restricted'       => $request->boolean('is_restricted'),
            'file_path'           => $path,
            'original_name'       => $file->getClientOriginalName(),
            'file_size'           => $file->getSize(),
            'mime_type'           => $file->getMimeType(),
            'uploaded_by'         => $user->id,
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'Document "' . $request->title . '" uploaded successfully.');
    }

    public function show(Document $document)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $isStaff  = $authUser->hasAnyRole(['Admin', 'Academic', 'HOD', 'Teacher', 'HR', 'Principal']);

        if ($document->is_restricted && !$isStaff) {
            abort(403, 'This document is restricted to staff only.');
        }

        $related = Document::where('category', $document->category)
            ->where('id', '!=', $document->id)
            ->when(!$isStaff, fn($q) => $q->where('is_restricted', false))
            ->latest()->limit(5)->get();

        return view('documents.show', compact('document', 'related', 'isStaff'));
    }

    public function download(Document $document)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $isStaff  = $authUser->hasAnyRole(['Admin', 'Academic', 'HOD', 'Teacher', 'HR', 'Principal']);

        if ($document->is_restricted && !$isStaff) {
            abort(403, 'This document is restricted to staff only.');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        $document->increment('download_count');

        $absolutePath = Storage::disk('public')->path($document->file_path);
        return response()->download($absolutePath, $document->original_name);
    }

    public function toggleFeatured(Document $document)
    {
        $document->update(['is_featured' => !$document->is_featured]);
        $status = $document->is_featured ? 'pinned as featured' : 'unpinned';
        return back()->with('success', "\"$document->title\" $status.");
    }

    public function toggleRestricted(Document $document)
    {
        $document->update(['is_restricted' => !$document->is_restricted]);
        $status = $document->is_restricted ? 'restricted to staff only' : 'visible to all users';
        return back()->with('success', "\"$document->title\" is now $status.");
    }

    public function destroy(Document $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }
}
