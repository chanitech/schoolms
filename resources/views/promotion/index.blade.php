@extends('adminlte::page')

@section('title', 'Promotion Module')

@section('content_header')
<h1>Student Promotion</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Promote Entire Class</h3>
            </div>

            <form action="{{ route('promotion.class') }}" method="POST">
                @csrf

                <div class="card-body">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>From Class</label>
                                <select name="from_class_id" id="from_class" class="form-control" required>
                                    <option value="">Select Class</option>
                                    @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->section }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>From Academic Session</label>
                                <select name="from_session_id" id="from_session" class="form-control" required>
                                    @foreach($sessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>To Academic Session</label>
                                <select name="to_session_id" class="form-control" required>
                                    @foreach($sessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" name="auto_next_class" class="form-check-input" id="auto_next_class" value="1" checked>
                                    <label class="form-check-label" for="auto_next_class">Auto detect next class</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Or select target class manually</label>
                                <select name="to_class_id" class="form-control">
                                    <option value="">Select Class</option>
                                    @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->section }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Students table --}}
                    <div id="students-container" style="display:none;">
                        <h5>Students in Class</h5>
                        <table class="table table-bordered" id="students-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Roll No</th>
                                    <th>Student Name</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>

                <div class="card-footer">
                    <button class="btn btn-primary">Promote Class</button>
                </div>
            </form>
        </div>

    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromClass = document.getElementById('from_class');
    const fromSession = document.getElementById('from_session');
    const studentsContainer = document.getElementById('students-container');
    const studentsTableBody = document.querySelector('#students-table tbody');

    function loadStudents() {
        const classId = fromClass.value;
        const sessionId = fromSession.value;

        if (!classId || !sessionId) {
            studentsContainer.style.display = 'none';
            studentsTableBody.innerHTML = '';
            return;
        }

        fetch(`/promotion/students?class_id=${classId}&session_id=${sessionId}`)
            .then(res => res.json())
            .then(data => {
                studentsTableBody.innerHTML = '';
                if (data.length === 0) {
                    studentsTableBody.innerHTML = '<tr><td colspan="3" class="text-center">No students found.</td></tr>';
                } else {
                    data.forEach((student, i) => {
                        studentsTableBody.innerHTML += `
                            <tr>
                                <td>${i+1}</td>
                                <td>${student.roll_no}</td>
                                <td>${student.first_name} ${student.last_name}</td>
                            </tr>
                        `;
                    });
                }
                studentsContainer.style.display = 'block';
            })
            .catch(err => {
                console.error(err);
                studentsContainer.style.display = 'none';
            });
    }

    fromClass.addEventListener('change', loadStudents);
    fromSession.addEventListener('change', loadStudents);
});
</script>
@stop
