@extends('adminlte::page')

@section('title', 'Mark Attendance')

@section('content_header')
    <h1>Mark Attendance</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('attendance.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Staff</label>
                <select name="staff_id" class="form-control" required>
                    <option value="">Select Staff</option>
                    @foreach($staff as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="leave">Leave</option>
                </select>
            </div>

            <button class="btn btn-success">Submit</button>
            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop
