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

                {{-- Exam --}}
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="exam_id">Exam</label>
                        <select name="exam_id" id="exam_id" class="form-control" required>
                            <option value="">Select Exam</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->name }}</option>
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

                {{-- Department --}}
                <div class="col-md-3">
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
