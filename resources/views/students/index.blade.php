@extends('adminlte::page')

@section('title', 'Students')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-user-graduate"></i> Students</h1>
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
            <h3 class="card-title"><i class="fas fa-list"></i> Student Records</h3>
            <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Student
            </a>
        </div>

        <div class="card-body table-responsive">
            <!-- Search -->
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search students...">
                    <button class="btn btn-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Photo</th>
                        <th>Admission No</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Class</th>
                        <th>Dormitory</th>
                        <th>Session</th>
                        <th>Guardian</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <!-- Photo -->
                            <td class="text-center align-middle">
                                @if($student->photo && Storage::exists('public/'.$student->photo))
                                    <img src="{{ asset('storage/'.$student->photo) }}" alt="Photo" class="img-thumbnail" style="width:50px; height:50px; object-fit:cover;">
                                @else
                                    <span class="text-muted">No photo</span>
                                @endif
                            </td>

                            <!-- Admission No -->
                            <td><span class="badge bg-info">{{ $student->admission_no }}</span></td>

                            <!-- Name -->
                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>

                            <!-- Gender -->
                            <td>{{ ucfirst($student->gender) }}</td>

                            <!-- Class -->
                            <td>{{ $student->class?->name ?? '-' }}</td>

                            <!-- Dormitory -->
                            <td>{{ $student->dormitory?->name ?? '-' }}</td>

                            <!-- Session -->
                            <td>{{ $student->academicSession?->name ?? '-' }}</td>

                            <!-- Guardian -->
                            <td>
                                @if($student->guardian)
                                    <a href="{{ route('guardians.show', $student->guardian->id) }}">
                                        {{ $student->guardian->first_name }} {{ $student->guardian->last_name }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="text-center">
                                <a href="{{ route('students.edit', $student->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this student?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $students->links('pagination::bootstrap-5') }}
        </div>
    </div>
@stop
