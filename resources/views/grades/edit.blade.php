@extends('adminlte::page')

@section('title', 'Edit Grade')

@section('content_header')
    <h1>Edit Grade</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('grades.update', $grade) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Grade Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $grade->name) }}">
                </div>

                <div class="form-group">
                    <label for="min_mark">Minimum Mark</label>
                    <input type="number" step="0.01" name="min_mark" class="form-control" value="{{ old('min_mark', $grade->min_mark) }}">
                </div>

                <div class="form-group">
                    <label for="max_mark">Maximum Mark</label>
                    <input type="number" step="0.01" name="max_mark" class="form-control" value="{{ old('max_mark', $grade->max_mark) }}">
                </div>

                <div class="form-group">
                    <label for="point">Point (GPA)</label>
                    <input type="number" step="0.01" name="point" class="form-control" value="{{ old('point', $grade->point) }}">
                </div>

                <div class="form-group">
                    <label for="description">Description (optional)</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description', $grade->description) }}">
                </div>

                <button type="submit" class="btn btn-success mt-2">Update Grade</button>
                <a href="{{ route('grades.index') }}" class="btn btn-secondary mt-2">Cancel</a>
            </form>
        </div>
    </div>
@stop
