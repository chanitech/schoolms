@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</h1>
        <div>
            <span class="badge badge-primary">{{ now()->format('l, d M Y') }}</span>
        </div>
    </div>
@stop

@section('content')

{{-- ================== TOP SUMMARY CARDS ================== --}}
<div class="row">
    <div class="col-md-2 col-6">
        <x-adminlte-small-box title="{{ $data['students'] ?? 0 }}" text="Students" icon="fas fa-user-graduate" theme="primary"/>
    </div>
    <div class="col-md-2 col-6">
        <x-adminlte-small-box title="{{ $data['staff'] ?? 0 }}" text="Staff" icon="fas fa-chalkboard-teacher" theme="info"/>
    </div>
    <div class="col-md-2 col-6">
        <x-adminlte-small-box title="{{ $data['classes'] ?? 0 }}" text="Classes" icon="fas fa-school" theme="success"/>
    </div>
    <div class="col-md-2 col-6">
        <x-adminlte-small-box title="{{ $data['departments'] ?? 0 }}" text="Departments" icon="fas fa-building" theme="warning"/>
    </div>
    <div class="col-md-2 col-6">
        <x-adminlte-small-box title="{{ $data['subjects'] ?? 0 }}" text="Subjects" icon="fas fa-book" theme="purple"/>
    </div>
    <div class="col-md-2 col-6">
        <x-adminlte-small-box title="{{ $data['dormitories'] ?? 0 }}" text="Dormitories" icon="fas fa-bed" theme="pink"/>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-3 col-6">
        <x-adminlte-small-box title="{{ $data['users'] ?? 0 }}" text="Active Users" icon="fas fa-users-cog" theme="dark"/>
    </div>
    <div class="col-md-3 col-6">
        <x-adminlte-small-box title="{{ $data['events'] ?? 0 }}" text="Total Events" icon="fas fa-calendar-alt" theme="primary"/>
    </div>
    <div class="col-md-3 col-6">
        <x-adminlte-small-box title="{{ $data['ongoing_events'] ?? 0 }}" text="Ongoing Today" icon="fas fa-bolt" theme="danger"/>
    </div>
    {{-- Pending Approvals card removed --}}
    <div class="col-md-3 col-6">
        {{-- Optional: you can add another card here or leave empty --}}
    </div>
</div>

{{-- ================== LIBRARY QUICK STATS (replacing revenue chart) ================== --}}
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-book"></i> Library Statistics</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="info-box bg-gradient-primary">
                            <span class="info-box-icon"><i class="fas fa-book"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Books</span>
                                <span class="info-box-number">{{ $libraryStats['books'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-gradient-warning">
                            <span class="info-box-icon"><i class="fas fa-hand-holding-heart"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Active Borrowings</span>
                                <span class="info-box-number">{{ $libraryStats['active_borrowings'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-gradient-success">
                            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Issued This Month</span>
                                <span class="info-box-number">{{ $libraryStats['issued_this_month'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="progress-group">
                        <span class="progress-text">Books Issued This Month</span>
                        <span class="float-right"><b>{{ $libraryStats['issued_this_month'] ?? 0 }}</b>/{{ $libraryStats['books'] ?? 1 }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width: {{ ($libraryStats['issued_this_month'] ?? 0) / max($libraryStats['books'] ?? 1, 1) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================== CHARTS & UPCOMING EVENTS ================== --}}
<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Event Type Distribution</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="eventChart" style="height: 250px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar"></i> Upcoming Events</h3>
                <div class="card-tools">
                    <a href="{{ route('events.calendar') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse ($upcomingEvents ?? [] as $event)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $event->title }}</strong><br>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }}
                                    @if($event->end_date) – {{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y') }} @endif
                                </small>
                            </div>
                            <span class="badge bg-{{ match($event->type) {
                                'academic' => 'primary',
                                'sport' => 'success',
                                'cultural' => 'warning',
                                'holiday' => 'danger',
                                default => 'secondary'
                            } }}">{{ ucfirst($event->type) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">No upcoming events.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- ================== RECENT STUDENTS & CALENDAR ================== --}}
<div class="row">
    <div class="col-md-5">
        <div class="card card-outline card-dark">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-graduate"></i> Recent Student Registrations</h3>
                <div class="card-tools">
                    <a href="{{ route('students.index') }}" class="btn btn-sm btn-secondary">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentStudents ?? [] as $student)
                            <tr>
                                <td>{{ $student->name ?? $student->first_name . ' ' . $student->last_name }}</td>
                                <td>{{ ucfirst($student->gender ?? 'N/A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($student->created_at)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">No recent registrations.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Event Calendar</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div id="dashboardCalendar"></div>
            </div>
        </div>
    </div>
</div>

{{-- ================== QUICK ACTION BUTTONS ================== --}}
<div class="row mt-3">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2 col-6 mb-2">
                        <a href="{{ route('students.create') }}" class="btn btn-app">
                            <i class="fas fa-user-plus"></i> Add Student
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <a href="{{ route('marks.create') }}" class="btn btn-app">
                            <i class="fas fa-pen-alt"></i> Enter Marks
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <a href="{{ route('results.class') }}" class="btn btn-app">
                            <i class="fas fa-chart-line"></i> Class Results
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <a href="{{ route('finance.payments.index') }}" class="btn btn-app">
                            <i class="fas fa-credit-card"></i> Record Payment
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <a href="{{ route('library.lendings.create') }}" class="btn btn-app">
                            <i class="fas fa-book"></i> Lend Book
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <a href="{{ route('events.create') }}" class="btn btn-app">
                            <i class="fas fa-calendar-plus"></i> Add Event
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">
<style>
    .btn-app {
        background-color: #f4f4f4;
        border-radius: 10px;
        color: #333;
        display: inline-block;
        font-size: 12px;
        margin: 0 5px 10px;
        padding: 15px 0;
        text-align: center;
        width: 100px;
        transition: all 0.2s;
    }
    .btn-app:hover {
        background-color: #e9ecef;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .btn-app i {
        display: block;
        font-size: 24px;
        margin-bottom: 8px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Event Type Chart (Doughnut)
    const eventCtx = document.getElementById('eventChart').getContext('2d');
    new Chart(eventCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($eventStats->keys() ?? []) !!},
            datasets: [{
                data: {!! json_encode($eventStats->values() ?? []) !!},
                backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#6c757d','#17a2b8'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // FullCalendar
    const calendarEl = document.getElementById('dashboardCalendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 350,
        headerToolbar: { left: 'prev,next', center: 'title', right: '' },
        events: @json($calendarEvents ?? [])
    });
    calendar.render();
});
</script>
@stop