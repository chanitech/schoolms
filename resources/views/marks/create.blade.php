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
                <div class="col-md-3">
                    <label for="session">Academic Session</label>
                    <select name="academic_session_id" id="session" class="form-control" required>
                        <option value="">Select Session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="class">Class</label>
                    <select name="class_id" id="class" class="form-control" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

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
                                <td colspan="2" class="text-center">Select class & session to load students</td>
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

    function loadStudents() {
        let class_id = $('#class').val();
        let session_id = $('#session').val();

        if(class_id && session_id){
            $.ajax({
                url: "{{ route('marks.students') }}",
                type: 'GET',
                data: { class_id: class_id, session_id: session_id },
                success: function(students){
                    let tbody = '';
                    if(students.length > 0){
                        students.forEach(function(student){
                            tbody += `<tr>
                                <td>${student.first_name} ${student.last_name}</td>
                                <td>
                                    <input type="number" name="marks[${student.id}]" class="form-control" required min="0" max="100">
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
            $('#students-table tbody').html('<tr><td colspan="2" class="text-center">Select class & session to load students</td></tr>');
        }
    }

    $('#class, #session').change(loadStudents);
});
</script>
@endsection
