@extends('adminlte::page')

@section('title', 'Subjects')

@section('content_header')
    <h1>Subjects</h1>
    <a href="{{ route('subjects.create') }}" class="btn btn-primary">Add New Subject</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        {{-- ================= Search & Filter ================= --}}
        <form method="GET" class="mb-3 row g-2 align-items-center">
            <div class="col-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search subjects..." class="form-control">
            </div>
            <div class="col-auto">
                <select name="department_id" class="form-control">
                    <option value="">-- All Departments --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-info">Filter</button>
            </div>
        </form>

        {{-- ================= Subjects Table ================= --}}
        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Department</th>
                    <th>Classes</th>
                    <th>Teacher</th>
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
                                <span class="badge bg-success">{{ $class->name }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $subject->teacher_name }}</td>
                    <td>
                        <a href="{{ route('subjects.edit', $subject->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <a href="{{ route('subjects.assign_students', $subject->id) }}" class="btn btn-sm btn-info">Assign Students</a>
                        <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-muted">No subjects found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-2">
            {{ $subjects->links() }}
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department');
    const subjectSelect = document.getElementById('subject');

    departmentSelect.addEventListener('change', function() {
        const departmentId = this.value;

        // Clear current subjects
        subjectSelect.innerHTML = '<option value="">All Subjects</option>';

        if (!departmentId) return; // if none selected, stop

        fetch(`/marks/subjects-by-department?department_id=${departmentId}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(subject => {
                    const option = document.createElement('option');
                    option.value = subject.id;
                    option.text = subject.name;
                    subjectSelect.appendChild(option);
                });
            })
            .catch(err => console.error(err));
    });
});
</script>

