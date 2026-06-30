@extends('adminlte::page')
@section('title', 'Exams')

@push('css')
<style>
.workflow-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    border-radius: 20px; padding: .25rem .75rem; font-size: .72rem; font-weight: 700;
}
.wf-draft     { background:#f0f0f5; color:#666; }
.wf-reviewed  { background:#fff8e1; color:#856404; border:1px solid #ffc107; }
.wf-published { background:#d4edda; color:#155724; border:1px solid #28a745; }

.step-bar { display:flex; align-items:center; gap:0; margin-bottom:1.2rem; }
.step-item { display:flex; align-items:center; gap:.4rem; font-size:.78rem; font-weight:600; }
.step-dot { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:800; flex-shrink:0; }
.step-dot.done    { background:#28a745; color:#fff; }
.step-dot.active  { background:#ffc107; color:#333; }
.step-dot.pending { background:#e9ecef; color:#aaa; }
.step-line { flex:1; height:2px; background:#e9ecef; min-width:30px; }
.step-line.done { background:#28a745; }
.exam-card { border-radius:12px; border:1.5px solid #e3e6f0; box-shadow:0 2px 10px rgba(0,0,0,.05); margin-bottom:.8rem; overflow:hidden; }
.exam-card .card-left { width:5px; flex-shrink:0; }
.exam-card.status-draft     .card-left { background:#dee2e6; }
.exam-card.status-reviewed  .card-left { background:#ffc107; }
.exam-card.status-published .card-left { background:#28a745; }
.action-btn { border-radius:8px; font-size:.78rem; font-weight:600; padding:.3rem .75rem; }
</style>
@endpush

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
    <div>
        <h1 class="m-0 font-weight-bold" style="font-size:1.4rem">
            <i class="fas fa-file-alt mr-2" style="color:#4e73df"></i>Exams & Result Publishing
        </h1>
        <p class="text-muted mb-0" style="font-size:.82rem">Manage exams and control result visibility for parents</p>
    </div>
    @can('create exams')
    <a href="{{ route('exams.create') }}" class="btn btn-primary btn-sm" style="border-radius:9px;font-weight:600">
        <i class="fas fa-plus mr-1"></i>New Exam
    </a>
    @endcan
</div>
@endsection

@section('content')
<div class="container-fluid">

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:10px">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:10px">
    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
</div>
@endif

{{-- Workflow legend ──────────────────────────────────────────────────────── --}}
<div class="card mb-3" style="border-radius:12px;border:1.5px solid #e3e6f0">
    <div class="card-body py-3">
        <div class="step-bar">
            <div class="step-item">
                <div class="step-dot done">1</div>
                <div>
                    <div>Draft</div>
                    <div style="font-size:.68rem;color:#888;font-weight:400">Marks entered</div>
                </div>
            </div>
            <div class="step-line done"></div>
            <div class="step-item">
                <div class="step-dot active">2</div>
                <div>
                    <div>Reviewed</div>
                    <div style="font-size:.68rem;color:#888;font-weight:400">HOD / Academic verified</div>
                </div>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-dot pending">3</div>
                <div>
                    <div>Published</div>
                    <div style="font-size:.68rem;color:#888;font-weight:400">Visible to parents & reports</div>
                </div>
            </div>
        </div>
        <p class="text-muted mb-0" style="font-size:.78rem">
            <i class="fas fa-info-circle mr-1 text-info"></i>
            Results are only visible to parents and available in reports once the exam is <strong>Published</strong>.
            HOD / Academic → Review → Principal / Admin → Publish.
        </p>
    </div>
</div>

{{-- Exams list ─────────────────────────────────────────────────────────── --}}
@forelse($exams as $exam)
<div class="exam-card d-flex status-{{ $exam->status }}">
    <div class="card-left"></div>
    <div class="flex-grow-1 p-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.5rem">

            {{-- Info ──────────────────────────────────────────────────── --}}
            <div>
                <div class="d-flex align-items-center" style="gap:.5rem">
                    <strong style="font-size:.95rem">{{ $exam->name }}</strong>
                    <span class="workflow-badge wf-{{ $exam->status }}">
                        @if($exam->isDraft())     <i class="fas fa-pencil-alt"></i> Draft
                        @elseif($exam->isReviewed()) <i class="fas fa-search"></i> Reviewed
                        @else                        <i class="fas fa-check-circle"></i> Published
                        @endif
                    </span>
                </div>
                <div class="text-muted" style="font-size:.78rem;margin-top:2px">
                    {{ $exam->academicSession->name ?? '—' }}
                    &nbsp;·&nbsp; Term {{ $exam->term }}
                    @if($exam->is_terminal_exam) &nbsp;·&nbsp; <span class="badge badge-danger" style="font-size:.65rem">Terminal</span> @endif
                    @if($exam->is_annual_exam)   &nbsp;·&nbsp; <span class="badge badge-info"   style="font-size:.65rem">Annual</span>   @endif
                </div>
                @if($exam->isReviewed())
                <div style="font-size:.72rem;color:#856404;margin-top:3px">
                    <i class="fas fa-user-check mr-1"></i>Reviewed by {{ $exam->reviewer->name ?? '—' }}
                    · {{ $exam->reviewed_at?->format('d M Y H:i') }}
                </div>
                @endif
                @if($exam->isPublished())
                <div style="font-size:.72rem;color:#155724;margin-top:3px">
                    <i class="fas fa-globe mr-1"></i>Published by {{ $exam->publisher->name ?? '—' }}
                    · {{ $exam->published_at?->format('d M Y H:i') }}
                </div>
                @endif
            </div>

            {{-- Actions ────────────────────────────────────────────────── --}}
            <div class="d-flex flex-wrap align-items-center" style="gap:.4rem">

                {{-- Step 1→2: Submit for Review (Academic/HOD/Admin, only when draft) --}}
                @if($exam->isDraft() && auth()->user()->hasAnyRole(['Admin','Academic','HOD']))
                <form action="{{ route('exams.review', $exam) }}" method="POST">
                    @csrf
                    <button class="btn btn-warning action-btn" title="Submit for principal approval">
                        <i class="fas fa-search mr-1"></i>Submit for Review
                    </button>
                </form>
                @endif

                {{-- Step 2→3: Publish (Admin/Principal only, only when reviewed) --}}
                @if($exam->isReviewed() && auth()->user()->hasAnyRole(['Admin','Principal']))
                <form action="{{ route('exams.publish', $exam) }}" method="POST">
                    @csrf
                    <button class="btn btn-success action-btn" title="Publish results to parents">
                        <i class="fas fa-globe mr-1"></i>Publish Results
                    </button>
                </form>
                @endif

                {{-- Reject back to draft (Admin/Academic, only when reviewed) --}}
                @if($exam->isReviewed() && auth()->user()->hasAnyRole(['Admin','Academic']))
                <form action="{{ route('exams.reject-review', $exam) }}" method="POST"
                      onsubmit="return confirm('Send back to draft for corrections?')">
                    @csrf
                    <button class="btn btn-outline-secondary action-btn">
                        <i class="fas fa-undo mr-1"></i>Reject
                    </button>
                </form>
                @endif

                {{-- Unpublish (Admin only) --}}
                @if($exam->isPublished() && auth()->user()->hasRole('Admin'))
                <form action="{{ route('exams.unpublish', $exam) }}" method="POST"
                      onsubmit="return confirm('Unpublish and hide results from parents?')">
                    @csrf
                    <button class="btn btn-outline-warning action-btn">
                        <i class="fas fa-eye-slash mr-1"></i>Unpublish
                    </button>
                </form>
                @endif

                {{-- Edit (only when draft) --}}
                @can('edit exams')
                @if($exam->isDraft())
                <a href="{{ route('exams.edit', $exam) }}" class="btn btn-outline-primary action-btn">
                    <i class="fas fa-edit"></i>
                </a>
                @endif
                @endcan

                {{-- Delete (only when draft) --}}
                @can('delete exams')
                @if($exam->isDraft())
                <form action="{{ route('exams.destroy', $exam) }}" method="POST"
                      onsubmit="return confirm('Permanently delete this exam?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger action-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
                @endif
                @endcan
            </div>
        </div>
    </div>
</div>
@empty
<div class="text-center py-5 text-muted">
    <i class="fas fa-file-alt fa-2x d-block mb-2" style="opacity:.3"></i>
    No exams found. <a href="{{ route('exams.create') }}">Create the first one.</a>
</div>
@endforelse

@if($exams->hasPages())
<div class="mt-3">{{ $exams->links() }}</div>
@endif

</div>
@endsection
