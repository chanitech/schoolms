@extends('adminlte::page')

@section('title', 'Assign Custom Bill to Student')

@section('content_header')
    <h1>Assign Custom Bill to Student</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">New Student Bill (Custom Amount)</h3>
        <div class="card-tools">
            <a href="{{ route('finance.student_bills.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('finance.student_bills.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="student_id">Student</label>
                <select name="student_id" id="student_id" class="form-control @error('student_id') is-invalid @enderror" required>
                    <option value="">-- Select Student --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->first_name }} {{ $student->last_name }} ({{ $student->admission_no }})
                        </option>
                    @endforeach
                </select>
                @error('student_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Bill Description</label>
                <input type="text" name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                       value="{{ old('description') }}" placeholder="e.g., Library Fine, Sports Fee">
                @error('description')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="amount">Amount (TZS)</label>
                <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" 
                       step="0.01" min="0" required value="{{ old('amount') }}" placeholder="Enter amount">
                @error('amount')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Assign Bill
            </button>
        </form>
    </div>
</div>
@stop