@extends('adminlte::page')

@section('title', 'Edit Staff')

@section('content_header')
    <h1 class="text-center text-success">Edit Staff Member</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Staff Information</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('staff.update', $staff->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">

                    <!-- First Name -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name', $staff->first_name) }}" required>
                            @error('first_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Last Name -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name', $staff->last_name) }}" required>
                            @error('last_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $staff->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $staff->phone) }}">
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Department -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ old('department_id', $staff->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Position -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" name="position" class="form-control @error('position') is-invalid @enderror"
                                   value="{{ old('position', $staff->position) }}">
                            @error('position')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Basic Salary -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Basic Salary (TZS) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">TZS</span>
                                </div>
                                <input type="number" step="0.01" name="basic_salary"
                                       class="form-control @error('basic_salary') is-invalid @enderror"
                                       value="{{ old('basic_salary', $staff->basic_salary) }}" required>
                            </div>
                            @error('basic_salary')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @else
                                <small class="form-text text-muted">Monthly basic salary used for loan eligibility.</small>
                            @enderror
                        </div>
                    </div>

                    <!-- Hire Date -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Hire Date <span class="text-danger">*</span></label>
                            <input type="date" name="hire_date"
                                   class="form-control @error('hire_date') is-invalid @enderror"
                                   value="{{ old('hire_date', $staff->hire_date ? $staff->hire_date->format('Y-m-d') : '') }}" required>
                            @error('hire_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @else
                                <small class="form-text text-muted">Used to calculate years employed for loan eligibility.</small>
                            @enderror
                        </div>
                    </div>

                    <!-- Roles (Multiple) - FIXED to use $userRoles from controller -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Roles</label>
                            <div class="row">
                                @foreach($roles as $role)
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                                   class="form-check-input" id="role-{{ $role->id }}"
                                                   {{ in_array($role->name, old('roles', $userRoles ?? [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Photo -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Photo</label>
                            <input type="file" name="photo" class="form-control-file @error('photo') is-invalid @enderror">
                            @error('photo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            @if($staff->photo)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $staff->photo) }}" class="img-thumbnail" width="100">
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="col-md-12 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Staff
                        </button>
                        <a href="{{ route('staff.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@stop