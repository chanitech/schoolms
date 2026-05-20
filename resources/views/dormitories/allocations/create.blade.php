@extends('adminlte::page')

@section('title', 'Allocate Bed')

@section('content_header')
    <h1><i class="fas fa-user-plus"></i> Allocate Bed to Student</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('dormitories.allocations.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Student <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-control @error('student_id') is-invalid @enderror" required>
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->full_name }} ({{ $student->admission_no }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Bed <span class="text-danger">*</span></label>
                        <select name="bed_id" class="form-control @error('bed_id') is-invalid @enderror" required>
                            <option value="">Select Bed</option>
                            @foreach($beds as $bed)
                                <option value="{{ $bed->id }}" {{ old('bed_id') == $bed->id ? 'selected' : '' }}>
                                    {{ $bed->room->dormitory->name }} - Room {{ $bed->room->room_number }} - Bed {{ $bed->bed_number }}
                                </option>
                            @endforeach
                        </select>
                        @error('bed_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Academic Session <span class="text-danger">*</span></label>
                        <select name="academic_session_id" class="form-control @error('academic_session_id') is-invalid @enderror" required>
                            <option value="">Select Session</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ old('academic_session_id') == $session->id ? 'selected' : '' }}>
                                    {{ $session->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_session_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', date('Y-m-d')) }}" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Allocate Bed</button>
                <a href="{{ route('dormitories.allocations') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>
@stop