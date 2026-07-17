@extends('adminlte::page')

@section('title', 'Students')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-user-graduate"></i> Students</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-list"></i> Student Records</h3>
            <div>
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#importStudentModal">
                    <i class="fas fa-file-excel"></i> Import Excel
                </button>
                <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm ml-2">
                    <i class="fas fa-plus"></i> Add Student
                </a>
            </div>
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
                            <td class="text-center align-middle">
                                @if($student->photo && Storage::exists('public/'.$student->photo))
                                    <img src="{{ asset('storage/'.$student->photo) }}" alt="Photo" class="img-thumbnail" style="width:50px; height:50px; object-fit:cover;">
                                @else
                                    <span class="text-muted">No photo</span>
                                @endif
                            </td>
                            <td><span class="badge bg-info">{{ $student->admission_no }}</span></td>
                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td>{{ ucfirst($student->gender) }}</td>
                            <td>{{ $student->class?->name ?? '-' }}</td>
                            <td>{{ $student->dormitory?->name ?? '-' }}</td>
                            <td>{{ $student->academicSession?->name ?? '-' }}</td>
                            <td>
                                @if($student->guardian)
                                    <a href="{{ route('guardians.show', $student->guardian->id) }}">
                                        {{ $student->guardian->first_name }} {{ $student->guardian->last_name }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- View Button --}}
                                <a href="{{ route('students.show', $student->id) }}" class="btn btn-sm btn-info" title="View Student">
                                    <i class="fas fa-eye"></i>
                                </a>
                                {{-- Edit Button --}}
                                <a href="{{ route('students.edit', $student->id) }}" class="btn btn-sm btn-warning" title="Edit Student">
                                    <i class="fas fa-edit"></i>
                                </a>
                                {{-- Delete Button --}}
                                <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete Student"
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
            {{ $students->links() }}
        </div>
    </div>

    {{-- Import Excel Modal --}}
    <div class="modal fade" id="importStudentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Import Students from Excel</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Excel Format Required:</strong>
                            <ul class="mb-0">
                                <li>Columns: <code>admission_no</code>, <code>first_name</code>, <code>last_name</code>, <code>gender</code>, <code>date_of_birth</code>, <code>guardian_id</code>, <code>class_id</code>, <code>dormitory_id</code>, <code>academic_session_id</code>.</li>
                                <li>Only <strong>admission_no, first_name, last_name</strong> are required – others can be empty.</li>
                                <li>Gender must be <code>male</code> or <code>female</code>.</li>
                                <li>Date of birth format: <code>YYYY-MM-DD</code>.</li>
                            </ul>
                        </div>
                        <div class="form-group">
                            <label for="excel_file">Choose Excel File (.xlsx, .xls)</label>
                            <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls" required>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" name="skip_duplicates" class="form-check-input" id="skip_duplicates" value="1" checked>
                            <label class="form-check-label" for="skip_duplicates">Skip duplicate admission numbers (do not update existing students)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('students.download-template') }}" class="btn btn-secondary">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                        <button type="submit" class="btn btn-primary">Import & Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@endpush