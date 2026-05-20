@extends('adminlte::page')

@section('title', 'Export Student Marksheet')

@section('content_header')
    <h1>Export Student Marksheet</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('results.export.pdf') }}" method="POST">
            @csrf
            <div class="row">
                {{-- Class --}}
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select name="class_id" id="class_id" class="form-control" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Academic Session --}}
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="academic_session_id">Academic Session</label>
                        <select name="academic_session_id" id="academic_session_id" class="form-control" required>
                            <option value="">Select Session</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}">{{ $session->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Exam Type Filter --}}
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="exam_type_filter">Exam Type</label>
                        <select id="exam_type_filter" class="form-control">
                            <option value="both">Terminal + Annual Exams</option>
                            <option value="terminal">Terminal Exams Only</option>
                            <option value="annual">Annual Exams Only</option>
                        </select>
                    </div>
                </div>

                {{-- Exam (dynamically loaded) --}}
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="exam_id">Exam</label>
                        <select name="exam_id" id="exam_id" class="form-control" required>
                            <option value="">— First select session —</option>
                        </select>
                    </div>
                </div>

                {{-- Department --}}
                <div class="col-md-12 mt-2">
                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select name="department_id" id="department_id" class="form-control">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@push('js')
<script>
    const sessionSelect = document.getElementById('academic_session_id');
    const examSelect    = document.getElementById('exam_id');
    const examTypeFilter = document.getElementById('exam_type_filter');

    function loadExams() {
        const sessionId = sessionSelect.value;
        const examType  = examTypeFilter.value;

        if (!sessionId) {
            examSelect.innerHTML = '<option value="">— First select a session —</option>';
            examSelect.disabled = false;
            return;
        }

        examSelect.innerHTML = '<option value="">Loading exams…</option>';
        examSelect.disabled = true;

        fetch(`{{ route('marks.exams.by.session') }}?session_id=${sessionId}&exam_type=${examType}`)
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(exams => {
                let options = '<option value="">— Select Exam —</option>';
                if (exams.length > 0) {
                    exams.forEach(exam => {
                        options += `<option value="${exam.id}">${exam.name}</option>`;
                    });
                } else {
                    options = '<option value="" disabled>No exams match the selected type</option>';
                }
                examSelect.innerHTML = options;
                examSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading exams:', error);
                examSelect.innerHTML = '<option value="">Error loading exams</option>';
                examSelect.disabled = false;
            });
    }

    sessionSelect.addEventListener('change', loadExams);
    examTypeFilter.addEventListener('change', loadExams);

    document.addEventListener('DOMContentLoaded', function() {
        if (sessionSelect.value) {
            loadExams();
        }
    });
</script>
@endpush