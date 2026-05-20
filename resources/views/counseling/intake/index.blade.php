@extends('adminlte::page')

@section('title', 'Counseling Intake Forms')

@section('content_header')
    <h1><i class="fas fa-notes-medical"></i> Counseling Intake Forms</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title mb-0">All Counseling Intake Forms</h3>
        </div>
        <div>
            <a href="{{ route('counseling.intake.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle"></i> New Intake Form
            </a>
        </div>
    </div>

    <div class="card-body">
        {{-- Search Form --}}
        <form method="GET" action="{{ route('counseling.intake.index') }}" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by student name or ID...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary btn-block">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Stream</th>
                        <th>Education Program</th>
                        <th>General Performance</th>
                        <th>Date Added</th>
                        <th style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($forms as $index => $form)
                        <tr>
                            <td>{{ $forms->firstItem() + $index }}</td>
                            <td>{{ $form->student->first_name ?? '' }} {{ $form->student->last_name ?? '' }}</td>
                            <td>{{ $form->gender ?? '-' }}</td>
                            <td>{{ $form->age ?? '-' }}</td>
                            <td>{{ $form->stream ?? '-' }}</td>
                            <td>{{ $form->education_program ?? '-' }}</td>
                            <td>{{ $form->g_performance ?? '-' }}</td>
                            <td>{{ $form->created_at->format('d M, Y') }}</td>
                            <td>
                                <a href="{{ route('counseling.intake.show', $form->id) }}" class="btn btn-info btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('counseling.intake.edit', $form->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('counseling.intake.destroy', $form->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this form?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No counseling intake forms found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $forms->withQueryString()->links() }}
        </div>
    </div>
</div>
@stop
