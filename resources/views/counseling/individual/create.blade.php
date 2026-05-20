@extends('adminlte::page')

@section('title', 'New Individual Counseling Report')

@section('content_header')
    <h1><i class="fas fa-user-edit"></i> New Individual Counseling Report</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Fill Counseling Session Details</h3>
    </div>

    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('counseling.individual.store') }}" method="POST">
            @csrf

            {{-- Student Selection --}}
            <div class="form-group">
                <label for="student_id">Student</label>
                <select name="student_id" id="student_id" class="form-control" required>
                    <option value="">-- Select Student --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date & Time --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ old('date') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="time">Time</label>
                        <input type="time" name="time" id="time" class="form-control" value="{{ old('time') }}" required>
                    </div>
                </div>
            </div>

            {{-- Session Number --}}
            <div class="form-group">
                <label for="session_number">Session Number</label>
                <input type="number" name="session_number" id="session_number" class="form-control" value="{{ old('session_number') }}">
            </div>

            {{-- Presenting Problem --}}
            <div class="form-group">
                <label for="presenting_problem">Presenting Problem</label>
                <textarea name="presenting_problem" id="presenting_problem" class="form-control" rows="3">{{ old('presenting_problem') }}</textarea>
            </div>

            {{-- Work Done --}}
            <div class="form-group">
                <label for="work_done">Work Done</label>
                <textarea name="work_done" id="work_done" class="form-control" rows="3">{{ old('work_done') }}</textarea>
            </div>

            {{-- Assessment & Progress --}}
            <div class="form-group">
                <label for="assessment_progress">Assessment & Progress</label>
                <textarea name="assessment_progress" id="assessment_progress" class="form-control" rows="3">{{ old('assessment_progress') }}</textarea>
            </div>

            {{-- Intervention Plan --}}
            <div class="form-group">
                <label for="intervention_plan">Intervention Plan</label>
                <textarea name="intervention_plan" id="intervention_plan" class="form-control" rows="3">{{ old('intervention_plan') }}</textarea>
            </div>

            {{-- Follow Up --}}
            <div class="form-group">
                <label for="follow_up">Follow Up</label>
                <textarea name="follow_up" id="follow_up" class="form-control" rows="3">{{ old('follow_up') }}</textarea>
            </div>

            {{-- Biopsychosocial Formulation (4P's) --}}
            <div class="form-group">
                <label>Biopsychosocial Formulation (4P's)</label>
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="biopsychosocial_formulation[predisposing]" class="form-control" placeholder="Predisposing" value="{{ old('biopsychosocial_formulation.prediposing') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="biopsychosocial_formulation[precipitating]" class="form-control" placeholder="Precipitating" value="{{ old('biopsychosocial_formulation.precipitating') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="biopsychosocial_formulation[perpetuating]" class="form-control" placeholder="Perpetuating" value="{{ old('biopsychosocial_formulation.perpetuating') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="biopsychosocial_formulation[protective]" class="form-control" placeholder="Protective" value="{{ old('biopsychosocial_formulation.protective') }}">
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="form-group mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Save Report
                </button>
                <a href="{{ route('counseling.individual.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@stop
