@extends('adminlte::page')

@section('title', 'Edit Subject')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="text-primary mb-0">Edit Subject</h1>
    <a href="{{ route('subjects.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>
@stop

@section('content')

<div class="card shadow">
    <div class="card-body">

        {{-- ================= ALERT MESSAGES ================= --}}
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ================= FORM ================= --}}
        <form action="{{ route('subjects.update', $subject->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Subject Name -->
            <div class="form-group mb-3">
                <label class="fw-bold">Subject Name</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $subject->name) }}" required>
            </div>

            <!-- Subject Code -->
            <div class="form-group mb-3">
                <label class="fw-bold">Subject Code</label>
                <input type="text" name="code" class="form-control"
                       value="{{ old('code', $subject->code) }}">
            </div>

            <!-- Subject Type -->
            <div class="form-group mb-3">
                <label class="fw-bold">Subject Type</label>
                <select name="type" class="form-control" required>
                    <option value="core" {{ old('type', $subject->type)=='core'?'selected':'' }}>Core</option>
                    <option value="elective" {{ old('type', $subject->type)=='elective'?'selected':'' }}>Elective</option>
                </select>
            </div>

            <!-- Department -->
            <div class="form-group mb-3">
                <label class="fw-bold">Department</label>
                <select name="department_id" class="form-control" required>
                    <option value="">-- Select Department --</option>

                    @foreach($departments as $department)
                        <option value="{{ $department->id }}"
                            {{ old('department_id', $subject->department_id)==$department->id?'selected':'' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <!-- Classes + Teachers Assignment -->
            <div class="form-group mb-3">
                <label class="fw-bold">Assign Classes & Teachers</label>

                @foreach($classes as $class)

                    @php
                        $isChecked = old('classes')
                            ? in_array($class->id, old('classes'))
                            : $subject->classes->pluck('id')->contains($class->id);

                        $assignedTeacher =
                            old('teacher.'.$class->id)
                            ?? $subject->classes->find($class->id)?->pivot->teacher_id;
                    @endphp

                    <div class="border rounded p-2 mb-2">

                        {{-- Hidden safety input --}}
                        <input type="hidden"
                               name="teacher[{{ $class->id }}]"
                               value="">

                        <div class="form-check">
                            <input type="checkbox"
                                   class="form-check-input"
                                   name="classes[]"
                                   value="{{ $class->id }}"
                                   {{ $isChecked?'checked':'' }}>

                            <label class="form-check-label fw-bold">
                                {{ $class->name }}
                            </label>
                        </div>

                        <select name="teacher[{{ $class->id }}]"
                                class="form-control mt-2">

                            <option value="">-- Select Teacher --</option>

                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}"
                                    {{ $assignedTeacher==$teacher->id?'selected':'' }}>
                                    {{ $teacher->first_name }} {{ $teacher->last_name }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                @endforeach

            </div>

            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Update Subject
            </button>

        </form>
    </div>
</div>

@stop