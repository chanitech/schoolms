@extends('adminlte::page')

@section('title', 'Add Student')

@section('content')
<div class="card">
    <div class="card-header">Add Student</div>
    <div class="card-body">
        <form action="{{ route('students.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Admission No</label>
                <input type="text" name="admission_no" class="form-control" value="{{ old('admission_no') }}">
            </div>
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}">
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender" class="form-control">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="form-group">
                <label>Guardian</label>
                <select name="guardian_id" class="form-control">
                    <option value="">--Select Guardian--</option>
                    @foreach($guardians as $guardian)
                        <option value="{{ $guardian->id }}">{{ $guardian->first_name }} {{ $guardian->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Class</label>
                <select name="class_id" class="form-control">
                    <option value="">--Select Class--</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Dormitory</label>
                <select name="dormitory_id" class="form-control">
                    <option value="">--Select Dormitory--</option>
                    @foreach($dormitories as $dorm)
                        <option value="{{ $dorm->id }}">{{ $dorm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Academic Session</label>
                <select name="academic_session_id" class="form-control">
                    <option value="">--Select Session--</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success">Save</button>
        </form>
    </div>
</div>
@stop
