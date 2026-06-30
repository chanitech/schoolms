@extends('adminlte::page')

@section('title', 'Timetables')

@section('content_header')
<div class="d-flex align-items-center justify-content-between flex-wrap">
    <div class="d-flex align-items-center">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm mr-3">
            <i class="fas fa-arrow-left mr-1"></i>Back
        </a>
        <h1 class="mb-0"><i class="fas fa-calendar-week text-primary mr-2"></i>Timetables</h1>
    </div>
    @if(auth()->user()->hasAnyRole(['Admin', 'Academic']))
    <a href="{{ route('timetables.create') }}" class="btn btn-primary btn-sm mt-1 mt-md-0">
        <i class="fas fa-plus mr-1"></i>New Timetable
    </a>
    @endif
</div>
@stop

@section('content')

@foreach(['success','warning','error'] as $type)
@if(session($type))
<div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session($type) }}
</div>
@endif
@endforeach

{{-- Filters --}}
<div class="card card-outline card-primary mb-3">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-1"></i>Filter</h3></div>
    <div class="card-body pb-0">
        <form method="GET" action="{{ route('timetables.index') }}" class="form-row align-items-end">
            <div class="form-group col-md-3">
                <label>Type</label>
                <select name="type" class="form-control form-control-sm">
                    <option value="">All Types</option>
                    <option value="class"  {{ request('type') === 'class'  ? 'selected' : '' }}>Class Routine</option>
                    <option value="exam"   {{ request('type') === 'exam'   ? 'selected' : '' }}>Exam Timetable</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    <option value="draft"          {{ request('status') === 'draft'          ? 'selected' : '' }}>Draft</option>
                    <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                    <option value="published"      {{ request('status') === 'published'      ? 'selected' : '' }}>Published</option>
                    <option value="rejected"       {{ request('status') === 'rejected'       ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>Academic Session</label>
                <select name="session_id" class="form-control form-control-sm">
                    <option value="">All Sessions</option>
                    @foreach($sessions as $s)
                    <option value="{{ $s->id }}" {{ request('session_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <button class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-search mr-1"></i>Search
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Timetable list --}}
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-1"></i>All Timetables</h3></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Session</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Published</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timetables as $t)
                    <tr>
                        <td>{{ $loop->iteration + ($timetables->currentPage() - 1) * $timetables->perPage() }}</td>
                        <td>
                            <a href="{{ route('timetables.show', $t) }}" class="font-weight-bold">
                                {{ $t->title }}
                            </a>
                        </td>
                        <td>
                            @if($t->type === 'class')
                                <span class="badge badge-info"><i class="fas fa-chalkboard mr-1"></i>Class Routine</span>
                            @else
                                <span class="badge badge-warning"><i class="fas fa-file-alt mr-1"></i>Exam</span>
                            @endif
                        </td>
                        <td>{{ $t->session?->name ?? '—' }}</td>
                        <td>{!! $t->statusBadge() !!}</td>
                        <td>{{ $t->creator?->name ?? ($t->creator ? $t->creator->first_name . ' ' . $t->creator->last_name : '—') }}</td>
                        <td>{{ $t->published_at ? $t->published_at->format('d M Y') : '—' }}</td>
                        <td>
                            <a href="{{ route('timetables.show', $t) }}" class="btn btn-xs btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(auth()->user()->hasAnyRole(['Admin', 'Academic']))
                            <form method="POST" action="{{ route('timetables.destroy', $t) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this timetable?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                            No timetables found. <a href="{{ route('timetables.create') }}">Create one now.</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($timetables->hasPages())
    <div class="card-footer">{{ $timetables->links() }}</div>
    @endif
</div>

@stop

@section('css')
<style>
.badge { font-size: 0.78rem; }
</style>
@stop
