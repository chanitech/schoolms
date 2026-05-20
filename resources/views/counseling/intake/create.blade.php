@extends('adminlte::page')

@section('title', 'New Counseling Intake Form')

@section('content_header')
    <h1>New Counseling Intake Form</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('counseling.intake.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <label>Student</label>
                    <select name="student_id" class="form-control" required>
                        <option value="">-- Select Student --</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">-- Select Gender --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Age</label>
                    <input type="number" name="age" class="form-control">
                </div>
            </div>

            <hr>

            <h5 class="mt-3">Academic & Living Information</h5>
            <div class="row">
                <div class="col-md-4">
                    <label>Stream</label>
                    <input type="text" name="stream" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Education Program</label>
                    <input type="text" name="education_program" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>General Performance</label>
                    <input type="text" name="g_performance" class="form-control">
                </div>
            </div>

            <div class="mt-3">
                <label>Living Situation</label>
                <textarea name="living_situation" class="form-control" rows="2"></textarea>
            </div>

            <hr>

            <h5 class="mt-3">Parent / Guardian Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <label>Father's Name</label>
                    <input type="text" name="father_name" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Father's Occupation</label>
                    <input type="text" name="father_occupation" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Father's Phone</label>
                    <input type="text" name="father_phone" class="form-control">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label>Mother's Name</label>
                    <input type="text" name="mother_name" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Mother's Occupation</label>
                    <input type="text" name="mother_occupation" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Mother's Phone</label>
                    <input type="text" name="mother_phone" class="form-control">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-4">
                    <label>Guardian Name</label>
                    <input type="text" name="guardian_name" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Relationship</label>
                    <input type="text" name="guardian_relationship" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Parents Relationship (Married / Separated / etc)</label>
                    <input type="text" name="parents_relationship" class="form-control">
                </div>
            </div>

            <hr>

            <h5 class="mt-3">Family Information</h5>
            <div class="row">
                <div class="col-md-3">
                    <label>Brothers</label>
                    <input type="number" name="siblings_brothers" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Sisters</label>
                    <input type="number" name="siblings_sisters" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Birth Order</label>
                    <input type="text" name="birth_order" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Referred By</label>
                    <input type="text" name="referred_by" class="form-control">
                </div>
            </div>

            <hr>

            <h5 class="mt-3">Counseling Details</h5>
            <div class="row">
                <div class="col-md-6">
                    <label>Counseling Type</label>
                    <select name="counseling_type[]" class="form-control" multiple>
                        <option value="academic">Academic</option>
                        <option value="career">Career</option>
                        <option value="personal">Personal</option>
                        <option value="social">Social</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Health Problems</label>
                    <input type="text" name="health_problems" class="form-control">
                </div>
            </div>

            <div class="mt-2">
                <label>Previous Counseling Experience</label>
                <textarea name="previous_counseling" class="form-control" rows="2"></textarea>
            </div>

            <div class="mt-2">
                <label>Reason for Counseling</label>
                <textarea name="reason_for_counseling" class="form-control" rows="2"></textarea>
            </div>

            <div class="mt-2">
                <label>Chief Complaint / Problem Description</label>
                <textarea name="chief_complaint" class="form-control" rows="2"></textarea>
            </div>

            <div class="mt-2">
                <label>Student Understanding of Counseling Services</label>
                <textarea name="understanding_of_services" class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" class="btn btn-primary mt-3">
                Submit Intake Form
            </button>
        </form>
    </div>
</div>
@stop
