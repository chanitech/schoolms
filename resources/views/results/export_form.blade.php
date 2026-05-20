@extends('adminlte::page')

@section('title', 'Export Student Results')

@section('content_header')
    <h1 class="text-center text-success">Export Student Results</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('results.export.individual') }}" method="GET" target="_blank">
            <div class="row">
                <div class="col-md-4">
                    <label>Class</label>
                    <select name="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Exam</label>
                    <select name="exam_id" class="form-control" required>
                        <option value="">Select Exam</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Academic Session</label>
                    <select name="academic_session_id" class="form-control" required>
                        <option value="">Select Session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-file-export"></i> Export PDF
                </button>
            </div>
        </form>
    </div>
</div>
@stop
