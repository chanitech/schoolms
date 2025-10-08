@extends('adminlte::page')

@section('title', 'Student Results')

@section('content_header')
    <h1 class="text-center text-success">Student Result Summary</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Flash Messages --}}
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Student Information --}}
    <div class="card shadow mb-4 border-success">
        <div class="card-body d-flex align-items-center">
            {{-- Student Photo --}}
            <div class="me-4">
                <img src="{{ $student->photo ? asset('storage/'.$student->photo) : asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}"
                     alt="Student Photo"
                     class="img-thumbnail rounded-circle border border-success"
                     style="width:120px; height:120px; object-fit:cover;">
            </div>

            {{-- Student Details --}}
            <div>
                <h4 class="mb-1 text-uppercase fw-bold">{{ $student->name }}</h4>
                <p class="mb-1"><strong>Admission No:</strong> {{ $student->admission_no ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Class:</strong> {{ $student->class->name ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Exam:</strong> {{ $exam->name ?? 'All Exams' }}</p>
            </div>
        </div>
    </div>

    {{-- Exam Selector --}}
    <form action="{{ route('results.show', $student->id) }}" method="GET" class="mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <select name="exam_id" class="form-control border-success">
                    <option value="">-- Select Exam --</option>
                    @foreach ($exams as $ex)
                        <option value="{{ $ex->id }}" {{ $selected_exam_id == $ex->id ? 'selected' : '' }}>
                            {{ $ex->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-success">
                    <i class="fas fa-search"></i> View Results
                </button>
            </div>
        </div>
    </form>

    {{-- Subject Results --}}
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Subject Results</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>Mark</th>
                        <th>Grade</th>
                        <th>Point</th>
                        <th>Remark</th>
                        <th>Position</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjectsData as $subject)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $subject['subject'] }}</td>
                            <td>{{ number_format($subject['mark'], 2) }}</td>

                            {{-- Grade Badge --}}
                            <td>
                                @php
                                    $badgeClass = match($subject['grade']) {
                                        'A' => 'bg-success',
                                        'B' => 'bg-primary',
                                        'C' => 'bg-warning text-dark',
                                        'D' => 'bg-orange text-dark',
                                        'E', 'F' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} fs-6">{{ $subject['grade'] ?? '-' }}</span>
                            </td>

                            <td>{{ $subject['point'] }}</td>
                            <td>{{ $subject['remark'] }}</td>
                            <td>{{ $subject['subject_position'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No results found for this exam.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-success">
                    <tr>
                        <th colspan="4" class="text-end">Total Points:</th>
                        <th colspan="3">{{ $totalPoints }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Summary Section --}}
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-success">
                <div class="card-body">
                    <h6 class="text-muted">GPA</h6>
                    <h3 class="fw-bold text-success">{{ number_format($result['gpa'], 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center shadow-sm border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Division</h6>
                    <h3 class="fw-bold text-primary">{{ $result['division'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center shadow-sm border-danger">
                <div class="card-body">
                    <h6 class="text-muted">Class Position</h6>
                    <h3 class="fw-bold text-danger">{{ $rank }}<sup>th</sup></h3>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
