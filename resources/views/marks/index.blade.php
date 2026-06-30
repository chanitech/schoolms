@extends('adminlte::page')

@section('title', 'Marks List')

@section('content_header')
    <h1>Marks List</h1>
@stop

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {{ session('error') }}
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Marks</span>
        <a href="{{ route('marks.create') }}" class="btn btn-success btn-sm">Add Mark</a>
    </div>
    <div class="card-body">

        {{-- Filters --}}
        <form method="GET" action="{{ route('marks.index') }}" class="mb-3 row">
            <div class="col-md-3">
                <label>Academic Session</label>
                <select name="academic_session_id" id="filter_session" class="form-control">
                    <option value="">All Sessions</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ request('academic_session_id') == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Class</label>
                <select name="class_id" id="filter_class" class="form-control">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Department</label>
                <select name="department_id" id="filter_department" class="form-control">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Subject</label>
                <select name="subject_id" id="filter_subject" class="form-control">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
                @if(auth()->user()->hasRole('Teacher'))
                    <small class="text-muted">Only your assigned subjects are shown</small>
                @endif
            </div>

            <div class="col-md-3 mt-3">
                <label>Exam</label>
                <select name="exam_id" id="filter_exam" class="form-control">
                    <option value="">All Exams</option>
                    @php
                        $exams = \App\Models\Exam::when(request('academic_session_id'), function($q) {
                            return $q->where('academic_session_id', request('academic_session_id'));
                        })->get();
                    @endphp
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                            {{ $exam->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mt-4">
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('marks.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        {{-- Professional Summary --}}
        @if(isset($stats) && $stats && $stats['total_students'] > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>
                            Performance Summary
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box bg-gradient-info">
                                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Students</span>
                                        <span class="info-box-number">{{ $stats['total_students'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box bg-gradient-success">
                                    <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Average Mark</span>
                                        <span class="info-box-number">{{ number_format($stats['average_mark'], 2) }}%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box bg-gradient-warning">
                                    <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Highest Mark</span>
                                        <span class="info-box-number">{{ $stats['highest_mark'] }}%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box bg-gradient-danger">
                                    <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Lowest Mark</span>
                                        <span class="info-box-number">{{ $stats['lowest_mark'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Grade distribution --}}
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5 class="text-center">Grade Distribution</h5>
                                <div class="row">
                                    @foreach($stats['grade_counts'] as $grade => $count)
                                        @php
                                            $percentage = $stats['total_students'] ? round(($count / $stats['total_students']) * 100, 1) : 0;
                                            $color = match(true) {
                                                str_contains($grade, 'A') => 'success',
                                                str_contains($grade, 'B') => 'primary',
                                                str_contains($grade, 'C') => 'info',
                                                str_contains($grade, 'D') => 'warning',
                                                default => 'danger',
                                            };
                                        @endphp
                                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                                            <div class="small-box bg-{{ $color }}">
                                                <div class="inner">
                                                    <h3>{{ $count }}</h3>
                                                    <p>{{ $grade }}</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-star"></i>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-{{ $color }}" style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <div class="small-box-footer">
                                                    {{ $percentage }}% of students
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Passing rate and top performer --}}
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="info-box bg-gradient-secondary">
                                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Passing Rate (≥50%)</span>
                                        <span class="info-box-number">{{ $stats['passing_count'] }} / {{ $stats['total_students'] }} ({{ $stats['passing_percentage'] }}%)</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: {{ $stats['passing_percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-gradient-primary">
                                    <span class="info-box-icon"><i class="fas fa-trophy"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Top Performer</span>
                                        <span class="info-box-number">
                                            @php
                                                $topStudentName = 'N/A';
                                                $topMark = 0;
                                                $topGrade = 'N/A';
                                                
                                                if(isset($stats['ranked_marks']) && $stats['ranked_marks']->count() > 0) {
                                                    $topMarkRecord = $stats['ranked_marks']->first();
                                                    if($topMarkRecord && isset($topMarkRecord->student)) {
                                                        $topStudentName = trim(($topMarkRecord->student->first_name ?? '') . ' ' . ($topMarkRecord->student->last_name ?? ''));
                                                        $topMark = $topMarkRecord->mark ?? 0;
                                                        
                                                        $gradeModel = \App\Models\Grade::where('min_mark', '<=', $topMark)
                                                            ->where('max_mark', '>=', $topMark)
                                                            ->first();
                                                        $topGrade = $gradeModel ? $gradeModel->name : 'N/A';
                                                    }
                                                }
                                            @endphp
                                            @if($topStudentName != 'N/A')
                                                {{ $topStudentName }}
                                                <small>({{ $topMark }}%)</small>
                                                <br>
                                                <span class="info-box-text">Best Grade: {{ $topGrade }}</span>
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Marks Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Department</th>
                        <th>Subject</th>
                        <th>Exam</th>
                        <th>Mark</th>
                        <th>Grade</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($marks as $mark)
                        <tr>
                            <td>{{ $mark->rank ?? '-' }}</td>
                            <td>{{ $mark->student->first_name ?? '-' }} {{ $mark->student->last_name ?? '-' }}</td>
                            <td>{{ $mark->student->schoolClass->name ?? '-' }}</td>
                            <td>{{ $mark->subject->department->name ?? '-' }}</td>
                            <td>{{ $mark->subject->name ?? '-' }}</td>
                            <td>{{ $mark->exam->name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $mark->mark >= 50 ? 'badge-success' : ($mark->mark >= 30 ? 'badge-warning' : 'badge-danger') }}" style="font-size:14px; padding:5px 10px;">
                                    {{ $mark->mark }}
                                </span>
                            </td>
                            <td>{{ $mark->grade->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('marks.edit', $mark->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('marks.destroy', $mark->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this mark?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i> No marks found. Please adjust your filters or add marks.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modern Pagination with Filter Preservation --}}
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-4 pt-2">
            <div class="text-muted mb-2 mb-sm-0">
                <small>
                    <i class="fas fa-table mr-1"></i>
                    Showing <strong>{{ $marks->firstItem() ?? 0 }}</strong> to <strong>{{ $marks->lastItem() ?? 0 }}</strong> of <strong>{{ $marks->total() }}</strong> results
                </small>
            </div>
            <div>
                @if ($marks->hasPages())
                    <nav aria-label="Pagination Navigation">
                        <ul class="pagination pagination-sm m-0">
                            {{-- Previous Page Link --}}
                            @if ($marks->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $marks->appends(request()->query())->previousPageUrl() }}" rel="prev">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @php
                                $start = max(1, $marks->currentPage() - 2);
                                $end = min($start + 4, $marks->lastPage());
                                if ($end - $start < 4 && $marks->lastPage() > 5) {
                                    $start = max(1, $marks->lastPage() - 4);
                                    $end = $marks->lastPage();
                                }
                            @endphp

                            @if ($start > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $marks->appends(request()->query())->url(1) }}">1</a>
                                </li>
                                @if ($start > 2)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $marks->currentPage())
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link">{{ $i }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $marks->appends(request()->query())->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endif
                            @endfor

                            @if ($end < $marks->lastPage())
                                @if ($end < $marks->lastPage() - 1)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ $marks->appends(request()->query())->url($marks->lastPage()) }}">{{ $marks->lastPage() }}</a>
                                </li>
                            @endif

                            {{-- Next Page Link --}}
                            @if ($marks->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $marks->appends(request()->query())->nextPageUrl() }}" rel="next">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Modern Pagination Styles */
    .pagination {
        gap: 4px;
    }
    .page-link {
        border-radius: 8px !important;
        margin: 0 2px;
        color: #1F7A63;
        border: 1px solid #e0e0e0;
        transition: all 0.2s ease;
        padding: 6px 12px;
    }
    .page-link:hover {
        background-color: #1F7A63;
        border-color: #1F7A63;
        color: white;
        transform: translateY(-1px);
    }
    .page-item.active .page-link {
        background-color: #1F7A63;
        border-color: #1F7A63;
        box-shadow: 0 2px 4px rgba(31, 122, 99, 0.3);
    }
    .page-item.disabled .page-link {
        color: #adb5bd;
        background-color: #f8f9fa;
        border-color: #dee2e6;
        cursor: not-allowed;
    }
    .page-item:first-child .page-link,
    .page-item:last-child .page-link {
        border-radius: 8px !important;
    }
    /* Table hover effect */
    .table-hover tbody tr:hover {
        background-color: rgba(31, 122, 99, 0.05);
    }
    /* Badge styles */
    .badge-success {
        background-color: #28a745;
        color: white;
    }
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    .badge-danger {
        background-color: #dc3545;
        color: white;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Update subjects dynamically when department changes
    $('#filter_department').change(function() {
        let department_id = $(this).val();
        let url = "{{ route('marks.subjects.by.department') }}";

        $.ajax({
            url: url,
            type: 'GET',
            data: { department_id: department_id },
            success: function(data) {
                let subjectSelect = $('#filter_subject');
                subjectSelect.empty();
                subjectSelect.append('<option value="">All Subjects</option>');

                data.forEach(subject => {
                    @if(auth()->user()->hasRole('Teacher'))
                        if(subject.classes.length === 0) return;
                    @endif
                    subjectSelect.append(`<option value="${subject.id}">${subject.name}</option>`);
                });
                
                let currentSubject = "{{ request('subject_id') }}";
                if(currentSubject) {
                    subjectSelect.val(currentSubject);
                }
            },
            error: function() {
                alert('Could not fetch subjects for this department.');
            }
        });
    });

    // Update exams dynamically when academic session changes
    $('#filter_session').change(function() {
        let session_id = $(this).val();
        let examSelect = $('#filter_exam');
        
        if(session_id) {
            examSelect.html('<option value="">Loading...</option>');
            $.ajax({
                url: "{{ route('marks.exams.by.session') }}",
                type: 'GET',
                data: { session_id: session_id },
                success: function(exams) {
                    let options = '<option value="">All Exams</option>';
                    if(exams.length > 0) {
                        exams.forEach(function(exam) {
                            options += `<option value="${exam.id}">${exam.name}</option>`;
                        });
                    }
                    examSelect.html(options);
                    
                    let currentExam = "{{ request('exam_id') }}";
                    if(currentExam) {
                        examSelect.val(currentExam);
                    }
                },
                error: function(xhr) {
                    console.error('Failed to fetch exams');
                    examSelect.html('<option value="">Error loading exams</option>');
                }
            });
        } else {
            examSelect.html('<option value="">All Exams</option>');
            @foreach(\App\Models\Exam::all() as $exam)
                examSelect.append('<option value="{{ $exam->id }}">{{ $exam->name }}</option>');
            @endforeach
        }
    });
});
</script>
@stop