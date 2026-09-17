@extends('adminlte::page')

@section('title', 'Teacher Assignments')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-user-tie mr-2"></i>Teacher Assignments</h1>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')

    <div class="callout callout-info py-2">
        <i class="fas fa-info-circle mr-1"></i>
        Assign which teacher is responsible for each subject in each class. Changing this here updates
        the same assignment used by marks entry, timetables, and topic coverage.
    </div>

    @forelse($subjects as $subject)
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                {{ $subject->name }}
                <span class="badge badge-light border ml-1">{{ $subject->department->name ?? 'No department' }}</span>
            </h3>
        </div>
        <div class="card-body table-responsive p-0">
            @if($subject->classes->isEmpty())
                <p class="text-muted text-center py-3 mb-0">Not assigned to any class yet.</p>
            @else
                <table class="table table-hover table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Class</th>
                            <th style="width: 320px">Teacher</th>
                            <th class="text-right" style="width: 100px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subject->classes as $class)
                        @php($formId = 'teacher-assign-' . $subject->id . '-' . $class->id)
                        <tr>
                            <td>{{ $class->name }}</td>
                            <td>
                                <form id="{{ $formId }}" action="{{ route('subjects.update-teacher-assignment', [$subject->id, $class->id]) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                </form>
                                <select name="teacher_id" form="{{ $formId }}" class="form-control form-control-sm">
                                    <option value="">— Not assigned —</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" @selected($class->pivot->teacher_id == $teacher->id)>
                                            {{ $teacher->first_name }} {{ $teacher->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-right">
                                <button type="submit" form="{{ $formId }}" class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i>Save</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">
        <i class="fas fa-user-tie fa-2x mb-2"></i>
        <p>No subjects found. Create a subject first.</p>
    </div>
    @endforelse
</div>
@stop
