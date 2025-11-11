@extends('adminlte::page')

@section('title', 'Edit Staff')

@section('content_header')
    <h1 class="text-center text-success">Edit Staff Member</h1>
@stop

@section('content')
<div class="container-fluid">
    <form action="{{ route('staff.update', $staff->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">

            <!-- First Name -->
            <div class="col-md-6">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" 
                    value="{{ old('first_name', $staff->first_name) }}" required>
            </div>

            <!-- Last Name -->
            <div class="col-md-6">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" 
                    value="{{ old('last_name', $staff->last_name) }}" required>
            </div>

            <!-- Email -->
            <div class="col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control" 
                    value="{{ old('email', $staff->email) }}" required>
            </div>

            <!-- Phone -->
            <div class="col-md-6">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" 
                    value="{{ old('phone', $staff->phone) }}">
            </div>

            <!-- Department -->
            <div class="col-md-6">
                <label>Department</label>
                <select name="department_id" class="form-control" required>
                    <option value="">-- Select Department --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" 
                            {{ old('department_id', $staff->department_id) == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Position -->
            <div class="col-md-6">
                <label>Position</label>
                <input type="text" name="position" class="form-control" 
                    value="{{ old('position', $staff->position) }}">
            </div>

            <!-- Roles (Multiple) -->
            <div class="col-md-12">
                <label>Roles</label>
                <div class="row">
                    @foreach($roles as $role)
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                    class="form-check-input" id="role-{{ $role->id }}"
                                    {{ in_array($role->name, old('roles', $staff->roles->pluck('name')->toArray())) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Photo -->
            <div class="col-md-6">
                <label>Photo</label>
                <input type="file" name="photo" class="form-control">
                @if($staff->photo)
                    <img src="{{ asset('storage/' . $staff->photo) }}" class="img-thumbnail mt-2" width="100">
                @endif
            </div>

            <!-- Buttons -->
            <div class="col-md-12 mt-3">
                <button class="btn btn-primary">Update Staff</button>
                <a href="{{ route('staff.index') }}" class="btn btn-secondary">Cancel</a>
            </div>

        </div>
    </form>
</div>
@stop
