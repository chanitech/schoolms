@extends('adminlte::page')

@section('title', 'Enrollments')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-user-graduate"></i> Enrollments</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-list"></i> Enrollment Records</h3>
            <a href="{{ route('enrollments.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Enrollment
            </a>
        </div>

        <div class="card-body table-responsive">
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search enrollments...">
                    <button class="btn btn-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Session</th>
                        <th>Roll No</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                        <tr>
                            <td>{{ $loop->iteration + ($enrollments->currentPage()-1)*$enrollments->perPage() }}</td>

                            <!-- Safe Student Check -->
                            <td>
                                @if($enrollment->student)
                                    <a href="{{ route('students.edit', $enrollment->student->id) }}">
                                        {{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}
                                    </a>
                                @else
                                    <span class="text-muted">Deleted student</span>
                                @endif
                            </td>

                            <!-- Safe Class Check -->
                            <td>{{ $enrollment->class?->name ?? '-' }}</td>

                            <!-- Safe Session Check -->
                            <td>{{ $enrollment->academicSession?->name ?? '-' }}</td>

                            <td>{{ $enrollment->roll_no ?? '-' }}</td>

                            <td>
                                <a href="{{ route('enrollments.edit', $enrollment->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('enrollments.destroy', $enrollment->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this enrollment?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No enrollments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $enrollments->links('pagination::bootstrap-5') }}
        </div>
    </div>
@stop
