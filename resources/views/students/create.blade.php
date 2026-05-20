@extends('adminlte::page')

@section('title', 'Add Student')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-user-graduate"></i> Add Student</h1>
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
        <h3 class="card-title mb-0">Manual Entry</h3>
        <div>
            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#importStudentModal">
                <i class="fas fa-file-excel"></i> Import from Excel
            </button>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data" id="studentForm">
            @csrf
            
            {{-- Personal Information --}}
            <div class="card card-info card-outline mb-3">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-user"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="admission_no" class="form-label">Admission No <span class="text-danger">*</span></label>
                            <input type="text" name="admission_no" class="form-control" value="{{ old('admission_no') }}" required>
                            @error('admission_no') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                            @error('first_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                            @error('last_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender')=='male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender')=='female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}" required>
                            @error('date_of_birth') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="national_id" class="form-label">National ID</label>
                            <input type="text" name="national_id" class="form-control" value="{{ old('national_id') }}">
                            @error('national_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*" onchange="previewPhoto(event)">
                            @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                            <img id="photo-preview" src="#" alt="Photo Preview" class="img-thumbnail mt-2" style="display:none; width:80px; height:80px; object-fit:cover;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                            @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="address" class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="1">{{ old('address') }}</textarea>
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
                            <label for="guardian_id" class="form-label">Guardian</label>
                            <select name="guardian_id" class="form-control">
                                <option value="">Select Guardian</option>
                                @foreach($guardians as $guardian)
                                    <option value="{{ $guardian->id }}" {{ old('guardian_id')==$guardian->id ? 'selected' : '' }}>
                                        {{ $guardian->first_name }} {{ $guardian->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Optional - can be assigned later</small>
                            @error('guardian_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                            <select name="class_id" id="class_id" class="form-control" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id')==$class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="department_id" class="form-label">Department</label>
                            <select name="department_id" class="form-control">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id')==$department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="academic_session_id" class="form-label">Academic Session <span class="text-danger">*</span></label>
                            <select name="academic_session_id" id="academic_session_id" class="form-control" required>
                                <option value="">Select Session</option>
                                @foreach($sessions as $session)
                                    <option value="{{ $session->id }}" {{ old('academic_session_id')==$session->id ? 'selected' : '' }}>
                                        {{ $session->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_session_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="admission_date" class="form-label">Admission Date</label>
                            <input type="date" name="admission_date" class="form-control" value="{{ old('admission_date', date('Y-m-d')) }}">
                            @error('admission_date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="active" {{ old('status')=='active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="graduated" {{ old('status')=='graduated' ? 'selected' : '' }}>Graduated</option>
                                <option value="suspended" {{ old('status')=='suspended' ? 'selected' : '' }}>Suspended</option>
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
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Note:</strong> Bed allocation is optional. You can assign a bed now or later from the Dormitory Management section.
                        Only beds matching the student's gender will be shown.
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dormitory_id" class="form-label">Dormitory</label>
                            <select name="dormitory_id" id="dormitory_id" class="form-control">
                                <option value="">Select Dormitory First</option>
                                @foreach($dormitories as $dorm)
                                    <option value="{{ $dorm->id }}" data-gender="{{ $dorm->gender }}" {{ old('dormitory_id')==$dorm->id ? 'selected' : '' }}>
                                        {{ $dorm->name }} ({{ ucfirst($dorm->gender) }}) - Capacity: {{ $dorm->capacity }}
                                    </option>
                                @endforeach
                            </select>
                            @error('dormitory_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="room_id" class="form-label">Room</label>
                            <select name="room_id" id="room_id" class="form-control" disabled>
                                <option value="">First select a dormitory</option>
                            </select>
                            <small class="text-muted" id="roomInfo"></small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="bed_id" class="form-label">Bed</label>
                            <select name="bed_id" id="bed_id" class="form-control" disabled>
                                <option value="">First select a room</option>
                            </select>
                            <small class="text-muted" id="bedInfo"></small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-4">
                                <div class="form-check">
                                    <input type="checkbox" name="allocate_bed" id="allocate_bed" class="form-check-input" value="1" {{ old('allocate_bed') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allocate_bed">
                                        <i class="fas fa-check-circle text-success"></i> Allocate this bed immediately
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="bedAllocationInfo" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Bed Allocation:</strong> You are about to allocate a bed to this student. The student will be assigned to the selected bed and the bed status will be updated to "occupied".
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Student</button>
                <a href="{{ route('students.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>

{{-- Import Excel Modal --}}
<div class="modal fade" id="importStudentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Students from Excel</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Excel Format Required:</strong>
                        <ul class="mb-0">
                            <li>Columns: <code>admission_no</code>, <code>first_name</code>, <code>last_name</code>, <code>gender</code>, <code>date_of_birth</code>, <code>guardian_id</code>, <code>class_id</code>, <code>dormitory_id</code>, <code>academic_session_id</code>, <code>photo</code>.</li>
                            <li>Only <strong>admission_no, first_name, last_name</strong> are required – others can be empty.</li>
                            <li>Gender must be <code>male</code> or <code>female</code>.</li>
                            <li>Date of birth format: <code>YYYY-MM-DD</code>.</li>
                            <li>IDs (guardian, class, dormitory, session) must exist in the database.</li>
                        </ul>
                    </div>
                    <div class="form-group">
                        <label for="excel_file">Choose Excel File (.xlsx, .xls)</label>
                        <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls" required>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="skip_duplicates" class="form-check-input" id="skip_duplicates" value="1" checked>
                        <label class="form-check-label" for="skip_duplicates">Skip duplicate admission numbers (do not update existing students)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('students.download-template') }}" class="btn btn-secondary">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                    <button type="submit" class="btn btn-primary">Import & Save</button>
                </div>
            </form>
        </div>
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
    } else {
        output.src = '#';
        output.style.display = 'none';
    }
}

// Gender validation for dormitory
function validateDormitoryGender() {
    let gender = $('#gender').val();
    let dormSelect = $('#dormitory_id');
    
    if (gender) {
        dormSelect.find('option').each(function() {
            let optionGender = $(this).data('gender');
            if (optionGender && optionGender !== gender && $(this).val() !== '') {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
        
        if (dormSelect.val() && dormSelect.find('option:selected').data('gender') !== gender) {
            dormSelect.val('');
            $('#room_id').html('<option value="">First select a dormitory</option>').prop('disabled', true);
            $('#bed_id').html('<option value="">First select a room</option>').prop('disabled', true);
        }
    }
}

// Load rooms based on selected dormitory
$('#dormitory_id').change(function() {
    let dormitoryId = $(this).val();
    let roomSelect = $('#room_id');
    let bedSelect = $('#bed_id');
    
    if (dormitoryId) {
        roomSelect.html('<option value="">Loading...</option>').prop('disabled', true);
        bedSelect.html('<option value="">First select a room</option>').prop('disabled', true);
        
        $.ajax({
            url: "{{ route('students.get-rooms') }}",   // ✅ Fixed route name
            type: 'GET',
            data: { dormitory_id: dormitoryId },
            success: function(data) {
                let options = '<option value="">Select a room</option>';
                if (data.length > 0) {
                    data.forEach(function(room) {
                        let availableBeds = room.capacity - room.occupied_beds;
                        options += `<option value="${room.id}" data-available="${availableBeds}">
                            Room ${room.room_number} - Floor ${room.floor || 'Ground'} - ${room.room_type} 
                            (${availableBeds} beds available / ${room.capacity} total)
                        </option>`;
                    });
                    roomSelect.html(options).prop('disabled', false);
                    $('#roomInfo').html('<i class="fas fa-check text-success"></i> ' + data.length + ' rooms available');
                } else {
                    roomSelect.html('<option value="">No rooms available in this dormitory</option>').prop('disabled', true);
                    $('#roomInfo').html('<i class="fas fa-warning text-danger"></i> No rooms found');
                }
            },
            error: function() {
                roomSelect.html('<option value="">Error loading rooms</option>').prop('disabled', true);
                $('#roomInfo').html('<i class="fas fa-error text-danger"></i> Error loading rooms');
            }
        });
    } else {
        roomSelect.html('<option value="">First select a dormitory</option>').prop('disabled', true);
        bedSelect.html('<option value="">First select a room</option>').prop('disabled', true);
        $('#roomInfo').html('');
    }
});

// Load beds based on selected room
$('#room_id').change(function() {
    let roomId = $(this).val();
    let bedSelect = $('#bed_id');
    
    if (roomId) {
        bedSelect.html('<option value="">Loading...</option>').prop('disabled', true);
        
        $.ajax({
            url: "{{ route('students.get-beds') }}",    // ✅ Fixed route name
            type: 'GET',
            data: { room_id: roomId },
            success: function(data) {
                let options = '<option value="">Select a bed</option>';
                if (data.length > 0) {
                    data.forEach(function(bed) {
                        let statusBadge = bed.status === 'available' ? '(Available)' : '(Occupied)';
                        options += `<option value="${bed.id}" data-status="${bed.status}">
                            Bed ${bed.bed_number} - ${bed.bed_type.replace('_', ' ')} ${statusBadge}
                        </option>`;
                    });
                    bedSelect.html(options).prop('disabled', false);
                    $('#bedInfo').html('<i class="fas fa-check text-success"></i> ' + data.length + ' beds in this room');
                } else {
                    bedSelect.html('<option value="">No beds available in this room</option>').prop('disabled', true);
                    $('#bedInfo').html('<i class="fas fa-warning text-danger"></i> No beds found');
                }
            },
            error: function() {
                bedSelect.html('<option value="">Error loading beds</option>').prop('disabled', true);
                $('#bedInfo').html('<i class="fas fa-error text-danger"></i> Error loading beds');
            }
        });
    } else {
        bedSelect.html('<option value="">First select a room</option>').prop('disabled', true);
        $('#bedInfo').html('');
    }
});

// Show/hide bed allocation info
$('#allocate_bed').change(function() {
    if ($(this).is(':checked')) {
        $('#bedAllocationInfo').show();
    } else {
        $('#bedAllocationInfo').hide();
    }
});

// Validate bed selection before form submission
$('#studentForm').submit(function(e) {
    let allocateBed = $('#allocate_bed').is(':checked');
    let bedId = $('#bed_id').val();
    
    if (allocateBed && !bedId) {
        e.preventDefault();
        alert('Please select a bed to allocate or uncheck the allocation option.');
        return false;
    }
    
    if (bedId && allocateBed) {
        let selectedOption = $('#bed_id option:selected');
        let bedStatus = selectedOption.data('status');
        
        if (bedStatus === 'occupied') {
            e.preventDefault();
            alert('This bed is already occupied. Please select another bed.');
            return false;
        }
    }
});

// Gender change validation
$('#gender').change(function() {
    validateDormitoryGender();
    $('#dormitory_id').trigger('change');
});

// Initial validation on page load
$(document).ready(function() {
    validateDormitoryGender();
    
    // If dormitory was pre-selected (e.g., after validation error), load rooms
    let preselectedDorm = $('#dormitory_id').val();
    if (preselectedDorm) {
        $('#dormitory_id').trigger('change');
        
        setTimeout(function() {
            let preselectedRoom = "{{ old('room_id') }}";
            if (preselectedRoom) {
                $('#room_id').val(preselectedRoom).trigger('change');
            }
            
            let preselectedBed = "{{ old('bed_id') }}";
            if (preselectedBed) {
                $('#bed_id').val(preselectedBed);
            }
        }, 500);
    }
});
</script>
@endpush