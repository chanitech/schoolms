@extends('adminlte::page')

@section('title', 'Staff List')

@section('content_header')
    <h1 class="text-success">Staff List</h1>
@stop

@section('content')
<div class="container-fluid">
    <a href="{{ route('staff.create') }}" class="btn btn-success mb-3">Add New Staff</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffs as $i => $staff)
                    <tr>
                        <td>{{ $staffs->firstItem() + $i }}</td>
                        <td>{{ $staff->name }}</td>
                        <td>{{ $staff->email }}</td>
                        <td>{{ $staff->department->name ?? '-' }}</td>
                        <td>{{ $staff->position ?? '-' }}</td>
                        <!-- ✅ Display Spatie Role -->
                        <td>{{ $staff->role_name }}</td>
                        <td>
                            <a href="{{ route('staff.edit', $staff->id) }}" class="btn btn-primary btn-sm">Edit</a>
                            <form action="{{ route('staff.destroy', $staff->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete staff?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-2">
                {{ $staffs->links() }}
            </div>
        </div>
    </div>
</div>
@stop
