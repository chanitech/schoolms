@extends('adminlte::page')

@section('title', 'Class Attendance')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-clipboard-check mr-2"></i>Class Attendance — {{ $class->name }}</h1>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <form method="GET" class="form-inline">
                <label class="mr-2">Class</label>
                <select name="class_id" class="form-control form-control-sm mr-3" onchange="this.form.submit()">
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $c->id === $class->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <label class="mr-2">Date</label>
                <input type="date" name="date" class="form-control form-control-sm mr-3"
                       value="{{ $date->toDateString() }}" onchange="this.form.submit()">
                <span class="text-muted small">{{ $date->format('l, d M Y') }}</span>
            </form>
        </div>
        <div class="card-body table-responsive">
            @if($date->isWeekend())
                <p class="text-center text-muted my-4">No classes on weekends.</p>
            @elseif($entries->isEmpty())
                <p class="text-center text-muted my-4">No published timetable sessions for this class on this day.</p>
            @else
            <table class="table table-bordered table-hover">
                <thead class="bg-light">
                    <tr>
                        <th style="width:14%">Period</th>
                        <th style="width:18%">Subject</th>
                        <th style="width:20%">Teacher</th>
                        <th style="width:30%" class="text-center">Attendance</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusCfg = [
                            'attended' => ['label' => 'Attended', 'btn' => 'btn-outline-success', 'active' => 'btn-success',   'icon' => 'fa-check'],
                            'late'     => ['label' => 'Late',     'btn' => 'btn-outline-warning', 'active' => 'btn-warning',   'icon' => 'fa-clock'],
                            'absent'   => ['label' => 'Absent',   'btn' => 'btn-outline-danger',  'active' => 'btn-danger',    'icon' => 'fa-times'],
                            'other'    => ['label' => 'Other',    'btn' => 'btn-outline-secondary','active' => 'btn-secondary','icon' => 'fa-ellipsis-h'],
                        ];
                    @endphp
                    @foreach($entries as $entry)
                    @php $log = $logs->get($entry->id); @endphp
                    <tr>
                        <td>
                            <strong>{{ $entry->period?->name }}</strong>
                            <div class="small text-muted">
                                {{ $entry->start_time ?? $entry->period?->start_time }} – {{ $entry->end_time ?? $entry->period?->end_time }}
                            </div>
                        </td>
                        <td>{{ $entry->subject?->name ?? '—' }}</td>
                        <td>{{ $entry->teacher?->name ?? '—' }}</td>
                        <td class="text-center align-middle">
                            <form method="POST" action="{{ route('timetables.log-session', $entry) }}" class="d-inline attendance-form" id="att-{{ $entry->id }}">
                                @csrf
                                <input type="hidden" name="session_date" value="{{ $date->toDateString() }}">
                                <input type="hidden" name="from" value="class-attendance">
                                <input type="hidden" name="notes" value="{{ $log?->notes }}" class="notes-mirror">
                                <div class="btn-group btn-group-sm" role="group">
                                    @foreach($statusCfg as $sk => $sc)
                                        <button type="submit" name="status" value="{{ $sk }}"
                                                class="btn {{ $log?->status === $sk ? $sc['active'] : $sc['btn'] }}">
                                            <i class="fas {{ $sc['icon'] }} mr-1"></i>{{ $sc['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </form>
                            @if(!$log || !$log->status)
                                <div class="small text-muted mt-1">Not marked yet</div>
                            @endif
                        </td>
                        <td class="align-middle">
                            <input type="text" class="form-control form-control-sm note-input" maxlength="500"
                                   form="att-{{ $entry->id }}" name="notes_visible" data-entry="{{ $entry->id }}"
                                   placeholder="e.g. Arrived 15 minutes late, class covered by substitute…"
                                   value="{{ $log?->notes }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle mr-1"></i>
                Click a status to save it for that period. Write a comment first if needed — it is saved together with the status.
            </p>
            @endif
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    // Keep the hidden notes field in sync with the visible comment box so
    // the comment submits together with whichever status button is clicked.
    document.querySelectorAll('.note-input').forEach(function (input) {
        input.addEventListener('input', function () {
            const form = document.getElementById('att-' + this.dataset.entry);
            form.querySelector('.notes-mirror').value = this.value;
        });
    });
</script>
@stop
