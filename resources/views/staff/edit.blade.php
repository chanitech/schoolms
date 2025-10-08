@extends('adminlte::page')
@section('title', 'Edit Staff')

@section('content_header')
<h1 class="text-center text-success">Edit Staff Member</h1>
@stop

@section('content')
<div class="container-fluid">
    <form action="{{ route('staff.update', $staff->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $staff->first_name) }}" required>
            </div>

            <div class="col-md-6">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $staff->last_name) }}" required>
            </div>

            <div class="col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $staff->email) }}" required>
            </div>

            <div class="col-md-6">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $staff->phone) }}">
            </div>

            <div class="col-md-6">
                <label>Department</label>
                <select name="department_id" class="form-control">
                    <option value="">-- Select Department --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $staff->department_id == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label>Position</label>
                <input type="text" name="position" class="form-control" value="{{ old('position', $staff->position) }}">
            </div>

            <div class="col-md-6">
                <label>Role</label>
                <input type="text" name="role" class="form-control" value="{{ old('role', $staff->role) }}">
            </div>
        </div>

        <button class="btn btn-primary mt-3">Update Staff</button>
        <a href="{{ route('staff.index') }}" class="btn btn-secondary mt-3">Cancel</a>
    </form>
</div>
@stop
