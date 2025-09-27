@extends('adminlte::page')

@section('title', 'Guardian Details')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-user-shield"></i> Guardian Profile</h1>
@stop

@section('content')
<div class="row">
    <!-- Guardian Profile -->
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <i class="fas fa-user-circle fa-5x text-primary mb-3"></i>
                <h3>{{ $guardian->first_name }} {{ $guardian->last_name }}</h3>
                <p class="text-muted">{{ ucfirst($guardian->gender) }} | {{ $guardian->relation_to_student }}</p>
                <hr>
                <p><i class="fas fa-phone"></i> {{ $guardian->phone }}</p>
                <p><i class="fas fa-envelope"></i> {{ $guardian->email ?? '-' }}</p>
                <p><i class="fas fa-map-marker-alt"></i> {{ $guardian->address ?? '-' }}</p>
                <p><i class="fas fa-briefcase"></i> {{ $guardian->occupation ?? '-' }}</p>
                <p><i class="fas fa-id-card"></i> {{ $guardian->national_id ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Students Linked to Guardian -->
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-user-graduate"></i> Students</h3>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Admission No</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Dormitory</th>
                            <th>Session</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guardian->students as $student)
                            <tr>
                                <td><span class="badge bg-info">{{ $student->admission_no }}</span></td>
                                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                <td>{{ $student->class?->name ?? '-' }}</td>
                                <td>{{ $student->dormitory?->name ?? '-' }}</td>
                                <td>{{ $student->academicSession?->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('students.edit', $student->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('students.destroy', $student->id) }}" 
                                          method="POST" class="d-inline">
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
                                <td colspan="6" class="text-center text-muted">No students linked to this guardian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
