@extends('adminlte::page')

@section('title', 'Edit Individual Counseling Report')

@section('content_header')
    <h1><i class="fas fa-user-edit"></i> Edit Individual Counseling Report</h1>
@stop

@php
    $r   = $individualSessionReport;
    $bps = old('biopsychosocial_formulation', $r->biopsychosocial_formulation ?? []);
@endphp

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Update Counseling Session Details</h3>
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

        <form action="{{ route('counseling.individual.update', $r->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="student_id">Student</label>
                <select name="student_id" id="student_id" class="form-control" required>
                    <option value="">-- Select Student --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" @selected(old('student_id', $r->student_id) == $student->id)>
                            {{ $student->first_name }} {{ $student->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ old('date', $r->date?->format('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="time">Time</label>
                        <input type="time" name="time" id="time" class="form-control" value="{{ old('time', $r->time ? substr($r->time, 0, 5) : '') }}" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="session_number">Session Number</label>
                <input type="number" name="session_number" id="session_number" class="form-control" value="{{ old('session_number', $r->session_number) }}">
            </div>

            @foreach([
                'presenting_problem'  => 'Presenting Problem',
                'work_done'           => 'Work Done',
                'assessment_progress' => 'Assessment & Progress',
                'intervention_plan'   => 'Intervention Plan',
                'follow_up'           => 'Follow Up',
            ] as $field => $label)
                <div class="form-group">
                    <label for="{{ $field }}">{{ $label }}</label>
                    <textarea name="{{ $field }}" id="{{ $field }}" class="form-control" rows="3">{{ old($field, $r->$field) }}</textarea>
                </div>
            @endforeach

            <div class="form-group">
                <label>Biopsychosocial Formulation (4P's)</label>
                @if(is_string($bps) && $bps !== '')
                    <div class="alert alert-info py-2 small mb-2">
                        <strong>Earlier note (free text):</strong> {{ $bps }}<br>
                        Copy it into the fields below — saving replaces it with the 4P fields.
                    </div>
                    @php $bps = []; @endphp
                @endif
                <div class="row">
                    @foreach(['predisposing' => 'Predisposing', 'precipitating' => 'Precipitating', 'perpetuating' => 'Perpetuating', 'protective' => 'Protective'] as $key => $label)
                        <div class="col-md-3">
                            <input type="text" name="biopsychosocial_formulation[{{ $key }}]" class="form-control" placeholder="{{ $label }}" value="{{ $bps[$key] ?? '' }}">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update Report
                </button>
                <a href="{{ route('counseling.individual.show', $r->id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@stop
