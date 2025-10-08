@extends('adminlte::page')

@section('title', 'Add Staff')

@section('content_header')
    <h1 class="text-success">Add New Staff</h1>
@stop

@section('content')
<div class="container-fluid">
    <form action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
            </div>
            <div class="col-md-6">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
            </div>
            <div class="col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="col-md-6">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="col-md-6">
                <label>Department</label>
                <select name="department_id" class="form-control" required>
                    <option value="">-- Select Department --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label>Position</label>
                <input type="text" name="position" class="form-control" value="{{ old('position') }}">
            </div>
            <div class="col-md-6">
                <label>Role</label>
                <input type="text" name="role" class="form-control" value="{{ old('role') }}" required>
            </div>
            <div class="col-md-6">
                <label>Photo</label>
                <input type="file" name="photo" class="form-control">
            </div>
            <div class="col-md-12 mt-3">
                <button class="btn btn-success">Save Staff</button>
            </div>
        </div>
    </form>
</div>
@stop
