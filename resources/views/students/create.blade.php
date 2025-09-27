@extends('adminlte::page')

@section('title', 'Add Student')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-user-graduate"></i> Add Student</h1>
@stop

@section('content')
<div class="card shadow">
    <div class="card-body">
        <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="admission_no" class="form-label">Admission No</label>
                    <input type="text" name="admission_no" class="form-control" value="{{ old('admission_no') }}">
                    @error('admission_no') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}">
                    @error('first_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
                    @error('last_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="gender" class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender')=='male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender')=='female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-3">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                    @error('date_of_birth') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-3">
                    <label for="photo" class="form-label">Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/*" onchange="previewPhoto(event)">
                    @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                    <img id="photo-preview" src="#" alt="Photo Preview" class="img-thumbnail mt-2" style="display:none; width:100px; height:100px; object-fit:cover;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="guardian_id" class="form-label">Guardian</label>
                    <select name="guardian_id" class="form-control">
                        <option value="">Select Guardian</option>
                        @foreach($guardians as $guardian)
                            <option value="{{ $guardian->id }}" {{ old('guardian_id')==$guardian->id ? 'selected' : '' }}>
                                {{ $guardian->first_name }} {{ $guardian->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('guardian_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-3">
                    <label for="class_id" class="form-label">Class</label>
                    <select name="class_id" class="form-control">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id')==$class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-3">
                    <label for="dormitory_id" class="form-label">Dormitory</label>
                    <select name="dormitory_id" class="form-control">
                        <option value="">Select Dormitory</option>
                        @foreach($dormitories as $dorm)
                            <option value="{{ $dorm->id }}" {{ old('dormitory_id')==$dorm->id ? 'selected' : '' }}>
                                {{ $dorm->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('dormitory_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-3">
                    <label for="academic_session_id" class="form-label">Academic Session</label>
                    <select name="academic_session_id" class="form-control">
                        <option value="">Select Session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ old('academic_session_id')==$session->id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('academic_session_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Student</button>
        </form>
    </div>
</div>
@stop

@push('js')
<script>
function previewPhoto(event) {
    const output = document.getElementById('photo-preview');
    output.src = URL.createObjectURL(event.target.files[0]);
    output.style.display = 'block';
}
</script>
@endpush
