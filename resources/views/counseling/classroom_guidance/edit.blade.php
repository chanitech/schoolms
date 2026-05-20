@extends('adminlte::page')

@section('title', 'Edit Classroom Guidance')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Edit Classroom Guidance</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('classroom-guidances.update', $classroomGuidance->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="class_id">Class</label>
                <select name="class_id" class="form-control" required>
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $classroomGuidance->class_id == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                @error('class_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label for="date">Date</label>
                <input type="date" name="date" class="form-control" value="{{ $classroomGuidance->date->format('Y-m-d') }}" required>
                @error('date') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label for="tasks">Tasks</label>
                <textarea name="tasks" class="form-control" rows="3">{{ $classroomGuidance->tasks }}</textarea>
            </div>

            <div class="mb-3">
                <label for="achievements">Achievements</label>
                <textarea name="achievements" class="form-control" rows="3">{{ $classroomGuidance->achievements }}</textarea>
            </div>

            <div class="mb-3">
                <label for="challenges">Challenges</label>
                <textarea name="challenges" class="form-control" rows="3">{{ $classroomGuidance->challenges }}</textarea>
            </div>

            <button class="btn btn-success">Update</button>
            <a href="{{ route('classroom-guidances.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop
