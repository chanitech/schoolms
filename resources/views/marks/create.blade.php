@extends('adminlte::page')

@section('title', 'Add Marks')

@section('content_header')
    <h1>Add Marks</h1>
@stop

@section('content')
<div class="card">
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
                            <option value="{{ $session->id }}">{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Class --}}
                <div class="col-md-3">
                    <label for="class">Class</label>
                    <select name="class_id" id="class" class="form-control" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Department --}}
                <div class="col-md-3">
                    <label for="department">Department</label>
                    <select name="department_id" id="department" class="form-control">
                        <option value="">Select Department</option>
                        @foreach(\App\Models\Department::all() as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject --}}
                <div class="col-md-3">
                    <label for="subject">Subject</label>
                    <select name="subject_id" id="subject" class="form-control" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
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
                        <option value="">Select Exam</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                        @endforeach
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
                                <td colspan="2" class="text-center">Select class, session & subject to load students</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Marks</button>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function(){

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
                        options += `<option value="${subject.id}">${subject.name}</option>`;
                    });
                    $('#subject').html(options);
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

    // Load students for selected class, session, subject
    function loadStudents() {
        let class_id = $('#class').val();
        let session_id = $('#session').val();
        let subject_id = $('#subject').val();
        let exam_id = $('#exam').val(); // optional if you want exam-specific marks

        if(class_id && session_id && subject_id){
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
                    let tbody = '';
                    if(students.length > 0){
                        students.forEach(function(student){
                            // Pre-fill mark if it exists
                            let mark = student.mark ?? '';
                            tbody += `<tr>
                                <td>${student.first_name} ${student.last_name}</td>
                                <td>
                                    <input type="number" name="marks[${student.id}]" class="form-control" min="0" max="100" value="${mark}">
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
            $('#students-table tbody').html('<tr><td colspan="2" class="text-center">Select class, session & subject to load students</td></tr>');
        }
    }

    // Trigger AJAX on change
    $('#department').change(loadSubjects);
    $('#class, #session, #subject, #exam').change(loadStudents);

});
</script>
@endsection
