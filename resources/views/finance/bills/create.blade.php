@extends('adminlte::page')

@section('title', 'Create Bill')

@section('content_header')
    <h1>Create Bill</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('finance.bills.store') }}" method="POST">
            @csrf

            <div class="form-group mb-3">
                <label for="title">Bill Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="description">Description (optional)</label>
                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
            </div>

            <div class="form-group mb-3">
                <label for="amount">Amount</label>
                <input type="number" name="amount" step="0.01" class="form-control" value="{{ old('amount') }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="due_date">Due Date (optional)</label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
            </div>

            <div class="form-group mb-3">
                <label for="class_id">Class</label>
                <select name="class_id" class="form-control" required>
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Create Bill</button>
            <a href="{{ route('finance.bills.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop
