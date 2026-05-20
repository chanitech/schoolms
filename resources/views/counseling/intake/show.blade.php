@extends('adminlte::page')

@section('title', 'Counseling Intake Form Details')

@section('content_header')
    <h1><i class="fas fa-user-edit"></i> Counseling Intake Form Details</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <strong>Student Information</strong>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-4"><strong>Name:</strong> {{ $form->student->first_name }} {{ $form->student->last_name }}</div>
            <div class="col-md-2"><strong>Gender:</strong> {{ $form->gender ?? '-' }}</div>
            <div class="col-md-2"><strong>Age:</strong> {{ $form->age ?? '-' }}</div>
            <div class="col-md-4"><strong>Stream:</strong> {{ $form->stream ?? '-' }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4"><strong>Education Program:</strong> {{ $form->education_program ?? '-' }}</div>
            <div class="col-md-4"><strong>General Performance:</strong> {{ $form->g_performance ?? '-' }}</div>
            <div class="col-md-4"><strong>Living Situation:</strong> {{ $form->living_situation ?? '-' }}</div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-secondary text-white">
        <strong>Parent / Guardian Information</strong>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-4"><strong>Father:</strong> {{ $form->father_name ?? '-' }}</div>
            <div class="col-md-4"><strong>Occupation:</strong> {{ $form->father_occupation ?? '-' }}</div>
            <div class="col-md-4"><strong>Phone:</strong> {{ $form->father_phone ?? '-' }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4"><strong>Mother:</strong> {{ $form->mother_name ?? '-' }}</div>
            <div class="col-md-4"><strong>Occupation:</strong> {{ $form->mother_occupation ?? '-' }}</div>
            <div class="col-md-4"><strong>Phone:</strong> {{ $form->mother_phone ?? '-' }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4"><strong>Guardian:</strong> {{ $form->guardian_name ?? '-' }}</div>
            <div class="col-md-4"><strong>Relationship:</strong> {{ $form->guardian_relationship ?? '-' }}</div>
            <div class="col-md-4"><strong>Parents Relationship:</strong> {{ $form->parents_relationship ?? '-' }}</div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-info text-white">
        <strong>Family Background</strong>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-3"><strong>Brothers:</strong> {{ $form->siblings_brothers ?? '-' }}</div>
            <div class="col-md-3"><strong>Sisters:</strong> {{ $form->siblings_sisters ?? '-' }}</div>
            <div class="col-md-3"><strong>Birth Order:</strong> {{ $form->birth_order ?? '-' }}</div>
            <div class="col-md-3"><strong>Referred By:</strong> {{ $form->referred_by ?? '-' }}</div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-success text-white">
        <strong>Counseling Information</strong>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-6">
                <strong>Counseling Type:</strong>
                @php
                    $types = is_array($form->counseling_type) ? $form->counseling_type : json_decode($form->counseling_type, true) ?? [];
                @endphp
                {{ !empty($types) ? implode(', ', $types) : '-' }}
            </div>
            <div class="col-md-6"><strong>Health Problems:</strong> {{ $form->health_problems ?? '-' }}</div>
        </div>

        <div class="mb-2"><strong>Previous Counseling:</strong><br>{{ $form->previous_counseling ?? '-' }}</div>
        <div class="mb-2"><strong>Reason for Counseling:</strong><br>{{ $form->reason_for_counseling ?? '-' }}</div>
        <div class="mb-2"><strong>Chief Complaint:</strong><br>{{ $form->chief_complaint ?? '-' }}</div>
        <div><strong>Understanding of Services:</strong><br>{{ $form->understanding_of_services ?? '-' }}</div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('counseling.intake.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>
@stop
