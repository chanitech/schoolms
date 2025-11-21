@extends('adminlte::page')

@section('title', 'New Group Session Report')

@section('content_header')
    <h1>Create Group Counseling Session Report</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- Display validation errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('counseling.group.store') }}" method="POST">
            @csrf

            {{-- Group Name --}}
            <div class="form-group">
                <label for="group_name">Group Name</label>
                <input type="text" name="group_name" class="form-control" id="group_name" value="{{ old('group_name') }}" required>
            </div>

            {{-- Members --}}
            <div class="form-group">
                <label for="students">Members</label>
                <select name="students[]" id="students" class="form-control" multiple>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ (collect(old('students'))->contains($student->id)) ? 'selected':'' }}>
                            {{ $student->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Hold Ctrl/Cmd to select multiple students</small>
            </div>

            {{-- Date & Time --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" class="form-control" id="date" value="{{ old('date') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="time">Time</label>
                        <input type="time" name="time" class="form-control" id="time" value="{{ old('time') }}" required>
                    </div>
                </div>
            </div>

            {{-- Session Number --}}
            <div class="form-group">
                <label for="session_number">Session Number</label>
                <input type="number" name="session_number" class="form-control" id="session_number" value="{{ old('session_number') }}">
            </div>

            {{-- Text Areas --}}
            <div class="form-group">
                <label for="presenting_problem">Presenting Problem(s)</label>
                <textarea name="presenting_problem" class="form-control" rows="3">{{ old('presenting_problem') }}</textarea>
            </div>

            <div class="form-group">
                <label for="work_done">Work Done Today</label>
                <textarea name="work_done" class="form-control" rows="3">{{ old('work_done') }}</textarea>
            </div>

            <div class="form-group">
                <label for="assessment_progress">Assessment / Progress Observed</label>
                <textarea name="assessment_progress" class="form-control" rows="3">{{ old('assessment_progress') }}</textarea>
            </div>

            <div class="form-group">
                <label for="intervention_plan">Intervention Plan</label>
                <textarea name="intervention_plan" class="form-control" rows="3">{{ old('intervention_plan') }}</textarea>
            </div>

            <div class="form-group">
                <label for="follow_up">Follow-up / Next Appointment</label>
                <textarea name="follow_up" class="form-control" rows="3">{{ old('follow_up') }}</textarea>
            </div>

            {{-- Biopsychosocial Formulation (4 P's) --}}
            <h4>Biopsychosocial Formulation (4 P's)</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>4 P's</th>
                        <th>Biological</th>
                        <th>Psychological</th>
                        <th>Social</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(['Predisposing','Precipitating','Perpetuating','Protecting'] as $p)
                        <tr>
                            <td>{{ $p }}</td>
                            <td>
                                <input type="text" name="biopsychosocial_formulation[{{ $p }}][biological]" class="form-control"
                                       value="{{ old("biopsychosocial_formulation.$p.biological") }}">
                            </td>
                            <td>
                                <input type="text" name="biopsychosocial_formulation[{{ $p }}][psychological]" class="form-control"
                                       value="{{ old("biopsychosocial_formulation.$p.psychological") }}">
                            </td>
                            <td>
                                <input type="text" name="biopsychosocial_formulation[{{ $p }}][social]" class="form-control"
                                       value="{{ old("biopsychosocial_formulation.$p.social") }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Buttons --}}
            <button type="submit" class="btn btn-primary">Save Report</button>
            <a href="{{ route('counseling.group.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop
