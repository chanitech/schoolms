@extends('adminlte::page')

@section('title', 'Add Division')

@section('content_header')
    <h1>Add Division</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('divisions.store') }}" method="POST">
            @csrf

            <div class="form-group mb-3">
                <label for="name">Division Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group mb-3">
                <label for="min_points">Minimum Points</label>
                <input type="number" name="min_points" class="form-control" value="{{ old('min_points') }}" required>
                @error('min_points') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group mb-3">
                <label for="max_points">Maximum Points</label>
                <input type="number" name="max_points" class="form-control" value="{{ old('max_points') }}" required>
                @error('max_points') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group mb-3">
                <label for="description">Description (Optional)</label>
                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-success">Save Division</button>
            <a href="{{ route('divisions.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@stop
