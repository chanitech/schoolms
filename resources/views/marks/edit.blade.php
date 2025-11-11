@extends('adminlte::page')

@section('title', 'Edit Mark')

@section('content_header')
    <h1>Edit Mark</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('marks.update', $mark->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                {{-- Student Info --}}
                <div class="col-md-4">
                    <label>Student</label>
                    <input type="text" class="form-control" 
                        value="{{ $mark->student->first_name ?? '-' }} {{ $mark->student->last_name ?? '-' }}" readonly>
                </div>

                <div class="col-md-4">
                    <label>Class</label>
                    <input type="text" class="form-control" value="{{ $mark->student->class->name ?? '-' }}" readonly>
                </div>

                <div class="col-md-4">
                    <label>Academic Session</label>
                    <select name="academic_session_id" class="form-control" required>
                        <option value="">Select Session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" 
                                {{ $mark->academic_session_id == $session->id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Subject</label>
                    <select name="subject_id" class="form-control" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" 
                                {{ $mark->subject_id == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Exam</label>
                    <select name="exam_id" class="form-control" required>
                        <option value="">Select Exam</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" 
                                {{ $mark->exam_id == $exam->id ? 'selected' : '' }}>
                                {{ $exam->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Mark</label>
                    <input type="number" name="mark" class="form-control" 
                        value="{{ $mark->mark }}" min="0" max="100" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Mark</button>
            <a href="{{ route('marks.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop
