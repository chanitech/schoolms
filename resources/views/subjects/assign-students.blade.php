@extends('adminlte::page')

@section('title', 'Assign Students')

@section('content_header')
    <h1>Assign Students to Subject: {{ $subject->name }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('subjects.updateAssignedStudents', $subject->id) }}" method="POST">
            @csrf
            @method('PUT')

            @foreach($classes as $class)
                <div class="card card-outline card-primary mb-2">
                    <a class="d-block w-100" data-toggle="collapse" href="#class-{{ $class->id }}" role="button" aria-expanded="false" aria-controls="class-{{ $class->id }}">
                        <div class="card-header">
                            <h3 class="card-title">
                                {{ $class->name }}
                            </h3>
                            <div class="card-tools">
                                <i class="fas fa-angle-down"></i>
                            </div>
                        </div>
                    </a>
                    <div id="class-{{ $class->id }}" class="collapse">
                        <div class="card-body p-0">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th class="text-center">Assigned</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $classStudents = $students->where('class_id', $class->id);
                                    @endphp
                                    @foreach($classStudents as $student)
                                        @php
                                            $assigned = isset($pivotData[$student->id]) && $pivotData[$student->id] == 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                            <td class="text-center">
                                                <input type="checkbox" name="students[]" value="{{ $student->id }}" {{ $assigned ? 'checked' : '' }}>
                                                @if($assigned)
                                                    <i class="fas fa-check text-success ml-1"></i>
                                                @else
                                                    <i class="fas fa-times text-danger ml-1"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

            <button class="btn btn-primary" type="submit">Update Assignments</button>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    // Toggle the angle icon on collapse
    document.querySelectorAll('.card a[data-toggle="collapse"]').forEach(el => {
        el.addEventListener('click', function() {
            const icon = this.querySelector('i.fas');
            icon.classList.toggle('fa-angle-down');
            icon.classList.toggle('fa-angle-up');
        });
    });
</script>
@stop
