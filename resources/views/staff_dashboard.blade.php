@extends('adminlte::page')
@section('title', 'My Dashboard')

@push('css')
<style>
/* ── KPI cards ─────────────────────────────────────────────────────── */
.perf-card {
    border-radius: 14px; border: none; overflow: hidden;
    box-shadow: 0 3px 16px rgba(0,0,0,.08);
    transition: transform .15s, box-shadow .15s;
}
.perf-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.13); }
.perf-card .inner { padding: 1.2rem 1.4rem; }
.perf-card .num  { font-size: 2.2rem; font-weight: 800; line-height: 1; }
.perf-card .lbl  { font-size: .73rem; text-transform: uppercase; letter-spacing: .07em; margin-top: 4px; opacity: .85; }
.perf-card .foot { background: rgba(0,0,0,.10); padding: .4rem 1.4rem; font-size: .74rem; }
.perf-card .icon-wrap { font-size: 2.6rem; opacity: .18; position: absolute; right: 1rem; top: .8rem; }

/* ── Section cards ─────────────────────────────────────────────────── */
.sec-card { border-radius: 12px; border: 1.5px solid #e3e6f0; box-shadow: 0 2px 10px rgba(0,0,0,.05); }
.sec-card .card-header {
    background: #fff; border-bottom: 2px solid #e8ecf5;
    padding: .8rem 1.2rem; font-weight: 700; font-size: .88rem; color: #1a3c6e;
    border-radius: 12px 12px 0 0;
}
.sec-card .card-body { padding: 1rem 1.2rem; }

/* ── Session row ───────────────────────────────────────────────────── */
.session-row {
    display: flex; align-items: center; gap: .75rem;
    padding: .55rem 0; border-bottom: 1px solid #f0f2f8;
}
.session-row:last-child { border-bottom: none; }
.session-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.session-subject { font-weight: 600; font-size: .85rem; }
.session-meta    { font-size: .75rem; color: #888; }
.session-chip    { font-size: .7rem; border-radius: 20px; padding: .15rem .55rem; font-weight: 600; }

/* ── Coverage bar ──────────────────────────────────────────────────── */
.cov-plan { margin-bottom: .9rem; }
.cov-label { display: flex; justify-content: space-between; font-size: .8rem; font-weight: 600; margin-bottom: 3px; }
.cov-bar { height: 7px; border-radius: 6px; background: #eef0f8; overflow: hidden; }
.cov-fill { height: 100%; border-radius: 6px; transition: width .5s; }

/* ── Report grid ───────────────────────────────────────────────────── */
.report-dot-grid { display: flex; flex-wrap: wrap; gap: 4px; }
.report-dot {
    width: 22px; height: 22px; border-radius: 6px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .55rem; font-weight: 700; color: #fff;
    cursor: default;
}
.tip-trigger { position: relative; }
.tip-trigger:hover::after {
    content: attr(data-tip);
    position: absolute; bottom: 120%; left: 50%; transform: translateX(-50%);
    background: #222; color: #fff; font-size: .7rem; padding: 3px 7px;
    border-radius: 5px; white-space: nowrap; z-index: 10;
}

/* ── Greeting banner ───────────────────────────────────────────────── */
.greeting-banner {
    background: linear-gradient(135deg, #1a3c6e 0%, #2e74c0 100%);
    border-radius: 14px; color: #fff; padding: 1.4rem 1.8rem;
    margin-bottom: 1.4rem; position: relative; overflow: hidden;
}
.greeting-banner::after {
    content: ''; position: absolute; right: -30px; top: -30px;
    width: 180px; height: 180px; border-radius: 50%;
    background: rgba(255,255,255,.06);
}
.greeting-banner .time  { font-size: .8rem; opacity: .75; }
.greeting-banner .name  { font-size: 1.5rem; font-weight: 800; margin: 2px 0; }
.greeting-banner .dept  { font-size: .82rem; opacity: .8; }
.greeting-banner .meta-chips { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .8rem; }
.banner-chip {
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
    border-radius: 20px; padding: .2rem .75rem; font-size: .73rem; font-weight: 600;
}
.banner-chip.alert-chip { background: rgba(239,68,68,.3); border-color: rgba(239,68,68,.5); }
</style>
@endpush

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
    <div>
        <h1 class="m-0 font-weight-bold" style="font-size:1.3rem">
            <i class="fas fa-tachometer-alt mr-2" style="color:#4e73df"></i>My Performance Dashboard
        </h1>
        <p class="text-muted mb-0" style="font-size:.8rem">{{ now()->format('l, d F Y') }}</p>
    </div>
    <a href="{{ route('daily-reports.create') }}" class="btn btn-primary btn-sm" style="border-radius:9px;font-weight:600">
        <i class="fas fa-edit mr-1"></i>Write Today's Report
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid">

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $attColor  = $attRate >= 90 ? '#1cc88a' : ($attRate >= 70 ? '#f6c23e' : '#e74a3b');
    $covColor  = $coveragePct >= 80 ? '#1cc88a' : ($coveragePct >= 50 ? '#4e73df' : '#f6c23e');
@endphp

{{-- Greeting banner ──────────────────────────────────────────────────────── --}}
<div class="greeting-banner">
    <div class="time">{{ $greeting }}</div>
    <div class="name">{{ $user->name }}</div>
    <div class="dept">
        {{ $staff->department->name ?? 'No Department' }}
        &nbsp;·&nbsp; {{ $user->getRoleNames()->first() ?? 'Staff' }}
    </div>
    <div class="meta-chips">
        <span class="banner-chip"><i class="fas fa-calendar mr-1"></i>{{ now()->format('M Y') }}</span>
        @if(!$todayReport)
        <span class="banner-chip alert-chip"><i class="fas fa-bell mr-1"></i>No report today — write now</span>
        @elseif($todayReport->status === 'draft')
        <span class="banner-chip alert-chip"><i class="fas fa-pencil-alt mr-1"></i>Draft report — submit to HOD</span>
        @else
        <span class="banner-chip"><i class="fas fa-check-circle mr-1"></i>Report submitted today</span>
        @endif
        @if($leavesPending)
        <span class="banner-chip alert-chip"><i class="fas fa-hourglass-half mr-1"></i>{{ $leavesPending }} leave pending</span>
        @endif
    </div>
</div>

{{-- KPI Row ──────────────────────────────────────────────────────────────── --}}
<div class="row mb-4">

    {{-- Attendance rate --}}
    <div class="col-6 col-md-3 mb-3">
        <div class="perf-card h-100 position-relative" style="background:linear-gradient(135deg,{{ $attColor }},{{ $attColor }}cc);color:#fff">
            <div class="icon-wrap"><i class="fas fa-user-check"></i></div>
            <div class="inner">
                <div class="num">{{ $attRate }}%</div>
                <div class="lbl">Attendance Rate</div>
            </div>
            <div class="foot text-white">{{ $attPresent }}/{{ $attTotal }} days this month</div>
        </div>
    </div>

    {{-- Sessions this month --}}
    <div class="col-6 col-md-3 mb-3">
        <div class="perf-card h-100 position-relative" style="background:linear-gradient(135deg,#4e73df,#224abe);color:#fff">
            <div class="icon-wrap"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="inner">
                <div class="num">{{ $monthSessionCount }}</div>
                <div class="lbl">Sessions This Month</div>
            </div>
            <div class="foot text-white">{{ $todaySessions->count() }} today · {{ now()->format('M') }}</div>
        </div>
    </div>

    {{-- Curriculum coverage --}}
    <div class="col-6 col-md-3 mb-3">
        <div class="perf-card h-100 position-relative" style="background:linear-gradient(135deg,{{ $covColor }},{{ $covColor }}bb);color:#fff">
            <div class="icon-wrap"><i class="fas fa-book-open"></i></div>
            <div class="inner">
                <div class="num">{{ $coveragePct }}%</div>
                <div class="lbl">Curriculum Coverage</div>
            </div>
            <div class="foot text-white">{{ $coveredSubtopics }}/{{ $totalSubtopics }} subtopics covered</div>
        </div>
    </div>

    {{-- Reports this month --}}
    <div class="col-6 col-md-3 mb-3">
        @php
            $reportDaysInMonth = now()->day; // working days approximation
            $reportPct = $reportDaysInMonth > 0 ? min(100, round(($submittedCount / $reportDaysInMonth) * 100)) : 0;
            $repColor  = $reportPct >= 80 ? '#1cc88a' : ($reportPct >= 50 ? '#f6c23e' : '#e74a3b');
        @endphp
        <div class="perf-card h-100 position-relative" style="background:linear-gradient(135deg,{{ $repColor }},{{ $repColor }}cc);color:#fff">
            <div class="icon-wrap"><i class="fas fa-clipboard-check"></i></div>
            <div class="inner">
                <div class="num">{{ $submittedCount }}</div>
                <div class="lbl">Reports Submitted</div>
            </div>
            <div class="foot text-white">{{ $monthReports->count() }} written · {{ now()->format('M') }}</div>
        </div>
    </div>

</div>

<div class="row">

{{-- Left column ─────────────────────────────────────────────────────────── --}}
<div class="col-lg-8">

    {{-- Today's sessions --}}
    <div class="card sec-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-clock mr-2" style="color:#4e73df"></i>Today's Sessions</span>
            <span class="badge badge-primary" style="border-radius:20px;font-size:.75rem">{{ $todaySessions->count() }}</span>
        </div>
        <div class="card-body">
            @forelse($todaySessions as $sess)
            @php
                $topicName = $sess->topic->title ?? $sess->topic_covered ?? null;
                $dotColor  = $topicName ? '#1cc88a' : '#f6c23e';
                $statusTxt = $topicName ? 'Covered' : 'Pending';
            @endphp
            <div class="session-row">
                <div class="session-dot" style="background:{{ $dotColor }}"></div>
                <div class="flex-grow-1">
                    <div class="session-subject">{{ $sess->subject->name ?? '—' }}</div>
                    <div class="session-meta">
                        {{ $sess->schoolClass->name ?? '' }}
                        @if($sess->period) &nbsp;·&nbsp; {{ $sess->period->name ?? '' }} @endif
                    </div>
                </div>
                <span class="session-chip" style="background:{{ $dotColor }}22;color:{{ $dotColor }};border:1px solid {{ $dotColor }}44">
                    {{ $statusTxt }}
                </span>
                @if($topicName)
                <span style="font-size:.75rem;color:#888;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $topicName }}">
                    {{ Str::limit($topicName, 22) }}
                </span>
                @endif
            </div>
            @empty
            <div class="text-center py-4 text-muted">
                <i class="fas fa-calendar-day fa-2x d-block mb-2" style="opacity:.25"></i>
                No sessions logged today
            </div>
            @endforelse
        </div>
    </div>

    {{-- Weekly sessions chart --}}
    <div class="card sec-card mb-3">
        <div class="card-header"><i class="fas fa-chart-bar mr-2" style="color:#4e73df"></i>Weekly Sessions (Last 4 Weeks)</div>
        <div class="card-body" style="padding:.8rem 1.2rem">
            <canvas id="weeklyChart" height="100"></canvas>
        </div>
    </div>

    {{-- Curriculum coverage per plan --}}
    <div class="card sec-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-book-open mr-2" style="color:#4e73df"></i>Curriculum Coverage by Subject</span>
            <a href="{{ route('topic-coverage.index') }}" class="btn btn-outline-primary btn-sm" style="border-radius:8px;font-size:.75rem">View All</a>
        </div>
        <div class="card-body">
            @forelse($lessonPlans as $plan)
            @php
                $planTotal   = 0; $planCovered = 0;
                foreach ($plan->topics as $topic) {
                    $planTotal   += $topic->subtopics->count();
                    $planCovered += $topic->subtopics->where('status', 'covered')->count();
                }
                $planPct   = $planTotal > 0 ? round(($planCovered / $planTotal) * 100) : 0;
                $planColor = $planPct >= 80 ? '#1cc88a' : ($planPct >= 50 ? '#4e73df' : '#f6c23e');
            @endphp
            <div class="cov-plan">
                <div class="cov-label">
                    <span>{{ $plan->subject->name ?? '—' }} <span class="text-muted font-weight-normal" style="font-size:.73rem">({{ $plan->schoolClass->name ?? '' }})</span></span>
                    <span style="color:{{ $planColor }}">{{ $planPct }}%</span>
                </div>
                <div class="cov-bar">
                    <div class="cov-fill" style="width:{{ $planPct }}%;background:{{ $planColor }}"></div>
                </div>
            </div>
            @empty
            <div class="text-center py-3 text-muted" style="font-size:.85rem">
                <i class="fas fa-book fa-lg d-block mb-2" style="opacity:.25"></i>No lesson plans assigned
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Right column ────────────────────────────────────────────────────────── --}}
<div class="col-lg-4">

    {{-- Daily report heatmap this month --}}
    <div class="card sec-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-clipboard-list mr-2" style="color:#4e73df"></i>Reports — {{ now()->format('M Y') }}</span>
            <a href="{{ route('daily-reports.index') }}" style="font-size:.75rem;color:#4e73df">All →</a>
        </div>
        <div class="card-body">
            {{-- Legend --}}
            <div class="d-flex align-items-center mb-3" style="gap:.75rem;font-size:.73rem">
                <span><span style="display:inline-block;width:12px;height:12px;background:#1cc88a;border-radius:3px;margin-right:3px"></span>Submitted</span>
                <span><span style="display:inline-block;width:12px;height:12px;background:#f6c23e;border-radius:3px;margin-right:3px"></span>Draft</span>
                <span><span style="display:inline-block;width:12px;height:12px;background:#e8eaf0;border-radius:3px;margin-right:3px"></span>Missing</span>
            </div>
            {{-- Dot grid: one square per calendar day up to today --}}
            <div class="report-dot-grid">
                @for($d = 1; $d <= now()->day; $d++)
                @php
                    $date = now()->startOfMonth()->copy()->addDays($d - 1);
                    $rpt  = $monthReports->first(fn($r) => $r->report_date->isSameDay($date));
                    $bg   = !$rpt ? '#e8eaf0' : ($rpt->status === 'submitted' ? '#1cc88a' : '#f6c23e');
                    $tip  = $date->format('d M') . ': ' . (!$rpt ? 'No report' : ucfirst($rpt->status));
                @endphp
                <div class="report-dot tip-trigger" style="background:{{ $bg }}" data-tip="{{ $tip }}">
                    {{ $d }}
                </div>
                @endfor
            </div>
            {{-- Summary row --}}
            <div class="d-flex justify-content-between mt-3" style="font-size:.78rem;color:#666">
                <span><strong style="color:#1cc88a">{{ $submittedCount }}</strong> submitted</span>
                <span><strong style="color:#f6c23e">{{ $monthReports->where('status','draft')->count() }}</strong> draft</span>
                <span><strong style="color:#e74a3b">{{ now()->day - $monthReports->count() }}</strong> missing</span>
            </div>
        </div>
    </div>

    {{-- Leave summary --}}
    <div class="card sec-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-umbrella-beach mr-2" style="color:#4e73df"></i>Leave This Year</span>
            <a href="{{ route('leaves.index') }}" style="font-size:.75rem;color:#4e73df">View →</a>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-around text-center">
                <div>
                    <div style="font-size:1.6rem;font-weight:800;color:#4e73df">{{ $leavesApproved }}</div>
                    <div style="font-size:.72rem;color:#888;text-transform:uppercase">Days Taken</div>
                </div>
                <div style="width:1px;background:#eee"></div>
                <div>
                    <div style="font-size:1.6rem;font-weight:800;color:{{ $leavesPending ? '#f6c23e' : '#aaa' }}">{{ $leavesPending }}</div>
                    <div style="font-size:.72rem;color:#888;text-transform:uppercase">Pending</div>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('leaves.create') }}" class="btn btn-block btn-outline-primary btn-sm" style="border-radius:8px;font-size:.8rem">
                    <i class="fas fa-plus mr-1"></i>Apply for Leave
                </a>
            </div>
        </div>
    </div>

    {{-- Upcoming events --}}
    @if($upcomingEvents->count())
    <div class="card sec-card mb-3">
        <div class="card-header"><i class="fas fa-calendar-alt mr-2" style="color:#4e73df"></i>Upcoming Events</div>
        <div class="card-body p-0">
            @foreach($upcomingEvents as $ev)
            @php
                $evColor = match($ev->type ?? '') {
                    'academic' => '#4e73df', 'sport' => '#1cc88a',
                    'cultural' => '#f6c23e', 'holiday' => '#e74a3b',
                    default => '#888',
                };
            @endphp
            <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f0f2f8">
                <div style="width:4px;height:36px;background:{{ $evColor }};border-radius:4px;flex-shrink:0"></div>
                <div>
                    <div style="font-weight:600;font-size:.82rem">{{ $ev->title }}</div>
                    <div style="font-size:.72rem;color:#888">{{ \Carbon\Carbon::parse($ev->start_date)->format('d M Y') }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Quick links --}}
    <div class="card sec-card mb-3">
        <div class="card-header"><i class="fas fa-bolt mr-2" style="color:#4e73df"></i>Quick Actions</div>
        <div class="card-body p-2">
            <div class="row" style="gap:0">
                @php
                    $links = [
                        ['icon'=>'fas fa-edit','label'=>'Daily Report','route'=>'daily-reports.create','color'=>'#4e73df'],
                        ['icon'=>'fas fa-chalkboard','label'=>'My Sessions','route'=>'timetables.my-sessions','color'=>'#1cc88a'],
                        ['icon'=>'fas fa-book','label'=>'Lesson Plans','route'=>'topic-coverage.index','color'=>'#f6c23e'],
                        ['icon'=>'fas fa-umbrella-beach','label'=>'Apply Leave','route'=>'leaves.create','color'=>'#e74a3b'],
                    ];
                @endphp
                @foreach($links as $lnk)
                <div class="col-6 p-1">
                    <a href="{{ route($lnk['route']) }}"
                        style="display:flex;flex-direction:column;align-items:center;padding:.75rem .5rem;border-radius:10px;background:{{ $lnk['color'] }}12;border:1.5px solid {{ $lnk['color'] }}30;text-decoration:none;transition:background .15s"
                        onmouseover="this.style.background='{{ $lnk['color'] }}22'" onmouseout="this.style.background='{{ $lnk['color'] }}12'">
                        <i class="{{ $lnk['icon'] }}" style="font-size:1.3rem;color:{{ $lnk['color'] }};margin-bottom:4px"></i>
                        <span style="font-size:.72rem;font-weight:600;color:#333;text-align:center">{{ $lnk['label'] }}</span>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
</div>

</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('weeklyChart'), {
    type: 'bar',
    data: {
        labels: @json($weeklyLabels),
        datasets: [{
            label: 'Sessions',
            data:  @json($weeklyCounts),
            backgroundColor: 'rgba(78,115,223,.65)',
            borderColor:     '#4e73df',
            borderWidth: 1.5,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0f2f8' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
