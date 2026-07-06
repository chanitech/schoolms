@extends('adminlte::page')
@section('title', 'Subjects')
@section('content_header')
<h1>Subjects</h1>
@can('create subjects')
<a href="{{ route('subjects.create') }}" class="btn btn-primary">Add New Subject</a>
@endcan
@endsection
@section('content')
<div class="card"> <div class="card-body"> {{-- ================= Search & Filter ================= --}} <form method="GET" class="mb-3 row g-2 align-items-center"> <div class="col-auto"> <input type="text" name="search" value="{{ request('search') }}" placeholder="Search subjects..." class="form-control"> </div> <div class="col-auto"> <select name="department_id" class="form-control"> <option value="">-- All Departments --</option> @foreach($departments as $dept) <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}> {{ $dept->name }} </option> @endforeach </select> </div> <div class="col-auto"> <button class="btn btn-info">Filter</button> </div> </form>
    {{-- ================= Subjects Table ================= --}}
    <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Code</th>
                <th>Type</th>
                <th>Department</th>
                <th>Classes & Teachers</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subjects as $i => $subject)
            <tr>
                <td>{{ $subjects->firstItem() + $i }}</td>
                <td>{{ $subject->name }}</td>
                <td>{{ $subject->code ?? '—' }}</td>
                <td>{{ ucfirst($subject->type) }}</td>
                <td>{{ $subject->department?->name ?? '—' }}</td>
                <td>
                    @if($subject->classes->isNotEmpty())
                        @foreach($subject->classes as $class)
                            @php
                                $teacherId = $class->pivot->teacher_id;
                                $teacher = $teachers[$teacherId] ?? null;
                            @endphp
                            <span class="badge bg-success">{{ $class->name }}</span>
                            <span class="text-muted">→ {{ $teacher ? $teacher->first_name.' '.$teacher->last_name : '—' }}</span><br>
                        @endforeach
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @can('edit subjects')
                    <a href="{{ route('subjects.edit', $subject->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    @endcan
                    @can('view subject assignments')
                    <a href="{{ route('subjects.assign_students', $subject->id) }}" class="btn btn-sm btn-info">Assign Students</a>
                    @endcan
                    @can('delete subjects')
                    <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-muted">No subjects found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-2">
        {{ $subjects->links() }}
    </div>
</div>
</div> @endsection