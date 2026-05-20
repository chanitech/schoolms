@extends('adminlte::page')

@section('title', 'Edit Counseling Intake Form')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Edit Counseling Intake Form</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Form - {{ $form->student->first_name ?? '' }} {{ $form->student->last_name ?? '' }}</h3>
    </div>

    <div class="card-body">
        <form action="{{ route('counseling.intake.update', $form->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- Student --}}
                <div class="col-md-6 mb-3">
                    <label for="student_id">Student</label>
                    <select name="student_id" id="student_id" class="form-control" required>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" {{ $student->id == $form->student_id ? 'selected' : '' }}>
                                {{ $student->first_name }} {{ $student->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Gender --}}
                <div class="col-md-3 mb-3">
                    <label for="gender">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">-- Select --</option>
                        <option value="Male" {{ $form->gender == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ $form->gender == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                {{-- Age --}}
                <div class="col-md-3 mb-3">
                    <label for="age">Age</label>
                    <input type="number" name="age" value="{{ old('age', $form->age) }}" class="form-control">
                </div>

                {{-- Stream --}}
                <div class="col-md-4 mb-3">
                    <label for="stream">Stream</label>
                    <input type="text" name="stream" value="{{ old('stream', $form->stream) }}" class="form-control">
                </div>

                {{-- Education Program --}}
                <div class="col-md-4 mb-3">
                    <label for="education_program">Education Program</label>
                    <input type="text" name="education_program" value="{{ old('education_program', $form->education_program) }}" class="form-control">
                </div>

                {{-- General Performance --}}
                <div class="col-md-4 mb-3">
                    <label for="g_performance">General Performance</label>
                    <input type="text" name="g_performance" value="{{ old('g_performance', $form->g_performance) }}" class="form-control">
                </div>
            </div>

            <hr>

            <h5>Family Information</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="father_name">Father's Name</label>
                    <input type="text" name="father_name" value="{{ old('father_name', $form->father_name) }}" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="mother_name">Mother's Name</label>
                    <input type="text" name="mother_name" value="{{ old('mother_name', $form->mother_name) }}" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="guardian_name">Guardian Name</label>
                    <input type="text" name="guardian_name" value="{{ old('guardian_name', $form->guardian_name) }}" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="guardian_relationship">Guardian Relationship</label>
                    <input type="text" name="guardian_relationship" value="{{ old('guardian_relationship', $form->guardian_relationship) }}" class="form-control">
                </div>
            </div>

            <hr>

            <h5>Counseling Details</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="reason_for_counseling">Reason for Counseling</label>
                    <textarea name="reason_for_counseling" rows="3" class="form-control">{{ old('reason_for_counseling', $form->reason_for_counseling) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="chief_complaint">Chief Complaint</label>
                    <textarea name="chief_complaint" rows="3" class="form-control">{{ old('chief_complaint', $form->chief_complaint) }}</textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="understanding_of_services">Understanding of Services</label>
                    <textarea name="understanding_of_services" rows="3" class="form-control">{{ old('understanding_of_services', $form->understanding_of_services) }}</textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="counseling_type">Counseling Type</label><br>
                    @php
                        $types = ['Individual', 'Group', 'Career', 'Family', 'Crisis'];
                        $selectedTypes = is_array($form->counseling_type) ? $form->counseling_type : json_decode($form->counseling_type, true);
                    @endphp
                    @foreach($types as $type)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="counseling_type[]" value="{{ $type }}" 
                                   {{ in_array($type, $selectedTypes ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ $type }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-right mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Form
                </button>
                <a href="{{ route('counseling.intake.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </form>
    </div>
</div>
@stop
