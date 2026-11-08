@extends('adminlte::page')

@section('title', 'Edit Budget')

@section('content_header')
    <h1>Edit Budget</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('finance.budgets.update', $budget) }}">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Department</label>
                    <select name="department_id" class="form-control" required>
                        @foreach($departments as $dep)
                            <option value="{{ $dep->id }}" @selected(old('department_id', $budget->department_id) == $dep->id)>{{ $dep->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Month</label>
                    <select name="month" class="form-control" required>
                        @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                            <option value="{{ $m }}" @selected(old('month', $budget->month) == $m)>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Year</label>
                    <input type="number" name="year" class="form-control" value="{{ old('year', $budget->year) }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label>Note</label>
                <textarea name="note" class="form-control" maxlength="500">{{ old('note', $budget->note) }}</textarea>
            </div>

            <button class="btn btn-success"><i class="fas fa-save"></i> Update Budget</button>
            <a href="{{ route('finance.budgets.show', $budget) }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop
