@extends('adminlte::page')

@section('title', 'New Session Report')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Create New Session Report</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('counseling.session_reports.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Student</label>
                <select name="student_id" class="form-control" required>
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Time</label>
                <input type="time" name="time" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Session Number</label>
                <input type="number" name="session_number" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Presenting Problem(s)</label>
                <textarea name="presenting_problem" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Work Done Today</label>
                <textarea name="work_done" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Assessment / Progress Observed</label>
                <textarea name="assessment_progress" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Intervention Plan</label>
                <textarea name="intervention_plan" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Follow-up / Next Appointment</label>
                <textarea name="follow_up" class="form-control" rows="3"></textarea>
            </div>

            <h5>Biopsychosocial Formulation</h5>
            <div class="form-group">
                <label>Predisposing</label>
                <textarea name="biopsychosocial_predisposing" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>Precipitating</label>
                <textarea name="biopsychosocial_precipitating" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>Perpetuating</label>
                <textarea name="biopsychosocial_perpetuating" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>Protecting</label>
                <textarea name="biopsychosocial_protecting" class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" class="btn btn-success mt-3">Create Session Report</button>
            <a href="{{ route('counseling.session_reports.index') }}" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
</div>
@endsection
