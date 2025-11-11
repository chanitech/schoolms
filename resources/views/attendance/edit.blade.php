@extends('adminlte::page')

@section('title', 'Edit Attendance')

@section('content_header')
    <h1>Edit Attendance</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('attendance.update', $attendance) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Staff</label>
                <select name="staff_id" class="form-control" required>
                    @foreach($staff as $s)
                        <option value="{{ $s->id }}" {{ $attendance->staff_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="{{ $attendance->date->format('Y-m-d') }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="present" {{ $attendance->status == 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ $attendance->status == 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="leave" {{ $attendance->status == 'leave' ? 'selected' : '' }}>Leave</option>
                </select>
            </div>

            <button class="btn btn-success">Update</button>
            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop
