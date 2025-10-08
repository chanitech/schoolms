@extends('adminlte::page')

@section('title', 'Edit Department')

@section('content_header')
    <h1 class="text-center text-success">Edit Department</h1>
@stop

@section('content')
<div class="container-fluid">

    <form action="{{ route('departments.update', $department->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Department Name --}}
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $department->name) }}" required>
        </div>

        {{-- Description --}}
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ old('description', $department->description) }}</textarea>
        </div>

        {{-- Department Head --}}
        <div class="mb-3">
            <label class="form-label">Department Head</label>
            <select name="head_id" class="form-control">
                <option value="">-- Select Head --</option>
                @foreach(\App\Models\Staff::all() as $staff)
                    <option value="{{ $staff->id }}" {{ $department->head_id == $staff->id ? 'selected' : '' }}>
                        {{ $staff->name }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Optional: Assign a head for this department</small>
        </div>

        <button class="btn btn-primary">Update Department</button>
        <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@stop
