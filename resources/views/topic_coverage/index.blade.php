@extends('adminlte::page')

@section('title', 'Topic Coverage')

@section('content_header')
<div class="d-flex align-items-center justify-content-between flex-wrap">
    <div class="d-flex align-items-center">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm mr-3">
            <i class="fas fa-arrow-left mr-1"></i>Back
        </a>
        <h1 class="mb-0"><i class="fas fa-tasks text-primary mr-2"></i>Topic Coverage</h1>
    </div>
    @if(auth()->user()->hasAnyRole(['Admin', 'Academic', 'HOD', 'Teacher']))
    <a href="{{ route('topic-coverage.create') }}" class="btn btn-primary btn-sm mt-1 mt-md-0">
        <i class="fas fa-plus mr-1"></i>New Coverage Record
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
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-1"></i>Filter</h3></div>
    <div class="card-body">
        <form method="GET" action="{{ route('topic-coverage.index') }}">
            <div class="row align-items-end">
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="font-weight-bold">Academic Year</label>
                    <select name="session_id" class="form-control select2">
                        <option value="">— All Years —</option>
                        @foreach($sessions as $s)
                        <option value="{{ $s->id }}" {{ request('session_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="font-weight-bold">Class</label>
                    <select name="class_id" class="form-control select2">
                        <option value="">— All Classes —</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="font-weight-bold">Department</label>
                    <select name="department_id" class="form-control select2">
                        <option value="">— All Departments —</option>
                        @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <label class="font-weight-bold">Subject</label>
                    <select name="subject_id" class="form-control select2">
                        <option value="">— All Subjects —</option>
                        @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 mb-2 d-flex">
                    <button type="submit" class="btn btn-primary mr-2 flex-grow-1">
                        <i class="fas fa-search mr-1"></i>Filter
                    </button>
                    <a href="{{ route('topic-coverage.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        @if($plans->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fas fa-tasks fa-3x mb-3 text-light"></i>
            <p class="mb-0">No topic coverage records found. <a href="{{ route('topic-coverage.create') }}">Create one now.</a></p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="plans-table">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Teacher</th>
                        <th>Year</th>
                        <th>Topics</th>
                        <th>Coverage</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plans as $i => $plan)
                    @php $stats = $plan->stats; @endphp
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <strong>{{ $plan->subject->name }}</strong>
                            @if($plan->subject->department)
                            <br><small class="text-muted">{{ $plan->subject->department->name }}</small>
                            @endif
                        </td>
                        <td>{{ $plan->schoolClass->name }}</td>
                        <td>
                            {{ $plan->teacher->name ?? ($plan->teacher ? $plan->teacher->first_name . ' ' . $plan->teacher->last_name : '—') }}
                        </td>
                        <td>{{ $plan->session->name }}</td>
                        <td class="text-center">
                            <span class="badge badge-secondary">{{ $plan->topics->count() }} topics</span>
                        </td>
                        <td style="min-width:140px;">
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 mr-2" style="height:8px;">
                                    @php
                                        $pct   = $stats['pct'];
                                        $color = $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                                    @endphp
                                    <div class="progress-bar bg-{{ $color }}" style="width:{{ $pct }}%"></div>
                                </div>
                                <small class="font-weight-bold text-{{ $color }}">{{ $pct }}%</small>
                            </div>
                            <small class="text-muted">{{ $stats['covered'] }}/{{ $stats['total'] }} subtopics</small>
                        </td>
                        <td>
                            <a href="{{ route('topic-coverage.show', $plan) }}" class="btn btn-xs btn-primary" title="Open">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(auth()->user()->hasAnyRole(['Admin','Academic']) || $plan->teacher_id === auth()->id())
                            <form action="{{ route('topic-coverage.destroy', $plan) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this coverage record and all its topics/subtopics?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@stop

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet"/>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
    $('#plans-table').DataTable({ pageLength: 25, order: [] });
});
</script>
@endpush
