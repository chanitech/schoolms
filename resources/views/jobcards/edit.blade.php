@extends('adminlte::page')
@section('title','Edit Job Card')
@section('content_header')
    <h1 class="text-center text-success">Edit Job Card</h1>
@stop
@section('content')
<div class="container-fluid">
    <a href="{{ route('jobcards.index') }}" class="btn btn-secondary mb-3">Back to Job Cards</a>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('jobcards.update', $jobcard->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $jobcard->title) }}" required>
                    @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $jobcard->description) }}</textarea>
                    @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Assign To <span class="text-danger">*</span></label>
                    <select name="assigned_to" class="form-control" required>
                        <option value="">-- Select Staff --</option>
                        @foreach($staffs as $staff)
                            <option value="{{ $staff->id }}" {{ old('assigned_to', $jobcard->assigned_to) == $staff->id ? 'selected' : '' }}>
                                {{ $staff->first_name }} {{ $staff->last_name }} ({{ $staff->department->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        @foreach(['pending','in_progress','completed'] as $status)
                            <option value="{{ $status }}" {{ old('status', $jobcard->status) == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                    @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Rating (1-5)</label>
                    <input type="number" name="rating" class="form-control" min="1" max="5" value="{{ old('rating', $jobcard->rating) }}">
                    @error('rating') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $jobcard->due_date) }}">
                    @error('due_date') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update Job</button>
            </form>
        </div>
    </div>
</div>
@stop
