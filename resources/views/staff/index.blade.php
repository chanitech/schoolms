@extends('adminlte::page')

@section('title', 'Staff List')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-users"></i> Staff List</h1>
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
        <h3 class="card-title"><i class="fas fa-list"></i> Staff Records</h3>
        @can('create staff')
        <a href="{{ route('staff.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus"></i> Add Staff
        </a>
        @endcan
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Role</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staffs as $i => $staff)
                    <tr>
                        <td>{{ $staffs->firstItem() + $i }}</td>
                        <td>{{ $staff->name }}</td>
                        <td>{{ $staff->email }}</td>
                        <td>{{ $staff->department?->name ?? '-' }}</td>
                        <td>{{ $staff->position ?? '-' }}</td>
                        <td>{{ $staff->roles->pluck('name')->join(', ') }}</td>
                        <td>
                            @can('edit staff')
                                <a href="{{ route('staff.edit', $staff->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            @can('delete staff')
                                <form action="{{ route('staff.destroy', $staff->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete staff?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No staff found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $staffs->links('pagination::bootstrap-5') }}
    </div>
</div>
@stop
