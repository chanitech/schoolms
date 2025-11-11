@extends('adminlte::page')

@section('title', 'Edit Bill')

@section('content_header')
    <h1>Edit Bill</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.bills.update', $bill->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label for="title">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $bill->title) }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="description">Description</label>
                <textarea name="description" class="form-control">{{ old('description', $bill->description) }}</textarea>
            </div>

            <div class="form-group mb-3">
                <label for="amount">Amount</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $bill->amount) }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="class_id">Class</label>
                <select name="class_id" class="form-control" required>
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id', $bill->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="due_date">Due Date</label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $bill->due_date?->format('Y-m-d')) }}">
            </div>

            <button type="submit" class="btn btn-primary">Update Bill</button>
        </form>
    </div>
</div>
@stop
