@extends('adminlte::page')

@section('title', 'Edit Session Report')

@section('content')
<div class="card">
    <div class="card-header"><h3>Edit Session Report</h3></div>
    <div class="card-body">
        <form action="{{ route('counseling.session_reports.update', $report) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group"><label>Student</label>
                <select name="student_id" class="form-control">
                    <option value="">Select student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ $report->student_id == $student->id ? 'selected' : '' }}>
                            {{ $student->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Date</label><input type="date" name="date" value="{{ $report->date }}" class="form-control"></div>
            <div class="form-group"><label>Time</label><input type="time" name="time" value="{{ $report->time }}" class="form-control"></div>
            <div class="form-group"><label>Session Number</label><input type="number" name="session_number" value="{{ $report->session_number }}" class="form-control"></div>
            {{-- Repeat for all other fields (presenting_problem, work_done, etc.) --}}
            <button type="submit" class="btn btn-primary mt-3">Update Report</button>
        </form>
    </div>
</div>
@endsection
