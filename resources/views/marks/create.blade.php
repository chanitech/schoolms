@extends('adminlte::page')

@section('title', 'Add Mark')

@section('content_header')
    <h1>Add Mark</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('marks.store') }}" method="POST">
            @csrf

            {{-- Student --}}
            <div class="form-group mb-3">
                <label for="student_id">Student</label>
                <select name="student_id" id="student_id" class="form-control @error('student_id') is-invalid @enderror" required>
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->first_name }} {{ $student->last_name }} ({{ $student->admission_no }})
                        </option>
                    @endforeach
                </select>
                @error('student_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Subject --}}
            <div class="form-group mb-3">
                <label for="subject_id">Subject</label>
                <select name="subject_id" id="subject_id" class="form-control @error('subject_id') is-invalid @enderror" required>
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }} ({{ $subject->code }})
                        </option>
                    @endforeach
                </select>
                @error('subject_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Exam --}}
            <div class="form-group mb-3">
                <label for="exam_id">Exam</label>
                <select name="exam_id" id="exam_id" class="form-control @error('exam_id') is-invalid @enderror" required>
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}" {{ old('exam_id') == $exam->id ? 'selected' : '' }}>
                            {{ $exam->name }} ({{ $exam->term }})
                        </option>
                    @endforeach
                </select>
                @error('exam_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Mark --}}
            <div class="form-group mb-3">
                <label for="mark">Mark</label>
                <input type="number" name="mark" id="mark" class="form-control @error('mark') is-invalid @enderror" value="{{ old('mark') }}" min="0" max="100" step="0.01" required>
                @error('mark')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-success">Save Mark</button>
            <a href="{{ route('marks.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop
