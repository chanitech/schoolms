@extends('adminlte::page')

@section('title', 'Edit Student')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-user-edit"></i> Edit Student</h1>
@stop

@section('content')

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('success') }}
    </div>
@endif
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('warning') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('error') }}
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            Editing: <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
            <span class="badge badge-secondary ml-2">{{ $student->admission_no }}</span>
        </h3>
        <a href="{{ route('students.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Students
        </a>
    </div>

    <div class="card-body">
        <form action="{{ route('students.update', $student->id) }}" method="POST" enctype="multipart/form-data" id="studentForm">
            @csrf
            @method('PUT')

            {{-- Personal Information --}}
            <div class="card card-info card-outline mb-3">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-user"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Admission No <span class="text-danger">*</span></label>
                            <input type="text" name="admission_no" class="form-control" value="{{ old('admission_no', $student->admission_no) }}" required>
                            @error('admission_no') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $student->first_name) }}" required>
                            @error('first_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $student->last_name) }}" required>
                            @error('last_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="male"   {{ old('gender', $student->gender) == 'male'   ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" class="form-control"
                                   value="{{ old('date_of_birth', $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : '') }}" required>
                            @error('date_of_birth') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">National ID</label>
                            <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $student->national_id) }}">
                            @error('national_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*" onchange="previewPhoto(event)">
                            @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                            <div class="mt-2">
                                @if($student->photo)
                                    <img id="photo-preview"
                                         src="{{ asset('storage/' . $student->photo) }}"
                                         alt="Current Photo"
                                         class="img-thumbnail"
                                         style="width:80px; height:80px; object-fit:cover;">
                                    <small class="d-block text-muted">Current photo. Upload a new one to replace it.</small>
                                @else
                                    <img id="photo-preview" src="#" alt="Photo Preview"
                                         class="img-thumbnail mt-2"
                                         style="display:none; width:80px; height:80px; object-fit:cover;">
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}">
                            @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}">
                            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="1">{{ old('address', $student->address) }}</textarea>
                            @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Academic Information --}}
            <div class="card card-primary card-outline mb-3">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-graduation-cap"></i> Academic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Guardian</label>
                            <select name="guardian_id" class="form-control">
                                <option value="">Select Guardian</option>
                                @foreach($guardians as $guardian)
                                    <option value="{{ $guardian->id }}" {{ old('guardian_id', $student->guardian_id) == $guardian->id ? 'selected' : '' }}>
                                        {{ $guardian->first_name }} {{ $guardian->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('guardian_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select name="class_id" id="class_id" class="form-control" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-control">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $student->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Academic Session <span class="text-danger">*</span></label>
                            <select name="academic_session_id" id="academic_session_id" class="form-control" required>
                                <option value="">Select Session</option>
                                @foreach($sessions as $session)
                                    <option value="{{ $session->id }}" {{ old('academic_session_id', $student->academic_session_id) == $session->id ? 'selected' : '' }}>
                                        {{ $session->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_session_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Admission Date</label>
                            <input type="date" name="admission_date" class="form-control"
                                   value="{{ old('admission_date', $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('Y-m-d') : '') }}">
                            @error('admission_date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="active"    {{ old('status', $student->status) == 'active'    ? 'selected' : '' }}>Active</option>
                                <option value="inactive"  {{ old('status', $student->status) == 'inactive'  ? 'selected' : '' }}>Inactive</option>
                                <option value="graduated" {{ old('status', $student->status) == 'graduated' ? 'selected' : '' }}>Graduated</option>
                                <option value="suspended" {{ old('status', $student->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dormitory & Bed Assignment --}}
            <div class="card card-success card-outline mb-3">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-bed"></i> Dormitory & Bed Assignment</h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">

                    {{-- Current Bed Allocation Info --}}
                    @if($currentAllocation && $currentBed && $currentRoom)
                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle"></i>
                            <strong>Currently Allocated:</strong>
                            {{ $currentRoom->dormitory->name ?? 'N/A' }} &rarr;
                            Room {{ $currentRoom->room_number }} (Floor {{ $currentRoom->floor ?? 'Ground' }}) &rarr;
                            Bed {{ $currentBed->bed_number }}
                            <span class="badge badge-info ml-1">{{ ucfirst(str_replace('_', ' ', $currentBed->bed_type)) }}</span>
                            <span class="badge badge-success ml-1">Active</span>
                        </div>

                        {{-- Deallocate option --}}
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="deallocate_bed" id="deallocate_bed" class="form-check-input" value="1">
                                <label class="form-check-label text-danger" for="deallocate_bed">
                                    <i class="fas fa-times-circle"></i> Remove current bed allocation
                                </label>
                            </div>
                        </div>

                        <div id="deallocateWarning" class="alert alert-warning" style="display:none;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This will remove the student from
                            Bed {{ $currentBed->bed_number }}, Room {{ $currentRoom->room_number }}.
                            The bed will be marked as available.
                        </div>

                        <hr>
                        <p class="text-muted"><i class="fas fa-exchange-alt"></i> <strong>Reassign to a different bed:</strong> Select a new dormitory, room and bed below, then check "Reallocate bed".</p>

                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> This student has no active bed allocation.
                            You can assign a bed below or do it later from the Dormitory Management section.
                            Only beds matching the student's gender will be available.
                        </div>
                    @endif

                    {{-- Dormitory / Room / Bed selectors --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Dormitory</label>
                            <select name="dormitory_id" id="dormitory_id" class="form-control">
                                <option value="">Select Dormitory</option>
                                @foreach($dormitories as $dorm)
                                    <option value="{{ $dorm->id }}"
                                            data-gender="{{ $dorm->gender }}"
                                            {{ old('dormitory_id', $student->dormitory_id) == $dorm->id ? 'selected' : '' }}>
                                        {{ $dorm->name }} ({{ ucfirst($dorm->gender) }}) - Capacity: {{ $dorm->capacity }}
                                    </option>
                                @endforeach
                            </select>
                            @error('dormitory_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room</label>
                            <select name="room_id" id="room_id" class="form-control" disabled>
                                <option value="">First select a dormitory</option>
                            </select>
                            <small class="text-muted" id="roomInfo"></small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Bed</label>
                            <select name="bed_id" id="bed_id" class="form-control" disabled>
                                <option value="">First select a room</option>
                            </select>
                            <small class="text-muted" id="bedInfo"></small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-4">
                                @if($currentAllocation)
                                    <div class="form-check">
                                        <input type="checkbox" name="reallocate_bed" id="reallocate_bed" class="form-check-input" value="1" {{ old('reallocate_bed') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reallocate_bed">
                                            <i class="fas fa-exchange-alt text-warning"></i> Reallocate to selected bed
                                            <small class="d-block text-muted">(will release the current bed first)</small>
                                        </label>
                                    </div>
                                @else
                                    <div class="form-check">
                                        <input type="checkbox" name="allocate_bed" id="allocate_bed" class="form-check-input" value="1" {{ old('allocate_bed') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allocate_bed">
                                            <i class="fas fa-check-circle text-success"></i> Allocate this bed immediately
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div id="bedAllocationInfo" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-info-circle"></i>
                        <strong>Bed Allocation:</strong> The student will be assigned to the selected bed and its status will be updated to "occupied".
                    </div>

                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Student</button>
                <a href="{{ route('students.show', $student->id) }}" class="btn btn-info"><i class="fas fa-eye"></i> View Profile</a>
                <a href="{{ route('students.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>

        </form>
    </div>
</div>
@stop

@push('js')
<script>
// Photo preview
function previewPhoto(event) {
    const output = document.getElementById('photo-preview');
    if (event.target.files && event.target.files[0]) {
        output.src = URL.createObjectURL(event.target.files[0]);
        output.style.display = 'block';
    }
}

// Show/hide deallocate warning
$('#deallocate_bed').change(function () {
    if ($(this).is(':checked')) {
        $('#deallocateWarning').show();
        // Disable bed selector section when deallocating
        $('#reallocate_bed').prop('checked', false).prop('disabled', true);
        $('#allocate_bed').prop('checked', false).prop('disabled', true);
        $('#bedAllocationInfo').hide();
    } else {
        $('#deallocateWarning').hide();
        $('#reallocate_bed').prop('disabled', false);
        $('#allocate_bed').prop('disabled', false);
    }
});

// Show/hide bed allocation info
$('#reallocate_bed, #allocate_bed').change(function () {
    if ($(this).is(':checked')) {
        $('#bedAllocationInfo').show();
    } else {
        $('#bedAllocationInfo').hide();
    }
});

// Gender validation for dormitory
function validateDormitoryGender() {
    let gender = $('#gender').val();
    let dormSelect = $('#dormitory_id');

    if (gender) {
        dormSelect.find('option').each(function () {
            let optionGender = $(this).data('gender');
            if (optionGender && optionGender !== gender && $(this).val() !== '') {
                $(this).hide();
            } else {
                $(this).show();
            }
        });

        // If selected dorm no longer matches gender, reset
        let selectedGender = dormSelect.find('option:selected').data('gender');
        if (dormSelect.val() && selectedGender && selectedGender !== gender) {
            dormSelect.val('');
            $('#room_id').html('<option value="">First select a dormitory</option>').prop('disabled', true);
            $('#bed_id').html('<option value="">First select a room</option>').prop('disabled', true);
            $('#roomInfo').html('');
            $('#bedInfo').html('');
        }
    }
}

// Load rooms when dormitory changes
$('#dormitory_id').change(function () {
    let dormitoryId = $(this).val();
    let roomSelect = $('#room_id');
    let bedSelect = $('#bed_id');

    if (dormitoryId) {
        roomSelect.html('<option value="">Loading...</option>').prop('disabled', true);
        bedSelect.html('<option value="">First select a room</option>').prop('disabled', true);
        $('#bedInfo').html('');

        $.ajax({
            url: "{{ route('students.get-rooms') }}",
            type: 'GET',
            data: { dormitory_id: dormitoryId },
            success: function (data) {
                let options = '<option value="">Select a room</option>';
                if (data.length > 0) {
                    data.forEach(function (room) {
                        let availableBeds = room.capacity - room.occupied_beds;
                        options += `<option value="${room.id}" data-available="${availableBeds}">
                            Room ${room.room_number} - Floor ${room.floor || 'Ground'} - ${room.room_type}
                            (${availableBeds} beds available / ${room.capacity} total)
                        </option>`;
                    });
                    roomSelect.html(options).prop('disabled', false);
                    $('#roomInfo').html('<i class="fas fa-check text-success"></i> ' + data.length + ' rooms found');
                } else {
                    roomSelect.html('<option value="">No rooms available</option>').prop('disabled', true);
                    $('#roomInfo').html('<i class="fas fa-exclamation-triangle text-warning"></i> No rooms found');
                }
            },
            error: function () {
                roomSelect.html('<option value="">Error loading rooms</option>').prop('disabled', true);
                $('#roomInfo').html('<i class="fas fa-times text-danger"></i> Error loading rooms');
            }
        });
    } else {
        roomSelect.html('<option value="">First select a dormitory</option>').prop('disabled', true);
        bedSelect.html('<option value="">First select a room</option>').prop('disabled', true);
        $('#roomInfo').html('');
        $('#bedInfo').html('');
    }
});

// Load beds when room changes
$('#room_id').change(function () {
    let roomId = $(this).val();
    let bedSelect = $('#bed_id');

    if (roomId) {
        bedSelect.html('<option value="">Loading...</option>').prop('disabled', true);

        $.ajax({
            url: "{{ route('students.get-beds') }}",
            type: 'GET',
            data: { room_id: roomId },
            success: function (data) {
                let options = '<option value="">Select a bed</option>';
                if (data.length > 0) {
                    data.forEach(function (bed) {
                        let statusLabel = bed.status === 'available' ? '(Available)' : '(Occupied)';
                        let disabled = bed.status !== 'available' ? 'disabled' : '';
                        options += `<option value="${bed.id}" data-status="${bed.status}" ${disabled}>
                            Bed ${bed.bed_number} - ${bed.bed_type.replace('_', ' ')} ${statusLabel}
                        </option>`;
                    });
                    bedSelect.html(options).prop('disabled', false);
                    $('#bedInfo').html('<i class="fas fa-check text-success"></i> ' + data.length + ' beds in this room');
                } else {
                    bedSelect.html('<option value="">No beds in this room</option>').prop('disabled', true);
                    $('#bedInfo').html('<i class="fas fa-exclamation-triangle text-warning"></i> No beds found');
                }
            },
            error: function () {
                bedSelect.html('<option value="">Error loading beds</option>').prop('disabled', true);
                $('#bedInfo').html('<i class="fas fa-times text-danger"></i> Error loading beds');
            }
        });
    } else {
        bedSelect.html('<option value="">First select a room</option>').prop('disabled', true);
        $('#bedInfo').html('');
    }
});

// Gender change re-validates dormitory options
$('#gender').change(function () {
    validateDormitoryGender();
});

// Form validation before submit
$('#studentForm').submit(function (e) {
    let allocateBed   = $('#allocate_bed').is(':checked');
    let reallocateBed = $('#reallocate_bed').is(':checked');
    let bedId         = $('#bed_id').val();

    if ((allocateBed || reallocateBed) && !bedId) {
        e.preventDefault();
        alert('Please select a bed or uncheck the allocation option.');
        return false;
    }

    if (bedId && (allocateBed || reallocateBed)) {
        let bedStatus = $('#bed_id option:selected').data('status');
        if (bedStatus === 'occupied') {
            e.preventDefault();
            alert('This bed is already occupied. Please select an available bed.');
            return false;
        }
    }
});

// On page load — if a dormitory is already selected, load its rooms
$(document).ready(function () {
    validateDormitoryGender();

    let preselectedDorm = $('#dormitory_id').val();
    if (preselectedDorm) {
        $('#dormitory_id').trigger('change');

        // After rooms load, try to restore old room & bed selections (e.g. after validation error)
        setTimeout(function () {
            let preselectedRoom = "{{ old('room_id') }}";
            if (preselectedRoom) {
                $('#room_id').val(preselectedRoom).trigger('change');
                setTimeout(function () {
                    let preselectedBed = "{{ old('bed_id') }}";
                    if (preselectedBed) {
                        $('#bed_id').val(preselectedBed);
                    }
                }, 600);
            }
        }, 600);
    }
});
</script>
@endpush