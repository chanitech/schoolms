@extends('adminlte::page')

@section('title', 'Dormitories')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-building"></i> Dormitories</h1>
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
        <h3 class="card-title"><i class="fas fa-list"></i> Dormitory Records</h3>
        <a href="{{ route('dormitories.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Dormitory
        </a>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Capacity</th>
                    <th>Gender</th>
                    <th>Dorm Master</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dormitories as $dorm)
                    <tr>
                        <td>{{ $loop->iteration + ($dormitories->currentPage()-1)*$dormitories->perPage() }}</td>
                        <td>{{ $dorm->name }}</td>
                        <td>{{ $dorm->capacity }}</td>
                        <td>{{ ucfirst($dorm->gender) }}</td>
                        <td>
                            @if($dorm->dormMaster)
                                {{ $dorm->dormMaster->first_name }} {{ $dorm->dormMaster->last_name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('dormitories.edit', $dorm->id) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('dormitories.destroy', $dorm->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this dormitory?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No dormitories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $dormitories->links('pagination::bootstrap-5') }}
    </div>
</div>
@stop
