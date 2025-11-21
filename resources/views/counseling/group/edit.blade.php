@extends('adminlte::page')

@section('title', 'Edit Group Counseling Session')

@section('content_header')
    <h1><i class="fas fa-users"></i> Edit Group Counseling Session</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Editing: {{ $groupCounselingSessionReport->group_name }}</h3>
    </div>

    <div class="card-body">
        <form action="{{ route('counseling.group.update', $groupCounselingSessionReport->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Group Name --}}
            <div class="form-group">
                <label for="group_name">Group Name</label>
                <input type="text" name="group_name" id="group_name" class="form-control @error('group_name') is-invalid @enderror" value="{{ old('group_name', $groupCounselingSessionReport->group_name) }}">
                @error('group_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Date & Time --}}
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="date">Date</label>
                    <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $groupCounselingSessionReport->date) }}">
                    @error('date')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 form-group">
                    <label for="time">Time</label>
                    <input type="time" name="time" id="time" class="form-control @error('time') is-invalid @enderror" value="{{ old('time', $groupCounselingSessionReport->time) }}">
                    @error('time')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Session Number --}}
            <div class="form-group">
                <label for="session_number">Session Number</label>
                <input type="number" name="session_number" id="session_number" class="form-control @error('session_number') is-invalid @enderror" value="{{ old('session_number', $groupCounselingSessionReport->session_number) }}">
                @error('session_number')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Presenting Problem --}}
            <div class="form-group">
                <label for="presenting_problem">Presenting Problem</label>
                <textarea name="presenting_problem" id="presenting_problem" class="form-control @error('presenting_problem') is-invalid @enderror">{{ old('presenting_problem', $groupCounselingSessionReport->presenting_problem) }}</textarea>
                @error('presenting_problem')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Work Done --}}
            <div class="form-group">
                <label for="work_done">Work Done</label>
                <textarea name="work_done" id="work_done" class="form-control @error('work_done') is-invalid @enderror">{{ old('work_done', $groupCounselingSessionReport->work_done) }}</textarea>
                @error('work_done')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Assessment / Progress --}}
            <div class="form-group">
                <label for="assessment_progress">Assessment / Progress</label>
                <textarea name="assessment_progress" id="assessment_progress" class="form-control @error('assessment_progress') is-invalid @enderror">{{ old('assessment_progress', $groupCounselingSessionReport->assessment_progress) }}</textarea>
                @error('assessment_progress')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Intervention Plan --}}
            <div class="form-group">
                <label for="intervention_plan">Intervention Plan</label>
                <textarea name="intervention_plan" id="intervention_plan" class="form-control @error('intervention_plan') is-invalid @enderror">{{ old('intervention_plan', $groupCounselingSessionReport->intervention_plan) }}</textarea>
                @error('intervention_plan')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Follow Up --}}
            <div class="form-group">
                <label for="follow_up">Follow Up</label>
                <textarea name="follow_up" id="follow_up" class="form-control @error('follow_up') is-invalid @enderror">{{ old('follow_up', $groupCounselingSessionReport->follow_up) }}</textarea>
                @error('follow_up')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Members / Students --}}
            <div class="form-group">
                <label for="students">Members / Students</label>
                <select name="students[]" id="students" class="form-control select2" multiple>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" 
                            {{ in_array($student->id, old('students', $selectedStudents)) ? 'selected' : '' }}>
                            {{ $student->first_name }} {{ $student->last_name }} ({{ $student->admission_no ?? '-' }})
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Hold Ctrl (Windows) / Cmd (Mac) to select multiple students.</small>
            </div>

            {{-- Biopsychosocial Formulation --}}
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    Biopsychosocial Formulation (4P's)
                </div>
                <div class="card-body">
                    @php
                        $pList = ['Predisposing', 'Precipitating', 'Perpetuating', 'Protecting'];
                        $factors = ['biological', 'psychological', 'social'];
                        $formulation = old('biopsychosocial_formulation', $groupCounselingSessionReport->biopsychosocial_formulation ?? []);
                    @endphp

                    @foreach($pList as $p)
                        <h5>{{ $p }}</h5>
                        <div class="row mb-2">
                            @foreach($factors as $factor)
                                <div class="col-md-4">
                                    <label>{{ ucfirst($factor) }}</label>
                                    <input type="text" name="biopsychosocial_formulation[{{ $p }}][{{ $factor }}]" 
                                        class="form-control" 
                                        value="{{ $formulation[$p][$factor] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update Report
                </button>
                <a href="{{ route('counseling.group.show', $groupCounselingSessionReport->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select Students',
            width: '100%'
        });
    });
</script>
@stop
