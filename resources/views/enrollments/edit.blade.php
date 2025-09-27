@extends('adminlte::page')

@section('title', 'Edit Enrollment')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-user-check"></i> Edit Enrollment</h1>
@stop

@section('content')
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('enrollments.update', $enrollment->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="student_id" class="form-label">Student</label>
                        <select name="student_id" class="form-control">
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ $enrollment->student_id==$student->id?'selected':'' }}>
                                    {{ $student->first_name }} {{ $student->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('student_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="class_id" class="form-label">Class</label>
                        <select name="class_id" class="form-control">
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $enrollment->class_id==$class->id?'selected':'' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="academic_session_id" class="form-label">Academic Session</label>
                        <select name="academic_session_id" class="form-control">
                            <option value="">Select Session</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ $enrollment->academic_session_id==$session->id?'selected':'' }}>
                                    {{ $session->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_session_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="roll_no" class="form-label">Roll No</label>
                        <input type="number" name="roll_no" class="form-control" value="{{ $enrollment->roll_no }}">
                        @error('roll_no') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active" {{ $enrollment->status=='active'?'selected':'' }}>Active</option>
                            <option value="inactive" {{ $enrollment->status=='inactive'?'selected':'' }}>Inactive</option>
                        </select>
                        @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="remarks" class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="{{ $enrollment->remarks }}">
                        @error('remarks') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Enrollment</button>
            </form>
        </div>
    </div>
@stop
