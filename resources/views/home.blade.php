@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
@stop

@section('content')
@include('partials.flash')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $user = auth()->user();
    $isTeacher = $user->hasRole('Teacher');
    $isAdmin   = $user->hasAnyRole(['Admin','Academic','HOD','HR']);
@endphp

{{-- ══════════════════════════════════════════════════════════════════════
     WELCOME HERO BANNER
══════════════════════════════════════════════════════════════════════ --}}
<div class="welcome-hero mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:1rem">
        <div class="d-flex align-items-center" style="gap:1rem">
            <div class="hero-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div>
                <div class="hero-greeting">{{ $greeting }},</div>
                <div class="hero-name">{{ $user->name ?? ($user->first_name . ' ' . $user->last_name) }}</div>
                <div class="hero-meta">
                    @foreach($user->getRoleNames() as $role)
                        <span class="role-badge">{{ $role }}</span>
                    @endforeach
                    @if($currentSession ?? null)
                        <span class="session-badge">
                            <i class="fas fa-graduation-cap mr-1"></i>{{ $currentSession->name }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="hero-date">
            <div class="date-day">{{ now()->format('l') }}</div>
            <div class="date-full">{{ now()->format('d F Y') }}</div>
            <div class="date-time" id="liveClock"></div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     STATS ROW
══════════════════════════════════════════════════════════════════════ --}}
<div class="row stats-row">
    @if($canViewStudents)
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
            <div class="stat-body">
                <div class="stat-number">{{ number_format($studentCount) }}</div>
                <div class="stat-label">Students</div>
                @if(isset($studentGrowth) && $studentGrowth !== null)
                    <div class="stat-growth {{ $studentGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-arrow-{{ $studentGrowth >= 0 ? 'up' : 'down' }} mr-1"></i>{{ abs($studentGrowth) }}% this month
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
    @if($canViewStaff)
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card stat-teal">
            <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="stat-body">
                <div class="stat-number">{{ number_format($staffCount) }}</div>
                <div class="stat-label">Staff Members</div>
                <div class="stat-growth text-info">
                    <i class="fas fa-circle mr-1"></i>{{ $staffPresentToday }} present today
                </div>
            </div>
        </div>
    </div>
    @endif
    @if($canViewClasses)
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="fas fa-school"></i></div>
            <div class="stat-body">
                <div class="stat-number">{{ $classCount }}</div>
                <div class="stat-label">Classes</div>
                <div class="stat-growth text-muted">{{ $subjectCount }} subjects</div>
            </div>
        </div>
    </div>
    @endif
    @if($canViewPayments)
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-body">
                <div class="stat-number">{{ number_format($todayCollection) }}</div>
                <div class="stat-label">Today's Collections</div>
                <div class="stat-growth text-muted">{{ number_format($monthCollection) }} this month</div>
            </div>
        </div>
    </div>
    @endif
    @if($canViewTimetables)
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card stat-orange">
            <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
            <div class="stat-body">
                <div class="stat-number">{{ $publishedTimetables }}</div>
                <div class="stat-label">Active Timetables</div>
                @if($canViewLeaves)
                    @if($pendingLeaves > 0)
                        <div class="stat-growth text-warning">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $pendingLeaves }} leave(s) pending
                        </div>
                    @else
                        <div class="stat-growth text-success"><i class="fas fa-check mr-1"></i>No pending leaves</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    @endif
    @if($canViewDorms)
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card stat-red">
            <div class="stat-icon"><i class="fas fa-bed"></i></div>
            <div class="stat-body">
                <div class="stat-number">{{ $dormCount }}</div>
                <div class="stat-label">Dormitories</div>
                <div class="stat-growth text-muted">{{ $departmentCount }} departments</div>
            </div>
        </div>
    </div>
    @endif
</div>
@if(!$canViewStudents && !$canViewStaff && !$canViewClasses && !$canViewPayments && !$canViewTimetables && !$canViewDorms)
<div class="row stats-row">
    <div class="col-12">
        <div class="alert alert-light border text-muted mb-3" style="border-radius:12px">
            <i class="fas fa-info-circle mr-1"></i> Your role doesn't have school-wide statistics to show here — use the sidebar menu for what you're set up to do.
        </div>
    </div>
</div>
@endif

@if($canViewStaff)
{{-- ══════════════════════════════════════════════════════════════════════
     STAFF ATTENDANCE PROGRESS BAR
══════════════════════════════════════════════════════════════════════ --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="attendance-bar-card">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="font-weight-bold text-dark">
                    <i class="fas fa-user-check text-success mr-2"></i>Staff Attendance Today
                </span>
                <span class="font-weight-bold {{ $staffAttendanceRate >= 80 ? 'text-success' : ($staffAttendanceRate >= 60 ? 'text-warning' : 'text-danger') }}">
                    {{ $staffPresentToday }} / {{ $staffCount }} &nbsp;·&nbsp; {{ $staffAttendanceRate }}%
                </span>
            </div>
            <div class="progress" style="height:10px;border-radius:8px">
                <div class="progress-bar {{ $staffAttendanceRate >= 80 ? 'bg-success' : ($staffAttendanceRate >= 60 ? 'bg-warning' : 'bg-danger') }}"
                     style="width:{{ $staffAttendanceRate }}%;border-radius:8px;transition:width 1s ease"></div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════
     MAIN CONTENT: Schedule | Events | Quick Actions
══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    {{-- TODAY'S SCHOOL SCHEDULE --}}
    <div class="{{ $isTeacher ? 'col-lg-8' : 'col-lg-8' }}">
        <div class="card shadow-sm border-0 mb-3" style="border-radius:12px;overflow:hidden">
            <div class="card-header d-flex align-items-center justify-content-between"
                 style="background:linear-gradient(135deg,#00897b,#00695c);color:#fff">
                <div>
                    <i class="fas fa-calendar-day mr-2"></i>
                    <strong>Today's School Schedule</strong>
                    <small class="ml-2 opacity-75">{{ now()->format('l, d M Y') }}</small>
                </div>
                <div class="d-flex" style="gap:.5rem">
                    <a href="{{ route('timetables.index', ['status'=>'published','type'=>'class']) }}"
                       class="btn btn-sm btn-light">
                        <i class="fas fa-list mr-1"></i>All Timetables
                    </a>
                    <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="todayScheduleBody">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>Loading today's schedule…
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT SIDEBAR: Quick Stats + Quick Actions --}}
    <div class="col-lg-4">
        {{-- Library Quick Stats --}}
        @if($canViewLibrary)
        <div class="card shadow-sm border-0 mb-3" style="border-radius:12px;overflow:hidden">
            <div class="card-header" style="background:linear-gradient(135deg,#6f42c1,#4a148c);color:#fff">
                <i class="fas fa-book mr-2"></i><strong>Library</strong>
            </div>
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small"><i class="fas fa-book-open mr-1"></i>Total Books</span>
                    <strong>{{ number_format($libraryStats['books'] ?? 0) }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small"><i class="fas fa-hand-holding-heart mr-1"></i>Borrowed</span>
                    <strong class="text-warning">{{ $libraryStats['active_borrowings'] ?? 0 }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small"><i class="fas fa-chart-line mr-1"></i>Issued This Month</span>
                    <strong class="text-success">{{ $libraryStats['issued_this_month'] ?? 0 }}</strong>
                </div>
                @if(($libraryStats['books'] ?? 0) > 0)
                    <div class="progress mt-2" style="height:6px;border-radius:4px">
                        <div class="progress-bar bg-purple"
                             style="width:{{ min(100, round(($libraryStats['active_borrowings'] ?? 0) / max($libraryStats['books'], 1) * 100)) }}%;background:#6f42c1"></div>
                    </div>
                    <small class="text-muted">{{ round(($libraryStats['active_borrowings'] ?? 0) / max($libraryStats['books'] ?? 1, 1) * 100) }}% of books currently borrowed</small>
                @endif
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="card shadow-sm border-0" style="border-radius:12px;overflow:hidden">
            <div class="card-header" style="background:linear-gradient(135deg,#e65100,#bf360c);color:#fff">
                <i class="fas fa-bolt mr-2"></i><strong>Quick Actions</strong>
            </div>
            <div class="card-body p-3">
                <div class="quick-actions-grid">
                    @if($isAdmin)
                        <a href="{{ route('students.create') }}" class="qa-btn qa-blue">
                            <i class="fas fa-user-plus"></i><span>Add Student</span>
                        </a>
                        <a href="{{ route('marks.create') }}" class="qa-btn qa-green">
                            <i class="fas fa-pen-alt"></i><span>Enter Marks</span>
                        </a>
                        <a href="{{ route('finance.payments.index') }}" class="qa-btn qa-purple">
                            <i class="fas fa-credit-card"></i><span>Payments</span>
                        </a>
                        <a href="{{ route('timetables.create') }}" class="qa-btn qa-teal">
                            <i class="fas fa-calendar-plus"></i><span>New Timetable</span>
                        </a>
                        <a href="{{ route('events.create') }}" class="qa-btn qa-orange">
                            <i class="fas fa-calendar-alt"></i><span>Add Event</span>
                        </a>
                        <a href="{{ route('hr-reports.index') }}" class="qa-btn qa-red">
                            <i class="fas fa-chart-bar"></i><span>HR Reports</span>
                        </a>
                    @endif
                    @if($isTeacher)
                        <a href="{{ route('timetables.my-sessions') }}" class="qa-btn qa-blue">
                            <i class="fas fa-chalkboard-teacher"></i><span>My Sessions</span>
                        </a>
                        <a href="{{ route('marks.create') }}" class="qa-btn qa-green">
                            <i class="fas fa-pen-alt"></i><span>Enter Marks</span>
                        </a>
                        <a href="{{ route('topic-coverage.index') }}" class="qa-btn qa-teal">
                            <i class="fas fa-tasks"></i><span>Lesson Plans</span>
                        </a>
                        <a href="{{ route('results.class') }}" class="qa-btn qa-purple">
                            <i class="fas fa-chart-line"></i><span>Results</span>
                        </a>
                    @endif
                    @if($canViewLibrary)
                    <a href="{{ route('library.lendings.create') }}" class="qa-btn qa-orange">
                        <i class="fas fa-book"></i><span>Lend Book</span>
                    </a>
                    @endif
                    @if($canViewEvents)
                    <a href="{{ route('events.calendar') }}" class="qa-btn qa-red">
                        <i class="fas fa-calendar"></i><span>Calendar</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     EVENTS + RECENT STUDENTS + CALENDAR
══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    {{-- Upcoming Events --}}
    @if($canViewEvents)
    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm border-0 h-100" style="border-radius:12px;overflow:hidden">
            <div class="card-header d-flex justify-content-between align-items-center"
                 style="background:linear-gradient(135deg,#f57c00,#e65100);color:#fff">
                <div><i class="fas fa-calendar-alt mr-2"></i><strong>Upcoming Events</strong></div>
                <a href="{{ route('events.calendar') }}" class="btn btn-sm btn-light">View All</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($upcomingEvents as $event)
                        @php
                            $evColor = match($event->type) {
                                'academic'=>'primary','sport'=>'success','cultural'=>'warning','holiday'=>'danger',default=>'secondary'
                            };
                        @endphp
                        <li class="list-group-item px-3 py-2 border-left-{{ $evColor }}" style="border-left:4px solid">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="font-weight-bold small">{{ $event->title }}</div>
                                    <div class="text-muted" style="font-size:.78rem">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}
                                    </div>
                                </div>
                                <span class="badge badge-{{ $evColor }} badge-pill">{{ ucfirst($event->type) }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>No upcoming events
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Recent Student Registrations --}}
    @if($canViewStudents)
    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm border-0 h-100" style="border-radius:12px;overflow:hidden">
            <div class="card-header d-flex justify-content-between align-items-center"
                 style="background:linear-gradient(135deg,#37474f,#263238);color:#fff">
                <div><i class="fas fa-user-graduate mr-2"></i><strong>Recent Enrollments</strong></div>
                <a href="{{ route('students.index') }}" class="btn btn-sm btn-light">View All</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentStudents as $student)
                        <li class="list-group-item px-3 py-2">
                            <div class="d-flex align-items-center" style="gap:.75rem">
                                <div class="student-avatar">
                                    {{ strtoupper(substr($student->first_name ?? $student->name ?? 'S', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-weight-bold small">
                                        {{ $student->name ?? ($student->first_name . ' ' . $student->last_name) }}
                                    </div>
                                    <div class="text-muted" style="font-size:.78rem">
                                        {{ ucfirst($student->gender ?? '') }}
                                        · {{ \Carbon\Carbon::parse($student->created_at)->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-4">No recent enrollments</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Event Type Chart --}}
    @if($canViewEvents)
    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm border-0 h-100" style="border-radius:12px;overflow:hidden">
            <div class="card-header"
                 style="background:linear-gradient(135deg,#1565c0,#0d47a1);color:#fff">
                <i class="fas fa-chart-pie mr-2"></i><strong>Event Distribution</strong>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                @if($eventStats->count() > 0)
                    <canvas id="eventChart" style="max-height:200px"></canvas>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-chart-pie fa-2x mb-2 d-block"></i>No event data yet
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Event Calendar --}}
@if($canViewEvents)
<div class="row">
    <div class="col-12 mb-3">
        <div class="card shadow-sm border-0" style="border-radius:12px;overflow:hidden">
            <div class="card-header d-flex align-items-center justify-content-between"
                 style="background:linear-gradient(135deg,#2e7d32,#1b5e20);color:#fff">
                <div><i class="fas fa-calendar-alt mr-2"></i><strong>Event Calendar</strong></div>
                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            <div class="card-body">
                <div id="dashboardCalendar"></div>
            </div>
        </div>
    </div>
</div>
@endif

@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">
<style>
/* ── Welcome Hero ──────────────────────────────────────────────────── */
.welcome-hero {
    background: linear-gradient(135deg, #1a237e 0%, #1565c0 50%, #0288d1 100%);
    border-radius: 16px;
    padding: 1.5rem 2rem;
    color: #fff;
    box-shadow: 0 4px 20px rgba(26,35,126,.25);
}
.hero-avatar { font-size: 3.5rem; opacity: .85; line-height:1; }
.hero-greeting { font-size: .95rem; opacity: .8; font-weight:400; }
.hero-name { font-size: 1.6rem; font-weight: 700; line-height:1.2; }
.hero-meta { margin-top: .4rem; display:flex; gap:.5rem; flex-wrap:wrap; }
.role-badge {
    background: rgba(255,255,255,.2);
    border: 1px solid rgba(255,255,255,.4);
    border-radius: 20px;
    padding: .15rem .75rem;
    font-size: .78rem;
    font-weight: 600;
    letter-spacing: .5px;
}
.session-badge {
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.3);
    border-radius: 20px;
    padding: .15rem .75rem;
    font-size: .78rem;
}
.hero-date { text-align: right; }
.date-day { font-size: 1rem; opacity: .75; }
.date-full { font-size: 1.1rem; font-weight: 600; }
.date-time { font-size: 1.6rem; font-weight: 700; font-variant-numeric: tabular-nums; letter-spacing:.5px; }

/* ── Stats Cards ───────────────────────────────────────────────────── */
.stats-row { gap-y: 1rem; }
.stat-card {
    border-radius: 14px;
    padding: 1.1rem 1rem;
    display: flex;
    align-items: flex-start;
    gap: .9rem;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
    margin-bottom: 1rem;
    transition: transform .15s, box-shadow .15s;
    color: #fff;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.13); }
.stat-icon { font-size: 1.8rem; opacity: .85; min-width:2rem; }
.stat-number { font-size: 1.45rem; font-weight: 700; line-height: 1.1; }
.stat-label { font-size: .78rem; opacity: .85; font-weight: 500; margin-top:.1rem; }
.stat-growth { font-size: .72rem; margin-top: .3rem; opacity: .9; }

.stat-blue   { background: linear-gradient(135deg, #1976d2, #0d47a1); }
.stat-teal   { background: linear-gradient(135deg, #00897b, #004d40); }
.stat-green  { background: linear-gradient(135deg, #43a047, #1b5e20); }
.stat-purple { background: linear-gradient(135deg, #7b1fa2, #4a148c); }
.stat-orange { background: linear-gradient(135deg, #f57c00, #e65100); }
.stat-red    { background: linear-gradient(135deg, #e53935, #b71c1c); }

/* ── Attendance Bar ────────────────────────────────────────────────── */
.attendance-bar-card {
    background: #fff;
    border-radius: 12px;
    padding: .9rem 1.2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
}

/* ── Quick Actions Grid ────────────────────────────────────────────── */
.quick-actions-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
.qa-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    padding: .9rem .5rem;
    border-radius: 10px;
    text-decoration: none;
    font-size: .75rem;
    font-weight: 600;
    color: #fff;
    transition: transform .12s, box-shadow .12s;
    text-align: center;
    line-height: 1.2;
}
.qa-btn i { font-size: 1.25rem; }
.qa-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.18); color:#fff; text-decoration:none; }
.qa-blue   { background: linear-gradient(135deg,#1976d2,#0d47a1); }
.qa-green  { background: linear-gradient(135deg,#43a047,#2e7d32); }
.qa-purple { background: linear-gradient(135deg,#7b1fa2,#4a148c); }
.qa-teal   { background: linear-gradient(135deg,#00897b,#004d40); }
.qa-orange { background: linear-gradient(135deg,#f57c00,#e65100); }
.qa-red    { background: linear-gradient(135deg,#e53935,#b71c1c); }

/* ── Student Avatar ────────────────────────────────────────────────── */
.student-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg,#1976d2,#7b1fa2);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .9rem;
    flex-shrink: 0;
}

/* ── Today Schedule Table ──────────────────────────────────────────── */
#todayScheduleBody .table th { font-size: .8rem; text-transform: uppercase; letter-spacing: .5px; }
#todayScheduleBody .table td { font-size: .87rem; }

</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Live clock ──────────────────────────────────────────────────
    function tick() {
        const now  = new Date();
        const h    = String(now.getHours()).padStart(2, '0');
        const m    = String(now.getMinutes()).padStart(2, '0');
        const s    = String(now.getSeconds()).padStart(2, '0');
        const el   = document.getElementById('liveClock');
        if (el) el.textContent = `${h}:${m}:${s}`;
    }
    tick(); setInterval(tick, 1000);

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

    // ── FullCalendar ────────────────────────────────────────────────
    const calendarEl = document.getElementById('dashboardCalendar');
    if (calendarEl) {
        new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 340,
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
            '<div class="text-center text-muted py-4"><i class="fas fa-umbrella-beach fa-2x mb-2 d-block"></i>No classes on weekends — enjoy!</div>';
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
