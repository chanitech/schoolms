@extends('adminlte::page')

@section('title', 'Add Event')

@section('content_header')
    <h1>Add Event</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('events.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="title" class="form-label">Event Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>
                <div class="col-md-6">
                    <label for="department_id" class="form-label">Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="type" class="form-label">Event Type</label>
                    <select name="type" class="form-control" required>
                        <option value="academic" {{ old('type')=='academic'?'selected':'' }}>Academic</option>
                        <option value="sport" {{ old('type')=='sport'?'selected':'' }}>Sports</option>
                        <option value="cultural" {{ old('type')=='cultural'?'selected':'' }}>Cultural</option>
                        <option value="holiday" {{ old('type')=='holiday'?'selected':'' }}>Holiday</option>
                        <option value="other" {{ old('type')=='other'?'selected':'' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>

            <button class="btn btn-primary">Add Event</button>
            <a href="{{ route('events.index') }}" class="btn btn-secondary">Cancel</a>
        </form>

    </div>
</div>
@stop
