@extends('adminlte::page')

@section('title', 'Add Subject')

@section('content_header')
    <h1>Add Subject</h1>
    <a href="{{ route('subjects.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('subjects.store') }}" method="POST">
            @csrf
            <div class="form-group mb-2">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            <div class="form-group mb-2">
                <label>Code</label>
                <input type="text" name="code" class="form-control" value="{{ old('code') }}">
            </div>

            <div class="form-group mb-2">
                <label>Type</label>
                <select name="type" class="form-control" required>
                    <option value="core" {{ old('type')=='core' ? 'selected' : '' }}>Core</option>
                    <option value="elective" {{ old('type')=='elective' ? 'selected' : '' }}>Elective</option>
                </select>
            </div>

            <div class="form-group mb-2">
                <label>Assign to Classes</label>
                <select name="classes[]" class="form-control" multiple>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple classes.</small>
            </div>

            <button type="submit" class="btn btn-success">Save Subject</button>
        </form>
    </div>
</div>
@endsection
