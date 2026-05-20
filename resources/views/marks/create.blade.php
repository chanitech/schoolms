@extends('adminlte::page')

@section('title', 'Add Marks')

@section('content_header')
    <h1>Add Marks</h1>
@stop

@section('content')
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            {{ session('warning') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Manual Entry</h3>
        <div>
            {{-- Filtered student list template --}}
            <a href="#" id="downloadFilteredBtn" class="btn btn-sm btn-info mr-2" style="display: none;">
                <i class="fas fa-users"></i> Download Student List
            </a>

            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#importExcelModal">
                <i class="fas fa-file-excel"></i> Import from Excel
            </button>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('marks.store') }}" method="POST" id="marksForm">
            @csrf

            <div class="row mb-3">
                {{-- Academic Session --}}
                <div class="col-md-3">
                    <label for="session">Academic Session</label>
                    <select name="academic_session_id" id="session" class="form-control" required>
                        <option value="">Select Session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ old('academic_session_id', $selectedSession ?? '') == $session->id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Class --}}
                <div class="col-md-3">
                    <label for="class">Class</label>
                    <select name="class_id" id="class" class="form-control" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $selectedClass ?? '') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Department --}}
                <div class="col-md-3">
                    <label for="department">Department</label>
                    <select name="department_id" id="department" class="form-control">
                        <option value="">Select Department</option>
                        @foreach(\App\Models\Department::all() as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $selectedDepartment ?? '') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject --}}
                <div class="col-md-3">
                    <label for="subject">Subject</label>
                    <select name="subject_id" id="subject" class="form-control" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $selectedSubject ?? '') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @if(auth()->user()->hasRole('Teacher'))
                        <small class="text-muted">Only your assigned subjects are shown</small>
                    @endif
                </div>
            </div>

            {{-- Exam --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="exam">Exam</label>
                    <select name="exam_id" id="exam" class="form-control" required>
                        <option value="">-- Select Session First --</option>
                    </select>
                </div>
            </div>

            {{-- Students Table --}}
            <div class="row">
                <div class="col-12">
                    <table class="table table-bordered" id="students-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Mark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2" class="text-center">Select class, session, subject & exam to load students</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Marks</button>
        </form>
    </div>
</div>

{{-- Import Excel Modal --}}
<div class="modal fade" id="importExcelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('marks.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Marks from Excel</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Excel Format Required:</strong>
                        <ul class="mb-0">
                            <li>Columns: <code>student_id</code> or <code>admission_no</code>, and <code>mark</code></li>
                            <li>Make sure you have selected <strong>Class, Session, Subject & Exam</strong> before importing.</li>
                            <li>Marks must be between 0 and 100.</li>
                        </ul>
                    </div>
                    <div class="form-group">
                        <label for="excel_file">Choose Excel File (.xlsx, .xls)</label>
                        <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls" required>
                    </div>
                    <input type="hidden" name="class_id" id="import_class_id">
                    <input type="hidden" name="session_id" id="import_session_id">
                    <input type="hidden" name="subject_id" id="import_subject_id">
                    <input type="hidden" name="exam_id" id="import_exam_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import & Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function(){

    // Load exams based on selected session
    function loadExams() {
        let session_id = $('#session').val();
        let examSelect = $('#exam');
        
        if(session_id) {
            examSelect.html('<option value="">Loading...</option>');
            $.ajax({
                url: "{{ route('marks.exams.by.session') }}",
                type: 'GET',
                data: { session_id: session_id },
                success: function(exams) {
                    let options = '<option value="">Select Exam</option>';
                    if(exams.length > 0) {
                        exams.forEach(function(exam) {
                            let selected = ({{ $selectedExam ?? 'null' }} == exam.id) ? 'selected' : '';
                            options += `<option value="${exam.id}" ${selected}>${exam.name}</option>`;
                        });
                    } else {
                        options = '<option value="">No exams found for this session</option>';
                    }
                    examSelect.html(options);
                    
                    // If an exam was pre-selected, trigger loadStudents
                    let preselectedExam = $('#exam').val();
                    if(preselectedExam) {
                        loadStudents();
                    }
                },
                error: function(xhr) {
                    console.error('Failed to fetch exams');
                    examSelect.html('<option value="">Error loading exams</option>');
                }
            });
        } else {
            examSelect.html('<option value="">-- Select Session First --</option>');
        }
    }

    // Load subjects based on department
    function loadSubjects() {
        let department_id = $('#department').val();
        if(department_id){
            $.ajax({
                url: "{{ route('marks.subjects.by.department') }}",
                type: 'GET',
                data: { department_id: department_id },
                success: function(subjects){
                    let options = '<option value="">Select Subject</option>';
                    subjects.forEach(function(subject){
                        let selected = ({{ $selectedSubject ?? 'null' }} == subject.id) ? 'selected' : '';
                        options += `<option value="${subject.id}" ${selected}>${subject.name}</option>`;
                    });
                    $('#subject').html(options);
                    
                    // If a subject was pre-selected, trigger loadStudents if exam also selected
                    let preselectedSubject = $('#subject').val();
                    let exam_id = $('#exam').val();
                    if(preselectedSubject && exam_id) {
                        loadStudents();
                    }
                },
                error: function(xhr){
                    console.error('Failed to fetch subjects for this department');
                    $('#subject').html('<option value="">Could not fetch subjects</option>');
                }
            });
        } else {
            $('#subject').html('<option value="">Select Subject</option>');
        }
    }

    // Load students for selected class, session, subject and exam
    function loadStudents() {
        let class_id = $('#class').val();
        let session_id = $('#session').val();
        let subject_id = $('#subject').val();
        let exam_id = $('#exam').val();

        if(class_id && session_id && subject_id && exam_id){
            $.ajax({
                url: "{{ route('marks.students') }}",
                type: 'GET',
                data: { 
                    class_id: class_id, 
                    session_id: session_id,
                    subject_id: subject_id,
                    exam_id: exam_id
                },
                success: function(students){
                    // Sort students alphabetically by full name
                    students.sort((a, b) => {
                        let nameA = `${a.first_name} ${a.last_name}`;
                        let nameB = `${b.first_name} ${b.last_name}`;
                        return nameA.localeCompare(nameB);
                    });

                    let tbody = '';
                    if(students.length > 0){
                        students.forEach(function(student){
                            let mark = student.mark ?? '';
                            tbody += `<tr>
                                <td>${student.first_name} ${student.last_name}</td>
                                <td>
                                    <input type="number" 
                                           name="marks[${student.id}]" 
                                           class="form-control" 
                                           min="0" 
                                           max="100" 
                                           step="0.01" 
                                           value="${mark}">
                                </td>
                            </tr>`;
                        });
                    } else {
                        tbody = `<tr><td colspan="2" class="text-center">No students found</td></tr>`;
                    }
                    $('#students-table tbody').html(tbody);
                },
                error: function(xhr){
                    let message = xhr.responseJSON?.error || 'Failed to load students';
                    $('#students-table tbody').html(`<tr><td colspan="2" class="text-center text-danger">${message}</td></tr>`);
                    console.error('AJAX error:', message);
                }
            });
        } else {
            $('#students-table tbody').html('<tr><td colspan="2" class="text-center">Select class, session, subject & exam to load students</td></tr>');
        }
    }

    // Update the dynamic download link for filtered student list
    function updateDownloadLink() {
        let class_id = $('#class').val();
        let session_id = $('#session').val();
        let exam_id = $('#exam').val();
        let subject_id = $('#subject').val();
        let department_id = $('#department').val();

        if (class_id && session_id && exam_id && subject_id) {
            let params = new URLSearchParams({
                class_id: class_id,
                academic_session_id: session_id,
                exam_id: exam_id,
                subject_id: subject_id,
                department_id: department_id
            });
            let url = "{{ route('marks.download-filtered-template') }}" + "?" + params.toString();
            $('#downloadFilteredBtn').attr('href', url).show();
        } else {
            $('#downloadFilteredBtn').hide();
        }
    }

    // Trigger AJAX on change
    $('#department').change(loadSubjects);
    $('#session').change(function() {
        loadExams();
        // Clear students table and hide download link until exam is selected
        $('#students-table tbody').html('<tr><td colspan="2" class="text-center">Select class, session, subject & exam to load students</td></tr>');
        $('#downloadFilteredBtn').hide();
        // Clear exam dropdown selection (already handled in loadExams)
    });
    $('#class, #subject, #exam').change(function() {
        loadStudents();
        updateDownloadLink();
    });

    // Initial calls
    updateDownloadLink();
    
    // Load exams if session is pre-selected (e.g., after form validation error)
    if($('#session').val()) {
        loadExams();
    }

    // Before opening the import modal, copy current selections to hidden fields
    $('#importExcelModal').on('show.bs.modal', function () {
        $('#import_class_id').val($('#class').val());
        $('#import_session_id').val($('#session').val());
        $('#import_subject_id').val($('#subject').val());
        $('#import_exam_id').val($('#exam').val());
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endsection