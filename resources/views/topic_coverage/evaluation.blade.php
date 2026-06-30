@extends('adminlte::page')

@section('title', $isManagement ? 'Staff Evaluation Dashboard' : 'My Teaching Evaluation')

@php
$ratingCfg = [
    'excellent' => ['label'=>'Excellent',    'color'=>'success', 'icon'=>'fa-star',            'bg'=>'#d4edda'],
    'good'      => ['label'=>'Good',         'color'=>'primary', 'icon'=>'fa-thumbs-up',        'bg'=>'#cce5ff'],
    'fair'      => ['label'=>'Fair',         'color'=>'warning', 'icon'=>'fa-exclamation-circle','bg'=>'#fff3cd'],
    'poor'      => ['label'=>'Needs Urgent Attention','color'=>'danger', 'icon'=>'fa-times-circle','bg'=>'#f8d7da'],
    'no-plan'   => ['label'=>'No Plan Filed','color'=>'dark',   'icon'=>'fa-ban',              'bg'=>'#e2e3e5'],
];
@endphp

@section('content_header')
<div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:.5rem">
    <div>
        <h1 class="m-0 text-dark">
            @if($isManagement)
                <i class="fas fa-chart-line mr-2 text-primary"></i>Staff Evaluation & Coverage Report
                @if($userDept && !$user->hasAnyRole(['Admin','Academic']))
                    <small class="text-muted" style="font-size:.55em">— {{ $userDept->name }} Department</small>
                @endif
            @else
                <i class="fas fa-user-graduate mr-2 text-primary"></i>My Professional Dashboard
            @endif
        </h1>
        @if($selectedSession)
            <small class="text-muted">Academic Session: <strong>{{ $selectedSession->name }}</strong></small>
        @endif
    </div>
    <div class="d-flex flex-wrap align-items-center" style="gap:.5rem">
        @if($isManagement)
        <form method="GET" action="{{ route('topic-coverage.evaluation') }}" class="d-inline-flex flex-wrap align-items-center" style="gap:.4rem">
            <select name="session_id" class="form-control form-control-sm" style="min-width:130px" onchange="this.form.submit()">
                <option value="">All Sessions</option>
                @foreach($sessions as $s)
                    <option value="{{ $s->id }}" @selected($selectedSession?->id == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
            @if($user->hasAnyRole(['Admin','Academic']))
            <select name="department_id" class="form-control form-control-sm" style="min-width:150px" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected($selectedDept == $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
            @endif
        </form>
        @endif
        <a href="{{ route('topic-coverage.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-tasks mr-1"></i>Lesson Plans
        </a>
        @if(!$isManagement)
        <a href="{{ route('timetables.my-sessions') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-calendar-week mr-1"></i>My Sessions
        </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

{{-- ══════════════════════════════════════════════════════════
     MANAGEMENT VIEW
══════════════════════════════════════════════════════════ --}}
@if($isManagement)

@php
    $totalTeachers  = count($teacherData);
    $avgCoverage    = $totalTeachers > 0 ? round(collect($teacherData)->avg('coverage_pct'), 1) : 0;
    $avgAtt         = collect($teacherData)->whereNotNull('att_rate')->avg('att_rate');
    $avgAtt         = $avgAtt !== null ? round($avgAtt, 1) : null;
    $needAttention  = collect($teacherData)->whereIn('rating', ['poor','no-plan'])->count();
    $excellentCount = collect($teacherData)->where('rating','excellent')->count();
@endphp

{{-- ── Summary stats ── --}}
<div class="row mb-3">
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm mb-3">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-chalkboard-teacher"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Teachers Evaluated</span>
                <span class="info-box-number">{{ $totalTeachers }}</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm mb-3">
            <span class="info-box-icon bg-{{ $avgCoverage >= 70 ? 'success' : ($avgCoverage >= 40 ? 'warning' : 'danger') }} elevation-1">
                <i class="fas fa-book-open"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Avg Curriculum Coverage</span>
                <span class="info-box-number">{{ $avgCoverage }}%</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm mb-3">
            <span class="info-box-icon bg-{{ $avgAtt === null ? 'secondary' : ($avgAtt >= 80 ? 'success' : ($avgAtt >= 65 ? 'warning' : 'danger')) }} elevation-1">
                <i class="fas fa-calendar-check"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Avg Attendance Rate</span>
                <span class="info-box-number">@if($avgAtt !== null){{ $avgAtt }}%@else—@endif</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm mb-3">
            <span class="info-box-icon bg-{{ $needAttention > 0 ? 'danger' : 'success' }} elevation-1">
                <i class="fas fa-{{ $needAttention > 0 ? 'exclamation-triangle' : 'check-circle' }}"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Needs Attention</span>
                <span class="info-box-number">{{ $needAttention }} <small class="text-muted" style="font-size:.5em">/ {{ $totalTeachers }}</small></span>
            </div>
        </div>
    </div>
</div>

@if(empty($teacherData))
    <div class="alert alert-info"><i class="fas fa-info-circle mr-1"></i>No teachers found for the selected filters.</div>
@else

{{-- ── Coverage distribution bar ── --}}
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mr-auto"><i class="fas fa-chart-bar mr-2"></i>Coverage Distribution</h3>
        <small class="text-muted">{{ $excellentCount }} Excellent · {{ $needAttention }} Needs Attention</small>
    </div>
    <div class="card-body pb-2">
        <div style="display:flex;height:32px;border-radius:6px;overflow:hidden;gap:2px">
            @foreach(['excellent'=>'#28a745','good'=>'#007bff','fair'=>'#ffc107','poor'=>'#dc3545','no-plan'=>'#6c757d'] as $r => $color)
                @php $cnt = collect($teacherData)->where('rating',$r)->count(); @endphp
                @if($cnt > 0)
                <div style="flex:{{ $cnt }};background:{{ $color }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:600" title="{{ $ratingCfg[$r]['label'] }}: {{ $cnt }}">
                    {{ $cnt }}
                </div>
                @endif
            @endforeach
        </div>
        <div class="d-flex flex-wrap mt-2" style="gap:.6rem">
            @foreach($ratingCfg as $r => $rc)
                <span class="badge badge-{{ $rc['color'] }}" style="font-size:.72rem">
                    <i class="fas {{ $rc['icon'] }} mr-1"></i>{{ $rc['label'] }}
                </span>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Teacher cards grid ── --}}
<div class="row">
    @foreach($teacherData as $td)
    @php
        $rc = $ratingCfg[$td['rating']];
        $coverColor = $td['coverage_pct'] >= 80 ? 'success' : ($td['coverage_pct'] >= 50 ? 'primary' : ($td['coverage_pct'] >= 25 ? 'warning' : 'danger'));
        $attColor   = $td['att_rate'] === null ? 'secondary' : ($td['att_rate'] >= 85 ? 'success' : ($td['att_rate'] >= 70 ? 'warning' : 'danger'));
    @endphp
    <div class="col-lg-6 col-xl-4 mb-4">
        <div class="card shadow h-100" style="border-left:4px solid var(--bs-{{ $rc['color'] }},#6c757d);border-radius:8px">
            <div class="card-header d-flex align-items-center py-2" style="background:{{ $rc['bg'] }}">
                <div class="flex-grow-1">
                    <strong class="d-block" style="font-size:.95rem">{{ $td['teacher']->name }}</strong>
                    @if($td['dept'])
                        <small class="text-muted"><i class="fas fa-building mr-1"></i>{{ $td['dept']->name }}</small>
                    @endif
                </div>
                <span class="badge badge-{{ $rc['color'] }} ml-2" style="font-size:.7rem;white-space:normal;text-align:center;max-width:80px">
                    <i class="fas {{ $rc['icon'] }} mr-1"></i>{{ $rc['label'] }}
                </span>
            </div>
            <div class="card-body p-3">

                {{-- Coverage & Attendance meters --}}
                <div class="row mb-2" style="font-size:.82rem">
                    <div class="col-6">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Curriculum</span>
                            <strong class="text-{{ $coverColor }}">{{ $td['coverage_pct'] }}%</strong>
                        </div>
                        <div class="progress" style="height:8px;border-radius:4px">
                            <div class="progress-bar bg-{{ $coverColor }}" style="width:{{ $td['coverage_pct'] }}%"></div>
                        </div>
                        <small class="text-muted">{{ $td['covered_subs'] }}/{{ $td['total_subs'] }} subtopics</small>
                    </div>
                    <div class="col-6">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Attendance</span>
                            <strong class="text-{{ $attColor }}">@if($td['att_rate'] !== null){{ $td['att_rate'] }}%@else—@endif</strong>
                        </div>
                        <div class="progress" style="height:8px;border-radius:4px">
                            <div class="progress-bar bg-{{ $attColor }}" style="width:{{ $td['att_rate'] ?? 0 }}%"></div>
                        </div>
                        <small class="text-muted">{{ $td['attended'] + $td['late'] }}/{{ $td['sessions_total'] }} sessions</small>
                    </div>
                </div>

                {{-- Plan stats row --}}
                <div class="d-flex flex-wrap border-top pt-2 mt-1" style="gap:.5rem;font-size:.78rem">
                    <span class="text-muted"><i class="fas fa-book text-info mr-1"></i>{{ $td['total_plans'] }} plans</span>
                    <span class="text-muted"><i class="fas fa-list text-primary mr-1"></i>{{ $td['covered_topics'] }}/{{ $td['total_topics'] }} topics</span>
                    <span class="text-muted"><i class="fas fa-edit text-secondary mr-1"></i>
                        {{ $td['record_rate'] !== null ? $td['record_rate'].'% logged' : 'no logs' }}
                    </span>
                    @if($td['absent'] > 0)
                    <span class="text-danger"><i class="fas fa-times-circle mr-1"></i>{{ $td['absent'] }} absent</span>
                    @endif
                </div>

                {{-- Per-plan mini progress ── --}}
                @if($td['plans']->count())
                <div class="mt-2 border-top pt-2" style="max-height:110px;overflow-y:auto">
                    @foreach($td['plans'] as $plan)
                        @php
                            $pSubs = $plan->topics->sum(fn($t) => $t->subtopics->count());
                            $pCov  = $plan->topics->sum(fn($t) => $t->subtopics->where('status','covered')->count());
                            $pPct  = $pSubs > 0 ? round($pCov/$pSubs*100) : 0;
                            $pClr  = $pPct >= 80 ? 'success' : ($pPct >= 50 ? 'primary' : ($pPct >= 25 ? 'warning' : 'danger'));
                        @endphp
                        <div class="mb-1 d-flex align-items-center" style="gap:.4rem;font-size:.75rem">
                            <div style="flex:1;min-width:0">
                                <div class="d-flex justify-content-between">
                                    <span class="text-truncate" title="{{ $plan->subject->name }} · {{ $plan->schoolClass->name }}">
                                        {{ $plan->subject->name }} <span class="text-muted">· {{ $plan->schoolClass->name }}</span>
                                    </span>
                                    <strong class="text-{{ $pClr }} ml-1">{{ $pPct }}%</strong>
                                </div>
                                <div class="progress" style="height:4px;border-radius:2px">
                                    <div class="progress-bar bg-{{ $pClr }}" style="width:{{ $pPct }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif

                {{-- Recommendations --}}
                @foreach($td['recommendations'] as $rec)
                <div class="alert alert-{{ $rec['type'] }} py-1 px-2 mt-2 mb-0 small">
                    <i class="fas fa-{{ $rec['type'] === 'danger' ? 'exclamation-triangle' : ($rec['type'] === 'warning' ? 'exclamation-circle' : ($rec['type'] === 'success' ? 'check-circle' : 'info-circle')) }} mr-1"></i>
                    {{ $rec['msg'] }}
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Full Recommendations Summary Table ── --}}
@php $hasRecs = collect($teacherData)->filter(fn($t) => collect($t['recommendations'])->whereIn('type',['danger','warning'])->count() > 0)->count() > 0; @endphp
@if($hasRecs)
<div class="card shadow mb-4">
    <div class="card-header bg-danger text-white">
        <h3 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Action Required — Recommendations Summary</h3>
        <div class="card-tools"><button type="button" class="btn btn-tool text-white" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-bordered mb-0" style="font-size:.85rem">
            <thead class="thead-light">
                <tr>
                    <th width="180">Teacher</th>
                    <th width="160">Department</th>
                    <th width="90" class="text-center">Coverage</th>
                    <th width="90" class="text-center">Attendance</th>
                    <th>Recommendations</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teacherData as $td)
                    @php $urgentRecs = collect($td['recommendations'])->whereIn('type',['danger','warning']); @endphp
                    @if($urgentRecs->count())
                    <tr>
                        <td>
                            <strong>{{ $td['teacher']->name }}</strong>
                            <br><small class="text-muted">{{ $td['teacher']->email }}</small>
                        </td>
                        <td>{{ $td['dept']?->name ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $td['coverage_pct'] >= 80 ? 'success' : ($td['coverage_pct'] >= 50 ? 'primary' : ($td['coverage_pct'] >= 25 ? 'warning' : 'danger')) }}">
                                {{ $td['coverage_pct'] }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @if($td['att_rate'] !== null)
                            <span class="badge badge-{{ $td['att_rate'] >= 85 ? 'success' : ($td['att_rate'] >= 70 ? 'warning' : 'danger') }}">
                                {{ $td['att_rate'] }}%
                            </span>
                            @else<span class="text-muted">—</span>@endif
                        </td>
                        <td>
                            @foreach($urgentRecs as $rec)
                            <div class="d-flex align-items-start mb-1" style="gap:.3rem">
                                <i class="fas fa-{{ $rec['type'] === 'danger' ? 'exclamation-triangle text-danger' : 'exclamation-circle text-warning' }} mt-1" style="flex-shrink:0"></i>
                                <span>{{ $rec['msg'] }}</span>
                            </div>
                            @endforeach
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endif {{-- end @if empty --}}

{{-- ══════════════════════════════════════════════════════════
     TEACHER SELF-DASHBOARD
══════════════════════════════════════════════════════════ --}}
@else

@php $td = $teacherData[0] ?? null; @endphp
@if(!$td)
<div class="alert alert-info"><i class="fas fa-info-circle mr-1"></i>No data available. Make sure you are assigned to a published timetable.</div>
@else
@php
    $rc          = $ratingCfg[$td['rating']];
    $coverColor  = $td['coverage_pct'] >= 80 ? 'success' : ($td['coverage_pct'] >= 50 ? 'primary' : ($td['coverage_pct'] >= 25 ? 'warning' : 'danger'));
    $attColor    = $td['att_rate'] === null ? 'secondary' : ($td['att_rate'] >= 85 ? 'success' : ($td['att_rate'] >= 70 ? 'warning' : 'danger'));
@endphp

{{-- ── Hero Banner ── --}}
<div class="card mb-4 shadow border-0" style="border-radius:12px;overflow:hidden;background:linear-gradient(135deg,#1a237e,#1976d2)">
    <div class="card-body py-4 px-4 d-flex align-items-center flex-wrap" style="gap:1.5rem;color:#fff">
        <div class="d-flex align-items-center justify-content-center rounded-circle shadow"
             style="width:72px;height:72px;background:rgba(255,255,255,.15);flex-shrink:0;font-size:1.8rem">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="flex-grow-1">
            <h3 class="mb-0 font-weight-bold">{{ $td['teacher']->name }}</h3>
            @if($td['dept'])
                <p class="mb-0 opacity-75"><i class="fas fa-building mr-1"></i>{{ $td['dept']->name }} Department</p>
            @endif
            @if($selectedSession)
                <p class="mb-0 opacity-75"><i class="fas fa-calendar-alt mr-1"></i>{{ $selectedSession->name }}</p>
            @endif
        </div>
        <div class="text-center">
            <div class="badge badge-{{ $rc['color'] }} px-3 py-2" style="font-size:.9rem;border-radius:8px">
                <i class="fas {{ $rc['icon'] }} mr-1"></i>{{ $rc['label'] }}
            </div>
            <small class="d-block mt-1 opacity-75">Overall Rating</small>
        </div>
    </div>
</div>

{{-- ── 4 KPI cards ── --}}
<div class="row mb-3">
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm mb-3">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-book"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Lesson Plans</span>
                <span class="info-box-number">{{ $td['total_plans'] }}</span>
                <span class="info-box-text" style="font-size:.7rem">{{ $td['total_topics'] }} topics · {{ $td['total_subs'] }} subtopics</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm mb-3">
            <span class="info-box-icon bg-{{ $coverColor }} elevation-1"><i class="fas fa-tasks"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Curriculum Coverage</span>
                <span class="info-box-number">{{ $td['coverage_pct'] }}%</span>
                <span class="info-box-text" style="font-size:.7rem">{{ $td['covered_subs'] }}/{{ $td['total_subs'] }} subtopics</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm mb-3">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-calendar-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sessions Taught</span>
                <span class="info-box-number">{{ $td['attended'] + $td['late'] }}</span>
                <span class="info-box-text" style="font-size:.7rem">of {{ $td['sessions_total'] }} logged</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm mb-3">
            <span class="info-box-icon bg-{{ $attColor }} elevation-1"><i class="fas fa-chart-pie"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Attendance Rate</span>
                <span class="info-box-number">@if($td['att_rate'] !== null){{ $td['att_rate'] }}%@else—@endif</span>
                <span class="info-box-text" style="font-size:.7rem">{{ $td['absent'] }} absent · {{ $td['late'] }} late</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- ── Left: Curriculum Progress ── --}}
    <div class="col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title"><i class="fas fa-book-open mr-2"></i>Curriculum Coverage — by Subject & Class</h3>
            </div>
            <div class="card-body p-0">
                @if($td['plans']->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <p>No lesson plans found. <a href="{{ route('topic-coverage.create') }}">Create one now.</a></p>
                </div>
                @else
                @foreach($td['plans'] as $plan)
                    @php
                        $pSubs = $plan->topics->sum(fn($t) => $t->subtopics->count());
                        $pCov  = $plan->topics->sum(fn($t) => $t->subtopics->where('status','covered')->count());
                        $pPct  = $pSubs > 0 ? round($pCov / $pSubs * 100, 1) : 0;
                        $pClr  = $pPct >= 80 ? 'success' : ($pPct >= 50 ? 'primary' : ($pPct >= 25 ? 'warning' : 'danger'));
                    @endphp
                    <div class="border-bottom p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-grow-1">
                                <strong>{{ $plan->subject->name }}</strong>
                                <span class="text-muted ml-2">{{ $plan->schoolClass->name }}</span>
                            </div>
                            <a href="{{ route('topic-coverage.show', $plan) }}" class="btn btn-xs btn-outline-secondary ml-2">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <span class="badge badge-{{ $pClr }} ml-2">{{ $pPct }}%</span>
                        </div>
                        <div class="progress mb-2" style="height:10px;border-radius:5px">
                            <div class="progress-bar bg-{{ $pClr }}" style="width:{{ $pPct }}%"></div>
                        </div>
                        <small class="text-muted">{{ $pCov }}/{{ $pSubs }} subtopics covered · {{ $plan->topics->count() }} topics</small>

                        {{-- Per-topic breakdown --}}
                        @if($plan->topics->count())
                        <div class="mt-2 pl-2" style="border-left:3px solid #dee2e6">
                            @foreach($plan->topics as $topic)
                                @php
                                    $tSubs = $topic->subtopics->count();
                                    $tCov  = $topic->subtopics->where('status','covered')->count();
                                    $tPct  = $tSubs > 0 ? round($tCov / $tSubs * 100) : 0;
                                    $tClr  = $tPct >= 100 ? 'success' : ($tPct >= 50 ? 'info' : ($tPct > 0 ? 'warning' : 'light'));
                                @endphp
                                <div class="d-flex align-items-center mb-1" style="gap:.5rem;font-size:.78rem">
                                    <i class="fas fa-{{ $tPct >= 100 ? 'check-circle text-success' : ($tPct > 0 ? 'adjust text-warning' : 'circle text-muted') }}" style="flex-shrink:0;width:14px"></i>
                                    <span class="flex-grow-1 text-truncate" title="{{ $topic->title }}">{{ $topic->title }}</span>
                                    <div class="progress flex-shrink-0" style="width:60px;height:5px;border-radius:3px">
                                        <div class="progress-bar bg-{{ $tClr }}" style="width:{{ $tPct }}%"></div>
                                    </div>
                                    <span class="text-muted text-nowrap" style="width:40px;text-align:right">{{ $tCov }}/{{ $tSubs }}</span>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- ── Right: Sessions + Recommendations ── --}}
    <div class="col-lg-5">

        {{-- Session Breakdown --}}
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-week mr-2"></i>Session Record Summary</h3>
            </div>
            <div class="card-body">
                @if($td['sessions_total'] === 0)
                <p class="text-muted text-center py-2">No sessions logged yet. Use <a href="{{ route('timetables.my-sessions') }}">My Sessions</a> to log each class.</p>
                @else
                {{-- Donut chart --}}
                <div class="d-flex align-items-center mb-3" style="gap:1rem">
                    <canvas id="sessionChart" width="110" height="110" style="flex-shrink:0"></canvas>
                    <div style="font-size:.83rem">
                        <div class="mb-1"><span class="badge badge-success mr-1">&nbsp;</span>Attended: <strong>{{ $td['attended'] }}</strong></div>
                        <div class="mb-1"><span class="badge badge-warning mr-1">&nbsp;</span>Late: <strong>{{ $td['late'] }}</strong></div>
                        <div class="mb-1"><span class="badge badge-danger mr-1">&nbsp;</span>Absent: <strong>{{ $td['absent'] }}</strong></div>
                        <div class="mb-1"><span class="badge badge-secondary mr-1">&nbsp;</span>Other: <strong>{{ $td['other'] }}</strong></div>
                        <hr class="my-1">
                        <div><span class="badge badge-info mr-1">&nbsp;</span>Topics logged: <strong>{{ $td['topics_logged'] }}</strong></div>
                    </div>
                </div>
                <div class="progress mb-1" style="height:12px;border-radius:6px">
                    <div class="progress-bar bg-success" style="width:{{ $td['sessions_total'] > 0 ? round($td['attended']/$td['sessions_total']*100) : 0 }}%" title="Attended"></div>
                    <div class="progress-bar bg-warning" style="width:{{ $td['sessions_total'] > 0 ? round($td['late']/$td['sessions_total']*100) : 0 }}%" title="Late"></div>
                    <div class="progress-bar bg-danger"  style="width:{{ $td['sessions_total'] > 0 ? round($td['absent']/$td['sessions_total']*100) : 0 }}%" title="Absent"></div>
                    <div class="progress-bar bg-secondary" style="width:{{ $td['sessions_total'] > 0 ? round($td['other']/$td['sessions_total']*100) : 0 }}%" title="Other"></div>
                </div>
                <small class="text-muted">{{ $td['sessions_total'] }} total logged sessions</small>
                @endif
            </div>
        </div>

        {{-- Recommendations --}}
        <div class="card shadow mb-4">
            <div class="card-header" style="background:{{ $rc['bg'] }}">
                <h3 class="card-title"><i class="fas fa-lightbulb mr-2 text-{{ $rc['color'] }}"></i>Performance Feedback</h3>
            </div>
            <div class="card-body p-2">
                @foreach($td['recommendations'] as $rec)
                <div class="alert alert-{{ $rec['type'] }} py-2 px-3 mb-2 small">
                    <i class="fas fa-{{ $rec['type'] === 'danger' ? 'exclamation-triangle' : ($rec['type'] === 'warning' ? 'exclamation-circle' : ($rec['type'] === 'success' ? 'check-circle' : 'info-circle')) }} mr-1"></i>
                    {{ $rec['msg'] }}
                </div>
                @endforeach

                <div class="border-top mt-2 pt-2">
                    <p class="small text-muted mb-2"><strong>Quick Actions:</strong></p>
                    <a href="{{ route('topic-coverage.index') }}" class="btn btn-sm btn-outline-primary btn-block mb-1">
                        <i class="fas fa-tasks mr-1"></i>Manage Lesson Plans
                    </a>
                    <a href="{{ route('timetables.my-sessions') }}" class="btn btn-sm btn-outline-secondary btn-block">
                        <i class="fas fa-calendar-week mr-1"></i>Log Session Records
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endif {{-- end if $td --}}
@endif {{-- end management/teacher split --}}

</div>
@endsection

@push('css')
<style>
.info-box { margin-bottom:1rem; }
.card { border-radius:8px; }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(!$isManagement && isset($td) && $td && $td['sessions_total'] > 0)
(function() {
    const ctx = document.getElementById('sessionChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Attended','Late','Absent','Other'],
            datasets: [{
                data: [{{ $td['attended'] }}, {{ $td['late'] }}, {{ $td['absent'] }}, {{ $td['other'] }}],
                backgroundColor: ['#28a745','#ffc107','#dc3545','#6c757d'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            cutout: '65%',
            plugins: { legend: { display: false } },
            animation: { animateRotate: true },
        }
    });
})();
@endif
</script>
@endpush
