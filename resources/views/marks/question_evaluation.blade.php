@extends('adminlte::page')

@section('title', 'Question Evaluation Report')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center">
            <a href="{{ route('marks.create') }}" class="btn btn-secondary btn-sm mr-3">
                <i class="fas fa-arrow-left mr-1"></i>Back to Enter Marks
            </a>
            <h1 class="mb-0"><i class="fas fa-chart-bar text-primary mr-2"></i>Question Evaluation Report</h1>
        </div>
        <div class="mt-1 mt-md-0">
            <a href="{{ route('exam.questions.manage') }}" class="btn btn-outline-primary btn-sm mr-2">
                <i class="fas fa-cog mr-1"></i>Manage Questions
            </a>
            @if($report)
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-print mr-1"></i>Print / PDF
            </button>
            @endif
        </div>
    </div>
@stop

@section('content')
{{-- ── Filter Form ───────────────────────────────────────────────────────── --}}
<div class="card card-outline card-primary shadow-sm mb-4 no-print">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter</h3></div>
    <div class="card-body">
        <form method="GET" action="{{ route('marks.question.evaluation') }}" id="eval-filter-form">
            <div class="row align-items-end">
                {{-- Academic Year --}}
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="font-weight-bold">Academic Year</label>
                    <select name="session_id" id="ev-session" class="form-control select2">
                        <option value="">— All Years —</option>
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}" {{ request('session_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Class --}}
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="font-weight-bold">Class <small class="text-muted">(optional)</small></label>
                    <select name="class_id" id="ev-class" class="form-control select2">
                        <option value="">— All Classes —</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Department --}}
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="font-weight-bold">Department <small class="text-muted">(optional)</small></label>
                    <select name="department_id" id="ev-department" class="form-control select2">
                        <option value="">— All Departments —</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Exam (cascades from session) --}}
                <div class="col-lg-3 col-md-6 mb-2">
                    <label class="font-weight-bold">Exam</label>
                    <select name="exam_id" id="ev-exam" class="form-control select2" required>
                        <option value="">— Select Year First —</option>
                        @foreach($exams as $e)
                            <option value="{{ $e->id }}" {{ request('exam_id') == $e->id ? 'selected' : '' }}>
                                {{ $e->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject (cascades from department) --}}
                <div class="col-lg-3 col-md-6 mb-2">
                    <label class="font-weight-bold">Subject</label>
                    <select name="subject_id" id="ev-subject" class="form-control select2" required>
                        <option value="">— Select Subject —</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-12 text-right">
                    <a href="{{ route('marks.question.evaluation') }}" class="btn btn-outline-secondary btn-sm mr-2">
                        <i class="fas fa-times mr-1"></i>Clear
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i>Generate Report
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($report === null && request()->filled('exam_id'))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        No questions have been defined for this exam and subject yet.
        <a href="{{ route('exam.questions.manage') }}" class="alert-link">Define questions first.</a>
    </div>
@endif

@if($report)
{{-- ── Report Header ─────────────────────────────────────────────────────── --}}
<div class="print-header" style="display:none;">
    <h2 class="text-center">Question Evaluation Report</h2>
    <p class="text-center text-muted mb-1">
        Exam: <strong>{{ $report['exam']->name }}</strong> &nbsp;·&nbsp;
        Subject: <strong>{{ $report['subject']->name }}</strong>
        @if($report['class']) &nbsp;·&nbsp; Class: <strong>{{ $report['class']->name }}</strong> @endif
    </p>
    <p class="text-center text-muted small">Generated: {{ now()->format('d M Y H:i') }}</p>
    <hr>
</div>

{{-- ── Summary Cards ─────────────────────────────────────────────────────── --}}
<div class="row mb-4">
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-primary"><i class="fas fa-file-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Questions</span>
                <span class="info-box-number">{{ $questions->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Students</span>
                <span class="info-box-number">{{ $report['student_count'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info"><i class="fas fa-star"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Marks</span>
                <span class="info-box-number">{{ $report['total_max'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon {{ ($report['class_avg_pct'] ?? 0) >= 70 ? 'bg-success' : (($report['class_avg_pct'] ?? 0) >= 50 ? 'bg-warning' : 'bg-danger') }}">
                <i class="fas fa-percentage"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Class Average</span>
                <span class="info-box-number">{{ $report['class_avg_pct'] !== null ? $report['class_avg_pct'].'%' : '—' }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Section 1: Question Difficulty Analysis ─────────────────────────── --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-bar-chart mr-2 text-primary"></i>1. Question Difficulty Analysis
        </h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:80px">Question</th>
                    <th>Description</th>
                    <th class="text-center" style="width:90px">Max Marks</th>
                    <th class="text-center" style="width:110px">Class Average</th>
                    <th class="text-center" style="width:90px">Avg %</th>
                    <th class="text-center" style="width:100px">Mastered ≥70%</th>
                    <th class="text-center" style="width:100px">Struggling &lt;50%</th>
                    <th style="width:220px">Difficulty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['question_stats'] as $qs)
                    @php
                        $pct     = $qs['avg_pct'];
                        $color   = $pct === null ? 'secondary' : ($pct >= 70 ? 'success' : ($pct >= 50 ? 'warning' : 'danger'));
                        $label   = $pct === null ? 'N/A' : ($pct >= 70 ? 'Easy' : ($pct >= 50 ? 'Moderate' : 'Hard'));
                        $barPct  = $pct ?? 0;
                    @endphp
                    <tr>
                        <td class="text-center">
                            <span class="badge badge-primary badge-pill" style="font-size:13px; min-width:34px;">
                                Q{{ $qs['question']->question_no }}
                            </span>
                        </td>
                        <td>{{ $qs['question']->description ?: '—' }}</td>
                        <td class="text-center font-weight-bold">{{ $qs['max_marks'] }}</td>
                        <td class="text-center">
                            {{ $qs['avg_score'] !== null ? $qs['avg_score'] : '—' }}
                            <small class="text-muted">/ {{ $qs['max_marks'] }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $color }} px-2 py-1" style="font-size:12px;">
                                {{ $pct !== null ? $pct.'%' : '—' }}
                            </span>
                        </td>
                        <td class="text-center text-success font-weight-bold">{{ $qs['mastered'] }}</td>
                        <td class="text-center text-danger font-weight-bold">{{ $qs['struggling'] }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-fill mr-2" style="height:14px; border-radius:7px;">
                                    <div class="progress-bar bg-{{ $color }}"
                                         style="width:{{ $barPct }}%; border-radius:7px;"
                                         title="{{ $pct !== null ? $pct.'%' : 'N/A' }}">
                                    </div>
                                </div>
                                <small class="text-{{ $color }} font-weight-bold" style="min-width:55px;">{{ $label }}</small>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── Section 2: Question Difficulty Ranking ──────────────────────────── --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-sort-amount-up mr-2 text-warning"></i>2. Question Difficulty Ranking
            <small class="text-muted font-weight-normal ml-2">(Hardest → Easiest)</small>
        </h3>
    </div>
    <div class="card-body">
        @php
            $ranked = $report['question_stats']->sortBy('avg_pct')->values();
        @endphp
        <div class="row">
            @foreach($ranked as $rank => $qs)
                @php
                    $pct   = $qs['avg_pct'];
                    $color = $pct === null ? 'secondary' : ($pct >= 70 ? 'success' : ($pct >= 50 ? 'warning' : 'danger'));
                    $icon  = $pct === null ? 'minus' : ($pct >= 70 ? 'smile' : ($pct >= 50 ? 'meh' : 'frown'));
                @endphp
                <div class="col-md-4 col-lg-3 mb-3">
                    <div class="card border-{{ $color }} shadow-sm h-100">
                        <div class="card-body py-3 text-center">
                            <div class="text-muted small mb-1">Rank #{{ $rank + 1 }}</div>
                            <h4 class="mb-1">
                                <span class="badge badge-{{ $color }}">Q{{ $qs['question']->question_no }}</span>
                            </h4>
                            @if($qs['question']->description)
                                <div class="small text-muted mb-2">{{ $qs['question']->description }}</div>
                            @endif
                            <h3 class="mb-0 text-{{ $color }} font-weight-bold">
                                {{ $pct !== null ? $pct.'%' : '—' }}
                            </h3>
                            <div class="small text-muted">avg {{ $qs['avg_score'] ?? '—' }} / {{ $qs['max_marks'] }}</div>
                            <i class="fas fa-{{ $icon }} fa-lg text-{{ $color }} mt-2"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Section 3: Per-Student Breakdown ───────────────────────────────── --}}
@if($studentRows->isNotEmpty())
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex align-items-center">
        <h3 class="card-title font-weight-bold mb-0">
            <i class="fas fa-table mr-2 text-success"></i>3. Per-Student Question Breakdown
        </h3>
        <span class="ml-auto badge badge-light border">
            {{ $studentRows->count() }} students
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0" id="student-breakdown-table">
                <thead class="thead-light">
                    <tr>
                        <th style="width:30px" class="text-center">#</th>
                        <th>Student</th>
                        <th class="text-center text-muted small" style="width:80px">Class</th>
                        @foreach($questions as $q)
                            <th class="text-center" style="min-width:70px;">
                                Q{{ $q->question_no }}
                                <br><small class="text-primary font-weight-normal">/{{ $q->max_marks }}</small>
                            </th>
                        @endforeach
                        <th class="text-center" style="min-width:70px;">Total<br><small class="text-primary">/{{ $report['total_max'] }}</small></th>
                        <th class="text-center" style="min-width:65px;">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentRows as $idx => $row)
                        @php
                            $pct   = $row['mark'];
                            $color = $pct >= 70 ? '#d4edda' : ($pct >= 50 ? '#fff3cd' : '#f8d7da');
                            $txt   = $pct >= 70 ? '#155724' : ($pct >= 50 ? '#856404' : '#721c24');
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $idx + 1 }}</td>
                            <td class="font-weight-bold">{{ $row['student']->full_name }}</td>
                            <td class="text-center small text-muted">{{ $row['student']->schoolClass->name ?? '—' }}</td>
                            @foreach($questions as $q)
                                @php
                                    $score  = $row['q_scores'][$q->id] ?? null;
                                    $qPct   = ($score !== null && $q->max_marks > 0) ? ($score / $q->max_marks) : null;
                                    $qBg    = $qPct === null ? '' : ($qPct >= 0.7 ? 'background:#d4edda;' : ($qPct >= 0.5 ? 'background:#fff3cd;' : 'background:#f8d7da;'));
                                @endphp
                                <td class="text-center" style="{{ $qBg }}">
                                    @if($score !== null)
                                        {{ $score + 0 }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center font-weight-bold">
                                {{ round($row['raw_total'], 1) + 0 }}
                            </td>
                            <td class="text-center font-weight-bold" style="background:{{ $color }}; color:{{ $txt }};">
                                {{ number_format($row['mark'], 1) }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light font-weight-bold">
                    <tr>
                        <td colspan="3" class="text-right">Class Average:</td>
                        @foreach($questions as $q)
                            @php
                                $colAvg = $report['question_stats']->firstWhere('question.id', $q->id)['avg_score'] ?? null;
                            @endphp
                            <td class="text-center text-info">{{ $colAvg !== null ? $colAvg : '—' }}</td>
                        @endforeach
                        <td class="text-center text-info">
                            @php
                                $rawAvg = $studentRows->avg('raw_total');
                            @endphp
                            {{ $rawAvg !== null ? round($rawAvg, 1) : '—' }}
                        </td>
                        <td class="text-center text-info">{{ $report['class_avg_pct'] !== null ? $report['class_avg_pct'].'%' : '—' }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ── Print footer ─────────────────────────────────────────────────────── --}}
<div class="print-footer text-center text-muted small mt-4" style="display:none;">
    Chani Technologies AI School Management System &nbsp;·&nbsp; {{ now()->format('d M Y H:i') }}
</div>

@endif {{-- end $report --}}

@stop

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet"/>
<style>
/* ── Print styles ── */
@media print {
    .no-print, .main-sidebar, .main-header, .content-header .btn,
    .breadcrumb-wrapper, nav, footer { display: none !important; }
    .print-header, .print-footer { display: block !important; }
    .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
    body { font-size: 12px; }
    .badge { border: 1px solid #ccc; }
    .content-wrapper { margin-left: 0 !important; }
    @page { margin: 1.5cm; }
}
.progress { background: #e9ecef; }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    // ── Session → load exams ──────────────────────────────────────────────
    function loadEvalExams() {
        const sessionId = $('#ev-session').val();
        const examSel   = $('#ev-exam');
        const current   = '{{ request("exam_id") }}';

        if (!sessionId) {
            examSel.html('<option value="">— Select Year First —</option>').trigger('change');
            return;
        }
        examSel.html('<option value="">Loading…</option>');
        $.get("{{ route('marks.exams.by.session') }}", { session_id: sessionId })
            .done(function (exams) {
                let opts = '<option value="">— Select Exam —</option>';
                exams.forEach(e => {
                    if (e.status === 'published') {
                        opts += `<option value="" disabled>${e.name} (Published — locked)</option>`;
                        return;
                    }
                    const sel = (current == e.id) ? 'selected' : '';
                    opts += `<option value="${e.id}" ${sel}>${e.name}</option>`;
                });
                examSel.html(opts).trigger('change');
            })
            .fail(() => examSel.html('<option value="">Error loading exams</option>'));
    }

    // ── Department → load subjects ────────────────────────────────────────
    function loadEvalSubjects() {
        const deptId  = $('#ev-department').val();
        const subSel  = $('#ev-subject');
        const current = '{{ request("subject_id") }}';

        if (!deptId) {
            // Show all subjects when no department selected
            subSel.html('<option value="">— Select Subject —</option>@foreach($subjects as $s)<option value="{{ $s->id }}" {{ request("subject_id") == $s->id ? "selected" : "" }}>{{ $s->name }}</option>@endforeach').trigger('change');
            return;
        }
        subSel.html('<option value="">Loading…</option>');
        $.get("{{ route('marks.subjects.by.department') }}", { department_id: deptId })
            .done(function (subjects) {
                let opts = '<option value="">— Select Subject —</option>';
                subjects.forEach(s => {
                    const sel = (current == s.id) ? 'selected' : '';
                    opts += `<option value="${s.id}" ${sel}>${s.name}</option>`;
                });
                subSel.html(opts).trigger('change');
            })
            .fail(() => subSel.html('<option value="">Error loading subjects</option>'));
    }

    $('#ev-session').on('change', loadEvalExams);
    $('#ev-department').on('change', loadEvalSubjects);

    // Init: if session already selected (from previous submit), cascade exams
    if ($('#ev-session').val()) loadEvalExams();
    if ($('#ev-department').val()) loadEvalSubjects();
});
</script>
@endpush
