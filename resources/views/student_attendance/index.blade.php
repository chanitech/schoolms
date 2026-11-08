@extends('adminlte::page')

@section('title', 'Student Attendance')

@section('content_header')
    <h1>Student Attendance</h1>
@stop

@section('content')
    @include('partials.flash')
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('student-attendance.index') }}" class="row">
                <div class="col-md-5 form-group">
                    <label for="class_id">Class</label>
                    <select name="class_id" id="class_id" class="form-control" required>
                        <option value="">Select class</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" @selected($class?->id === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label for="date">Date</label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ $date }}" max="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-3 form-group d-flex align-items-end">
                    <button class="btn btn-primary btn-block">Open Register</button>
                </div>
            </form>
        </div>
    </div>

    @if(!$session)
        <div class="alert alert-warning">No academic session is marked as current. Set one under School Setup first.</div>
    @elseif($class && $students->isEmpty())
        <div class="alert alert-warning">No active students are enrolled in {{ $class->name }} for {{ $session->name }}.</div>
    @elseif($class)
        <form method="POST" action="{{ route('student-attendance.store') }}">
            @csrf
            <input type="hidden" name="class_id" value="{{ $class->id }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $class->name }} · {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</h3>
                    <div class="card-tools">
                        <span class="badge {{ $saved->isNotEmpty() ? 'badge-success' : 'badge-secondary' }}">{{ $saved->isNotEmpty() ? 'Already saved' : 'Not yet saved' }}</span>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Adm. No</th>
                                @foreach($statuses as $label)
                                    <th class="text-center">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ trim($student->first_name . ' ' . $student->last_name) }}</td>
                                    <td>{{ $student->admission_no }}</td>
                                    @foreach($statuses as $value => $label)
                                        <td class="text-center">
                                            <input type="radio" name="attendance[{{ $student->id }}]" value="{{ $value }}" aria-label="{{ $label }}"
                                                   @checked(($saved[$student->id] ?? 'present') === $value)>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success">Save Attendance</button>
                    <span class="text-muted small ml-2">Everyone starts as Present, so only change those who are not.</span>
                </div>
            </div>
        </form>
    @endif
@stop
