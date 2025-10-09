@extends('adminlte::page')
@section('title','Edit Job Card')
@section('content_header')
    <h1 class="text-center text-success">Edit Job Card</h1>
@stop
@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('jobcards.update', $jobcard->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="title" class="form-label">Job Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $jobcard->title) }}" required>
                    @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control">{{ old('description', $jobcard->description) }}</textarea>
                    @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="assigned_to" class="form-label">Assign To</label>
                    <select name="assigned_to" id="assigned_to" class="form-control" required>
                        @foreach($staffs as $staff)
                            <option value="{{ $staff->id }}" {{ (old('assigned_to', $jobcard->assigned_to) == $staff->id) ? 'selected' : '' }}>
                                {{ $staff->getNameAttribute() }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="pending" {{ $jobcard->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $jobcard->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $jobcard->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="due_date" class="form-label">Due Date</label>
                    <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', $jobcard->due_date?->format('Y-m-d')) }}">
                    @error('due_date') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <button type="submit" class="btn btn-success">Update Job Card</button>
                <a href="{{ route('jobcards.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@stop
