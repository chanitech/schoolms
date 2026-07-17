@extends('adminlte::page')

@section('title', 'Class Attendance Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center no-print">
        <h1 class="m-0 text-dark"><i class="fas fa-file-alt mr-2"></i>Class Attendance Report</h1>
        <div>
            <a href="{{ route('timetables.class-attendance') }}" class="btn btn-outline-secondary btn-sm mr-1">
                <i class="fas fa-clipboard-check"></i> Mark Attendance
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    @if($errors->any())
        <div class="alert alert-danger no-print">{{ $errors->first() }}</div>
    @endif

    {{-- Filters --}}
    <div class="card card-outline card-primary shadow-sm no-print">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <label class="mr-1 small">Class</label>
                <select name="class_id" class="form-control form-control-sm mr-2">
                    <option value="">All my classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ ($class?->id) === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <label class="mr-1 small">Teacher</label>
                <select name="teacher_id" class="form-control form-control-sm mr-2">
                    <option value="">All teachers</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ $teacherId === $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
                <label class="mr-1 small">From</label>
                <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm mr-2">
                <label class="mr-1 small">To</label>
                <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm mr-2">
                <label class="mr-1 small">Status</label>
                <select name="status" class="form-control form-control-sm mr-2">
                    <option value="">All</option>
                    @foreach(['attended' => 'Attended', 'late' => 'Late', 'absent' => 'Absent', 'other' => 'Other'] as $sk => $sl)
                        <option value="{{ $sk }}" {{ $status === $sk ? 'selected' : '' }}>{{ $sl }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Apply</button>
            </form>
        </div>
    </div>

    {{-- Print header --}}
    <div class="d-none print-only mb-3">
        @include('partials.print-letterhead')
        <h4 class="mb-0">Class Attendance Report — {{ $class?->name ?? 'All Classes' }}</h4>
        <p class="text-muted mb-0">
            {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
            @if($teacherId) · Teacher: {{ $teachers->firstWhere('id', $teacherId)?->name }} @endif
            @if($status) · Status: {{ ucfirst($status) }} @endif
            · Printed {{ now()->format('d M Y H:i') }}
        </p>
        <hr>
    </div>

    {{-- Totals --}}
    @php
        $tot = [
            'attended' => $logs->where('status', 'attended')->count(),
            'late'     => $logs->where('status', 'late')->count(),
            'absent'   => $logs->where('status', 'absent')->count(),
            'unmarked' => $logs->whereNull('status')->count(),
        ];
    @endphp
    <div class="row">
        @foreach([['Attended', 'attended', 'success', 'fa-check'], ['Late', 'late', 'warning', 'fa-clock'], ['Absent', 'absent', 'danger', 'fa-times'], ['Not marked', 'unmarked', 'secondary', 'fa-hourglass-half']] as [$label, $key, $color, $icon])
        <div class="col-6 col-md-3">
            <div class="small-box bg-{{ $color }}">
                <div class="inner"><h3>{{ $tot[$key] }}</h3><p>{{ $label }}</p></div>
                <i class="icon fas {{ $icon }}"></i>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Per-teacher summary --}}
    <div class="card card-outline card-info shadow-sm">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-user-tie mr-2"></i>Summary by Teacher</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Teacher</th>
                        <th class="text-center">Sessions</th>
                        <th class="text-center">Attended</th>
                        <th class="text-center">Late</th>
                        <th class="text-center">Absent</th>
                        <th class="text-center">Other</th>
                        <th class="text-center">Not marked</th>
                        <th class="text-center">Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teacherSummary as $row)
                    @php $marked = $row['total'] - $row['unmarked']; @endphp
                    <tr>
                        <td>{{ $row['teacher']?->name ?? '—' }}</td>
                        <td class="text-center">{{ $row['total'] }}</td>
                        <td class="text-center text-success font-weight-bold">{{ $row['attended'] }}</td>
                        <td class="text-center text-warning font-weight-bold">{{ $row['late'] }}</td>
                        <td class="text-center text-danger font-weight-bold">{{ $row['absent'] }}</td>
                        <td class="text-center">{{ $row['other'] }}</td>
                        <td class="text-center text-muted">{{ $row['unmarked'] }}</td>
                        <td class="text-center">{{ $marked ? round(($row['attended'] + $row['late']) / $marked * 100) . '%' : '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-3">No attendance records match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detailed log --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-2"></i>Detailed Sessions ({{ $logs->count() }})</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Period</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Status</th>
                        <th>Comment</th>
                        <th class="no-print">Marked by</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    @php
                        $badge = ['attended' => 'success', 'late' => 'warning', 'absent' => 'danger', 'other' => 'secondary'][$log->status] ?? 'light';
                    @endphp
                    <tr>
                        <td>{{ $log->session_date->format('D, d M Y') }}</td>
                        <td>{{ $log->period?->name ?? '—' }}</td>
                        <td>{{ $log->schoolClass?->name ?? '—' }}</td>
                        <td>{{ $log->subject?->name ?? '—' }}</td>
                        <td>{{ $log->teacher?->name ?? '—' }}</td>
                        <td><span class="badge badge-{{ $badge }}">{{ $log->status ? ucfirst($log->status) : 'Not marked' }}</span></td>
                        <td class="small">{{ $log->notes ?? '—' }}</td>
                        <td class="small text-muted no-print">{{ $log->recorder?->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-3">No attendance records match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Send to HOD / Head Master --}}
    <div class="card card-outline card-success shadow-sm no-print">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-paper-plane mr-2"></i>Send This Report</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('timetables.class-attendance.report.send') }}" class="form-inline">
                @csrf
                {{-- carry current filters --}}
                @foreach(['class_id' => $class?->id, 'from' => $from->toDateString(), 'to' => $to->toDateString(), 'teacher_id' => $teacherId, 'status' => $status] as $k => $v)
                    @if($v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
                @endforeach
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" class="custom-control-input" id="rcpt-hod" name="recipients[]" value="hod" checked>
                    <label class="custom-control-label" for="rcpt-hod">HODs</label>
                </div>
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" class="custom-control-input" id="rcpt-do" name="recipients[]" value="do" checked>
                    <label class="custom-control-label" for="rcpt-do">Head Master (DO)</label>
                </div>
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-paper-plane mr-1"></i> Send Report
                </button>
                <span class="text-muted small ml-3">Recipients get a notification linking to this exact filtered report.</span>
            </form>
        </div>
    </div>

    @include('partials.print-powered-by')
</div>
@stop

@section('css')
<style>
    @media print {
        .no-print, .main-sidebar, .main-header, .main-footer, .content-header .no-print { display: none !important; }
        .print-only { display: block !important; }
        .content-wrapper { margin-left: 0 !important; background: #fff !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        .small-box .icon { display: none; }
    }
</style>
@stop
