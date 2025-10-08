@extends('adminlte::page')
@section('title','Assign Job Card')
@section('content_header')
    <h1 class="text-center text-success">Assign Job Card</h1>
@stop
@section('content')
<div class="container-fluid">
    <a href="{{ route('jobcards.index') }}" class="btn btn-secondary mb-3">Back to Job Cards</a>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('jobcards.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                    @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Assign To <span class="text-danger">*</span></label>
                    <select name="assigned_to" class="form-control" required>
                        <option value="">-- Select Staff --</option>
                        @foreach($staffs as $staff)
                            <option value="{{ $staff->id }}" {{ old('assigned_to') == $staff->id ? 'selected' : '' }}>
                                {{ $staff->first_name }} {{ $staff->last_name }} ({{ $staff->department->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                    @error('due_date') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-success">Assign Job</button>
            </form>
        </div>
    </div>
</div>
@stop
