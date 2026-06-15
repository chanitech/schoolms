@extends('adminlte::page')

@section('title', 'Add Guardian')

@section('content_header')
    <h1 class="text-success">Add New Guardian</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Guardian Information</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('guardians.store') }}" method="POST">
                @csrf
                <div class="row">
                    <!-- First Name -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name') }}" required>
                            @error('first_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <!-- Last Name -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name') }}" required>
                            @error('last_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <!-- Gender -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                                <option value="">-- Select --</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <!-- Relation -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Relation to Student <span class="text-danger">*</span></label>
                            <input type="text" name="relation_to_student" class="form-control @error('relation_to_student') is-invalid @enderror"
                                   value="{{ old('relation_to_student') }}" required
                                   placeholder="e.g., Father, Mother, Uncle">
                            @error('relation_to_student')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <!-- Phone -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}" required>
                            @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <!-- Email -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            <small class="text-muted">This will be used for login.</small>
                            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <!-- Address -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                   value="{{ old('address') }}">
                            @error('address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <!-- Occupation -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Occupation</label>
                            <input type="text" name="occupation" class="form-control @error('occupation') is-invalid @enderror"
                                   value="{{ old('occupation') }}">
                            @error('occupation')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <!-- National ID -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>National ID</label>
                            <input type="text" name="national_id" class="form-control @error('national_id') is-invalid @enderror"
                                   value="{{ old('national_id') }}">
                            @error('national_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <!-- Link Children -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Select Children</label>
                            <select name="student_ids[]" class="form-control select2 @error('student_ids') is-invalid @enderror" multiple>
                                @foreach($unlinkedStudents as $student)
                                    <option value="{{ $student->id }}"
                                        {{ in_array($student->id, old('student_ids', [])) ? 'selected' : '' }}>
                                        {{ $student->full_name }} ({{ $student->admission_no ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('student_ids')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <small class="form-text text-muted">You can select multiple children for this guardian.</small>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="col-md-12 mt-3">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Guardian
                        </button>
                        <a href="{{ route('guardians.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('js')
<!-- Enable Select2 for multi-select -->
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Search for a student...',
            allowClear: true
        });
    });
</script>
@endpush