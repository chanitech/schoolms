@extends('adminlte::page')

@section('title', 'Dashboard')

@php
    $user = auth()->user();
    $isTeacher = $user->hasRole('Teacher');
    $isAdmin   = $user->hasAnyRole(['Admin','Academic','HOD','HR']);
@endphp

@section('content_header')
    <div class="d-flex justify-content-between align-items-end flex-wrap">
        <div>
            <h1 class="m-0">Dashboard</h1>
            <small class="text-muted">
                Signed in as <strong>{{ $user->name ?? ($user->first_name . ' ' . $user->last_name) }}</strong>
                ({{ $user->getRoleNames()->implode(', ') }})
                @if($currentSession ?? null) · {{ $currentSession->name }} @endif
            </small>
        </div>
        <span class="text-muted">{{ now()->format('l, d F Y') }}</span>
    </div>
@stop

@section('content')
@include('partials.flash')

{{-- Key figures --}}
<div class="row">
    @if($canViewStudents)
    <div class="col-lg-4 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-user-graduate"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Students</span>
                <span class="info-box-number">{{ number_format($studentCount) }}</span>
                @if(isset($studentGrowth) && $studentGrowth !== null)
                    <span class="small text-muted">{{ $studentGrowth >= 0 ? '+' : '−' }}{{ abs($studentGrowth) }}% this month</span>
                @endif
                <a href="{{ route('students.index') }}" class="small">Student register</a>
            </div>
        </div>
    </div>
    @endif
    @if($canViewStaff)
    <div class="col-lg-4 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-chalkboard-teacher"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Staff Members</span>
                <span class="info-box-number">{{ number_format($staffCount) }}</span>
                <span class="small text-muted">{{ $staffPresentToday }} present today ({{ $staffAttendanceRate }}%)</span>
                <a href="{{ route('staff.index') }}" class="small">Staff list</a>
            </div>
        </div>
    </div>
    @endif
    @if($canViewClasses)
    <div class="col-lg-4 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-school"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Classes</span>
                <span class="info-box-number">{{ $classCount }}</span>
                <span class="small text-muted">{{ $subjectCount }} subjects</span>
                <a href="{{ route('classes.index') }}" class="small">View classes</a>
            </div>
        </div>
    </div>
    @endif
    @if($canViewPayments)
    <div class="col-lg-4 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Collected Today</span>
                <span class="info-box-number">{{ number_format($todayCollection) }} <small>TZS</small></span>
                <span class="small text-muted">{{ number_format($monthCollection) }} TZS this month</span>
                <a href="{{ route('finance.payments.index') }}" class="small">Payments</a>
            </div>
        </div>
    </div>
    @endif
    @if($canViewTimetables)
    <div class="col-lg-4 col-md-6">
        <div class="info-box">
            <span class="info-box-icon {{ $canViewLeaves && $pendingLeaves > 0 ? 'bg-warning' : 'bg-primary' }}"><i class="fas fa-calendar-week"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active Timetables</span>
                <span class="info-box-number">{{ $publishedTimetables }}</span>
                @if($canViewLeaves)
                    <span class="small text-muted">{{ $pendingLeaves > 0 ? $pendingLeaves . ' leave request(s) pending' : 'No pending leave requests' }}</span>
                @endif
                <a href="{{ route('timetables.index', ['status'=>'published','type'=>'class']) }}" class="small">Timetables</a>
            </div>
        </div>
    </div>
    @endif
    @if($canViewDorms)
    <div class="col-lg-4 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-bed"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Dormitories</span>
                <span class="info-box-number">{{ $dormCount }}</span>
                <span class="small text-muted">{{ $departmentCount }} departments</span>
                <a href="{{ route('dormitories.index') }}" class="small">View dormitories</a>
            </div>
        </div>
    </div>
    @endif
</div>
@if(!$canViewStudents && !$canViewStaff && !$canViewClasses && !$canViewPayments && !$canViewTimetables && !$canViewDorms)
<div class="alert alert-light border text-muted">
    <i class="fas fa-info-circle mr-1"></i> Your role has no school-wide figures to show here. Use the sidebar menu for your tasks.
</div>
@endif

<div class="row">
    {{-- Today's school schedule --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-day mr-1"></i> Today's School Schedule</h3>
                <div class="card-tools">
                    <a href="{{ route('timetables.index', ['status'=>'published','type'=>'class']) }}" class="btn btn-sm btn-default">All Timetables</a>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="todayScheduleBody">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Loading today's schedule…
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Notice board --}}
        @if($notices->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> Notice Board</h3>
                <div class="card-tools">
                    <a href="{{ route('notices.index') }}" class="btn btn-sm btn-default">View All</a>
                </div>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($notices as $notice)
                <li class="list-group-item">
                    <div class="font-weight-bold small">
                        @if($notice->pinned)<i class="fas fa-thumbtack text-muted mr-1" title="Pinned"></i>@endif
                        {{ $notice->title }}
                        @if($notice->created_at->gt(now()->subDays(3)))
                            <span class="badge badge-success ml-1">New</span>
                        @endif
                    </div>
                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($notice->body, 90) }}</div>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Library --}}
        @if($canViewLibrary)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-book mr-1"></i> Library</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr><td>Total books</td><td class="text-right font-weight-bold">{{ number_format($libraryStats['books'] ?? 0) }}</td></tr>
                    <tr><td>Currently borrowed</td><td class="text-right font-weight-bold">{{ $libraryStats['active_borrowings'] ?? 0 }}</td></tr>
                    <tr><td>Issued this month</td><td class="text-right font-weight-bold">{{ $libraryStats['issued_this_month'] ?? 0 }}</td></tr>
                </table>
            </div>
            @if(($libraryStats['books'] ?? 0) > 0)
            @php $borrowPct = round(($libraryStats['active_borrowings'] ?? 0) / max($libraryStats['books'], 1) * 100); @endphp
            <div class="card-footer">
                <div class="progress progress-sm mb-1">
                    <div class="progress-bar bg-primary" style="width:{{ min(100, $borrowPct) }}%"></div>
                </div>
                <small class="text-muted">{{ $borrowPct }}% of books currently borrowed</small>
            </div>
            @endif
        </div>
        @endif

        {{-- Quick actions --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-link mr-1"></i> Quick Actions</h3>
            </div>
            <div class="list-group list-group-flush">
                @if($isAdmin)
                    <a href="{{ route('students.create') }}" class="list-group-item list-group-item-action"><i class="fas fa-user-plus fa-fw mr-2 text-muted"></i>Add Student</a>
                    <a href="{{ route('marks.create') }}" class="list-group-item list-group-item-action"><i class="fas fa-pen-alt fa-fw mr-2 text-muted"></i>Enter Marks</a>
                    <a href="{{ route('finance.payments.index') }}" class="list-group-item list-group-item-action"><i class="fas fa-credit-card fa-fw mr-2 text-muted"></i>Payments</a>
                    <a href="{{ route('timetables.create') }}" class="list-group-item list-group-item-action"><i class="fas fa-calendar-plus fa-fw mr-2 text-muted"></i>New Timetable</a>
                    <a href="{{ route('events.create') }}" class="list-group-item list-group-item-action"><i class="fas fa-calendar-alt fa-fw mr-2 text-muted"></i>Add Event</a>
                    <a href="{{ route('hr-reports.index') }}" class="list-group-item list-group-item-action"><i class="fas fa-chart-bar fa-fw mr-2 text-muted"></i>HR Reports</a>
                @endif
                @if($isTeacher)
                    <a href="{{ route('timetables.my-sessions') }}" class="list-group-item list-group-item-action"><i class="fas fa-chalkboard-teacher fa-fw mr-2 text-muted"></i>My Sessions</a>
                    <a href="{{ route('marks.create') }}" class="list-group-item list-group-item-action"><i class="fas fa-pen-alt fa-fw mr-2 text-muted"></i>Enter Marks</a>
                    <a href="{{ route('topic-coverage.index') }}" class="list-group-item list-group-item-action"><i class="fas fa-tasks fa-fw mr-2 text-muted"></i>Lesson Plans</a>
                    <a href="{{ route('results.class') }}" class="list-group-item list-group-item-action"><i class="fas fa-chart-line fa-fw mr-2 text-muted"></i>Results</a>
                @endif
                @if($canViewLibrary)
                    <a href="{{ route('library.lendings.create') }}" class="list-group-item list-group-item-action"><i class="fas fa-book fa-fw mr-2 text-muted"></i>Lend Book</a>
                @endif
                @if($canViewEvents)
                    <a href="{{ route('events.calendar') }}" class="list-group-item list-group-item-action"><i class="fas fa-calendar fa-fw mr-2 text-muted"></i>Calendar</a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Analytics --}}
@if($canViewPayments || $canViewStudents || $canViewClasses || $canViewStaff)
<div class="row">
    @if($canViewPayments && array_sum($feeTrend['values']))
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Fee Collection — Last 6 Months</h3></div>
            <div class="card-body"><canvas id="feeTrendChart" style="max-height:240px"></canvas></div>
        </div>
    </div>
    @endif

    @if($canViewClasses && count($examPerformance['labels']))
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Average Mark per Class — {{ $examPerformance['exam'] }}</h3></div>
            <div class="card-body"><canvas id="examPerformanceChart" style="max-height:240px"></canvas></div>
        </div>
    </div>
    @endif

    @if($canViewStudents && count($classDistribution['labels']))
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Students per Class</h3></div>
            <div class="card-body"><canvas id="classDistChart" style="max-height:230px"></canvas></div>
        </div>
    </div>
    @endif

    @if($canViewStudents && count($genderSplit['labels']))
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Gender</h3></div>
            <div class="card-body"><canvas id="genderChart" style="max-height:230px"></canvas></div>
        </div>
    </div>
    @endif

    @if($canViewStaff && array_sum($sessionWeek))
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Teacher Sessions This Week</h3>
                <div class="card-tools">
                    <a href="{{ route('timetables.class-attendance.report') }}" class="btn btn-sm btn-default">Full report</a>
                </div>
            </div>
            <div class="card-body"><canvas id="sessionWeekChart" style="max-height:200px"></canvas></div>
        </div>
    </div>
    @endif
</div>
@endif

<div class="row">
    {{-- Upcoming events --}}
    @if($canViewEvents)
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i> Upcoming Events</h3>
                <div class="card-tools">
                    <a href="{{ route('events.calendar') }}" class="btn btn-sm btn-default">View All</a>
                </div>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($upcomingEvents as $event)
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <div class="font-weight-bold small">{{ $event->title }}</div>
                            <div class="small text-muted">{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}</div>
                        </div>
                        <span class="badge badge-secondary">{{ ucfirst($event->type) }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted py-4">No upcoming events</li>
                @endforelse
            </ul>
        </div>
    </div>
    @endif

    {{-- Recent enrollments --}}
    @if($canViewStudents)
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-graduate mr-1"></i> Recent Enrollments</h3>
                <div class="card-tools">
                    <a href="{{ route('students.index') }}" class="btn btn-sm btn-default">View All</a>
                </div>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($recentStudents as $student)
                    <li class="list-group-item">
                        <div class="font-weight-bold small">{{ $student->name ?? ($student->first_name . ' ' . $student->last_name) }}</div>
                        <div class="small text-muted">
                            {{ ucfirst($student->gender ?? '') }} · {{ \Carbon\Carbon::parse($student->created_at)->diffForHumans() }}
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted py-4">No recent enrollments</li>
                @endforelse
            </ul>
        </div>
    </div>
    @endif

    {{-- Event distribution --}}
    @if($canViewEvents)
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Event Distribution</h3></div>
            <div class="card-body">
                @if($eventStats->count() > 0)
                    <canvas id="eventChart" style="max-height:200px"></canvas>
                @else
                    <div class="text-center text-muted py-4">No event data yet</div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Event calendar --}}
@if($canViewEvents)
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i> Event Calendar</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body">
        <div id="dashboardCalendar"></div>
    </div>
</div>
@endif

@stop


@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
{{-- v6 ships one global bundle (styles included); main.min.js/css were v5 paths and 404 --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Event chart ─────────────────────────────────────────────────
    const eventChartEl = document.getElementById('eventChart');
    if (eventChartEl) {
        new Chart(eventChartEl, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($eventStats->keys()) !!},
                datasets: [{
                    data: {!! json_encode($eventStats->values()) !!},
                    backgroundColor: ['#1976d2','#43a047','#f57c00','#e53935','#6c757d','#00897b'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } }
                }
            }
        });
    }

    // ── Analytics charts ────────────────────────────────────────────
    const mkChart = (id, cfg) => {
        const el = document.getElementById(id);
        if (el) new Chart(el, cfg);
    };

    mkChart('feeTrendChart', {
        type: 'line',
        data: {
            labels: @json($feeTrend['labels'] ?? []),
            datasets: [{
                label: 'TZS collected',
                data: @json($feeTrend['values'] ?? []),
                borderColor: '#2e7d32',
                backgroundColor: 'rgba(46,125,50,.12)',
                fill: true, tension: .35, pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { ticks: { callback: v => v >= 1000000 ? (v/1000000)+'M' : (v >= 1000 ? (v/1000)+'K' : v) } } }
        }
    });

    mkChart('examPerformanceChart', {
        type: 'bar',
        data: {
            labels: @json($examPerformance['labels'] ?? []),
            datasets: [{
                label: 'Average mark',
                data: @json($examPerformance['values'] ?? []),
                backgroundColor: '#5e35b1', borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { min: 0, max: 100 } }
        }
    });

    mkChart('classDistChart', {
        type: 'bar',
        data: {
            labels: @json($classDistribution['labels'] ?? []),
            datasets: [{
                label: 'Students',
                data: @json($classDistribution['values'] ?? []),
                backgroundColor: '#1976d2', borderRadius: 6,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    mkChart('genderChart', {
        type: 'doughnut',
        data: {
            labels: @json($genderSplit['labels'] ?? []),
            datasets: [{
                data: @json($genderSplit['values'] ?? []),
                backgroundColor: ['#1976d2', '#e91e63', '#6c757d'],
                borderWidth: 0,
            }]
        },
        options: { responsive: true, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
    });

    mkChart('sessionWeekChart', {
        type: 'doughnut',
        data: {
            labels: ['Attended', 'Late', 'Absent', 'Not marked'],
            datasets: [{
                data: @json(array_values($sessionWeek)),
                backgroundColor: ['#2e7d32', '#f9a825', '#c62828', '#9e9e9e'],
                borderWidth: 0,
            }]
        },
        options: { responsive: true, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
    });

    // ── FullCalendar ────────────────────────────────────────────────
    const calendarEl = document.getElementById('dashboardCalendar');
    if (calendarEl) {
        new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
            events: @json($calendarEvents ?? []),
            eventDisplay: 'block',
        }).render();
    }
});
</script>

{{-- ── Today's Schedule (AJAX) ──────────────────────────────────────── --}}
<script>
(function () {
    const scheduleUrl = '{{ route("timetables.today-schedule") }}';
    const csrfToken   = '{{ csrf_token() }}';
    const todayDate   = '{{ now()->toDateString() }}';
    const isWeekend   = [0, 6].includes(new Date().getDay());

    if (isWeekend) {
        document.getElementById('todayScheduleBody').innerHTML =
            '<div class="text-center text-muted py-4">No classes are scheduled on weekends.</div>';
        return;
    }

    const SS_ICONS = { assembly:'🏁', prayer:'🕌', break:'☕', self_study:'📚', sports:'⚽', sdp:'📖', debate:'🗣️', free:'🌙' };
    const SS_ROW_CLASS = { assembly:'table-secondary', prayer:'table-info', break:'table-warning', self_study:'table-success', sports:'table-danger', sdp:'', debate:'table-primary', free:'table-light' };
    const STATUS_CFG = {
        attended: { label:'Attended', btnClass:'btn-success',   icon:'fa-check-circle' },
        late:     { label:'Late',     btnClass:'btn-warning',   icon:'fa-clock' },
        absent:   { label:'Absent',   btnClass:'btn-danger',    icon:'fa-times-circle' },
        other:    { label:'Other',    btnClass:'btn-secondary', icon:'fa-question-circle' },
    };
    const BADGE_COLOR = { attended:'success', late:'warning', absent:'danger', other:'secondary' };

    function buildStatusCell(entryId, currentStatus, canLog) {
        if (!canLog) {
            if (!currentStatus) return '<span class="text-muted small">—</span>';
            const cfg = STATUS_CFG[currentStatus];
            return `<span class="badge badge-${BADGE_COLOR[currentStatus]} px-2 py-1"><i class="fas ${cfg.icon} mr-1"></i>${cfg.label}</span>`;
        }
        if (currentStatus) {
            const cfg   = STATUS_CFG[currentStatus];
            const color = BADGE_COLOR[currentStatus];
            const changeBtns = Object.entries(STATUS_CFG)
                .filter(([k]) => k !== currentStatus)
                .map(([k,c]) => `<button class="btn btn-xs btn-outline-${BADGE_COLOR[k]} session-btn" data-entry="${entryId}" data-status="${k}" title="${c.label}"><i class="fas ${c.icon}"></i></button>`)
                .join(' ');
            return `<div class="d-flex align-items-center flex-wrap" style="gap:.25rem">
                <span class="badge badge-${color} px-2 py-1" style="font-size:.82rem"><i class="fas ${cfg.icon} mr-1"></i>${cfg.label}</span>
                <small class="text-muted">Change:</small>${changeBtns}
            </div>`;
        }
        return `<div class="d-flex flex-wrap" style="gap:.25rem">
            ${Object.entries(STATUS_CFG).map(([k,c]) =>
                `<button class="btn btn-sm ${c.btnClass} session-btn" data-entry="${entryId}" data-status="${k}">
                    <i class="fas ${c.icon} mr-1"></i>${c.label}
                </button>`
            ).join('')}
        </div>`;
    }

    function attachHandlers() {
        document.querySelectorAll('#todayScheduleBody .session-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const entryId = this.dataset.entry;
                const status  = this.dataset.status;
                const row     = this.closest('tr');
                row.querySelectorAll('.session-btn').forEach(b => b.disabled = true);
                fetch(`/timetables/entries/${entryId}/log-ajax`, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrfToken, 'Accept':'application/json' },
                    body: JSON.stringify({ session_date: todayDate, status })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const cell = row.querySelector('.status-cell');
                        if (cell) { cell.innerHTML = buildStatusCell(entryId, res.status, true); attachHandlers(); }
                        // Row highlight
                        row.className = row.className.replace(/table-(success|warning|danger)/g, '');
                        const highlight = { attended:'table-success', late:'table-warning', absent:'table-danger' };
                        if (highlight[res.status]) row.classList.add(highlight[res.status]);
                    }
                })
                .catch(() => { row.querySelectorAll('.session-btn').forEach(b => b.disabled = false); });
            });
        });
    }

    fetch(scheduleUrl, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            const body = document.getElementById('todayScheduleBody');
            if (!data.entries?.length) {
                body.innerHTML = `<div class="text-center text-muted py-4"><i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>${data.message || 'No published timetable entries for today.'}</div>`;
                return;
            }
            const hasStatus = data.entries.some(e => !e.is_special && e.can_log !== undefined);
            const thStatus  = hasStatus ? '<th class="text-center" style="min-width:310px">Status</th>' : '';
            const cols      = hasStatus ? 7 : 6;
            let html = `<div class="table-responsive"><table class="table table-sm table-hover mb-0">
                <thead class="thead-light"><tr>
                    <th>Period / Session</th><th>Time</th><th>Subject</th><th>Class</th><th>Teacher</th><th>Room</th>${thStatus}
                </tr></thead><tbody>`;

            data.entries.forEach(e => {
                if (e.is_special) {
                    const icon = SS_ICONS[e.type] || '📌';
                    const rc   = SS_ROW_CLASS[e.type] || 'table-light';
                    html += `<tr class="${rc}"><td colspan="${cols}" class="text-center small font-italic font-weight-bold text-muted py-1">${icon}&nbsp;${e.period}&nbsp;—&nbsp;${e.time}</td></tr>`;
                } else {
                    const highlight = e.log_status ? ({attended:'table-success',late:'table-warning',absent:'table-danger'}[e.log_status] || '') : '';
                    const statusTd = hasStatus
                        ? `<td class="status-cell align-middle">${buildStatusCell(e.entry_id, e.log_status, e.can_log)}</td>`
                        : '';
                    html += `<tr class="${highlight}">
                        <td><span class="badge badge-primary badge-pill">${e.period ?? '—'}</span></td>
                        <td class="small text-nowrap">${e.time ?? '—'}</td>
                        <td class="font-weight-bold">${e.subject ?? '—'}</td>
                        <td>${e.class ?? '—'}</td>
                        <td class="small">${e.teacher ?? '—'}</td>
                        <td class="small">${e.room ?? '—'}</td>
                        ${statusTd}
                    </tr>`;
                }
            });
            html += '</tbody></table></div>';
            body.innerHTML = html;
            attachHandlers();
        })
        .catch(() => {
            document.getElementById('todayScheduleBody').innerHTML =
                '<div class="text-center text-muted py-4"><i class="fas fa-wifi-slash fa-2x mb-2 d-block"></i>Could not load schedule.</div>';
        });
})();
</script>

@stop
