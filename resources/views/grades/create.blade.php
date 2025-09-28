@extends('adminlte::page')

@section('title', 'Add Grade')

@section('content_header')
    <h1>Add Grade</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('grades.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Grade Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g., A">
                </div>

                <div class="form-group">
                    <label for="min_mark">Minimum Mark</label>
                    <input type="number" step="0.01" name="min_mark" class="form-control" value="{{ old('min_mark') }}">
                </div>

                <div class="form-group">
                    <label for="max_mark">Maximum Mark</label>
                    <input type="number" step="0.01" name="max_mark" class="form-control" value="{{ old('max_mark') }}">
                </div>

                <div class="form-group">
                    <label for="point">Point (GPA)</label>
                    <input type="number" step="0.01" name="point" class="form-control" value="{{ old('point') }}">
                </div>

                <div class="form-group">
                    <label for="description">Description (optional)</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                </div>

                <button type="submit" class="btn btn-success mt-2">Save Grade</button>
                <a href="{{ route('grades.index') }}" class="btn btn-secondary mt-2">Cancel</a>
            </form>
        </div>
    </div>
@stop
