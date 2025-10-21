@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
@stop

@section('content')

{{-- ✅ Top Summary Cards --}}
<div class="row">
    <div class="col-md-2 col-6">
        <x-adminlte-small-box title="{{ $data['students'] }}" text="Students" icon="fas fa-user-graduate" theme="primary"/>
    </div>
    <div class="col-md-2 col-6">
        <x-adminlte-small-box title="{{ $data['staff'] }}" text="Staff" icon="fas fa-chalkboard-teacher" theme="info"/>
    </div>
    <div class="col-md-2 col-6">
        <x-adminlte-small-box title="{{ $data['classes'] }}" text="Classes" icon="fas fa-school" theme="success"/>
    </div>
    <div class="col-md-2 col-6">
        <x-adminlte-small-box title="{{ $data['departments'] }}" text="Departments" icon="fas fa-building" theme="warning"/>
    </div>
    <div class="col-md-2 col-6">
        <x-adminlte-small-box title="{{ $data['subjects'] }}" text="Subjects" icon="fas fa-book" theme="purple"/>
    </div>
    <div class="col-md-2 col-6">
        <x-adminlte-small-box title="{{ $data['dormitories'] }}" text="Dormitories" icon="fas fa-bed" theme="pink"/>
    </div>
</div>

{{-- ✅ Second Row of Summary Cards --}}
<div class="row">
    
    <div class="col-md-3 col-6">
        <x-adminlte-small-box title="{{ $data['users'] }}" text="Active Users" icon="fas fa-users-cog" theme="dark"/>
    </div>
    <div class="col-md-3 col-6">
        <x-adminlte-small-box title="{{ $data['events'] }}" text="Total Events" icon="fas fa-calendar-alt" theme="primary"/>
    </div>
    <div class="col-md-3 col-6">
        <x-adminlte-small-box title="{{ $data['ongoing_events'] }}" text="Ongoing Today" icon="fas fa-bolt" theme="danger"/>
    </div>
</div>

{{-- ✅ Charts Section --}}
<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Event Type Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="eventChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-calendar"></i> Upcoming Events</h5>
            </div>
            <div class="card-body">
                @forelse ($upcomingEvents as $event)
                    <div class="mb-2 border-bottom pb-2">
                        <strong>{{ $event->title }}</strong><br>
                        <small>
                            {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }} -
                            {{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y') }} |
                            <span class="badge bg-{{ match($event->type) {
                                'academic' => 'primary',
                                'sport' => 'success',
                                'cultural' => 'warning',
                                'holiday' => 'danger',
                                default => 'secondary'
                            } }}">{{ ucfirst($event->type) }}</span>
                        </small>
                    </div>
                @empty
                    <p class="text-muted">No upcoming events.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ✅ Recent Students & Calendar --}}
<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-user-graduate"></i> Recent Student Registrations</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentStudents as $student)
                            <tr>
                                <td>{{ $student->name ?? 'N/A' }}</td>
                                <td>{{ ucfirst($student->gender ?? 'N/A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($student->created_at)->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">No recent registrations.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Event Calendar</h5>
            </div>
            <div class="card-body">
                <div id="dashboardCalendar"></div>
            </div>
        </div>
    </div>
</div>

@stop


@section('footer')
    <footer class="main-footer text-center">
        <strong>&copy; {{ date('Y') }} MEMAsms.</strong> All rights reserved.
    </footer>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">
<style>
    #dashboardCalendar {
        max-width: 100%;
        margin: 0 auto;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === EVENT TYPE CHART ===
    const eventCtx = document.getElementById('eventChart');
    new Chart(eventCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($eventStats->keys()) !!},
            datasets: [{
                data: {!! json_encode($eventStats->values()) !!},
                backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#6c757d']
            }]
        }
    });

    // === FULLCALENDAR ===
    const calendarEl = document.getElementById('dashboardCalendar');
    if (calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 450,
            events: '{{ route('events.fetch') }}',
        });
        calendar.render();
    }
});
</script>
@stop
