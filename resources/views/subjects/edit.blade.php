@extends('adminlte::page')

@section('title', 'Edit Subject')

@section('content_header')
    <h1>Edit Subject</h1>
    <a href="{{ route('subjects.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('subjects.update', $subject->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group mb-2">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $subject->name) }}" required>
            </div>

            <div class="form-group mb-2">
                <label>Code</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $subject->code) }}">
            </div>

            <div class="form-group mb-2">
                <label>Type</label>
                <select name="type" class="form-control" required>
                    <option value="core" {{ old('type', $subject->type)=='core' ? 'selected' : '' }}>Core</option>
                    <option value="elective" {{ old('type', $subject->type)=='elective' ? 'selected' : '' }}>Elective</option>
                </select>
            </div>

            <div class="form-group mb-2">
                <label>Assign to Classes</label>
                <select name="classes[]" class="form-control" multiple>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" 
                            {{ $subject->classes->contains($class->id) ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple classes.</small>
            </div>

            <button type="submit" class="btn btn-success">Update Subject</button>
        </form>
    </div>
</div>
@endsection
