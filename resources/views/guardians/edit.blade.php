@extends('adminlte::page')

@section('title', 'Guardians')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-user-shield"></i> Guardians</h1>
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
            <h3 class="card-title"><i class="fas fa-list"></i> Guardian Records</h3>
            <a href="{{ route('guardians.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Guardian
            </a>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Relation</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Students</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guardians as $guardian)
                        <tr>
                            <td>
                                <a href="{{ route('guardians.show', $guardian->id) }}">
                                    {{ $guardian->first_name }} {{ $guardian->last_name }}
                                </a>
                            </td>
                            <td>{{ ucfirst($guardian->gender) }}</td>
                            <td>{{ $guardian->relation_to_student }}</td>
                            <td>{{ $guardian->phone }}</td>
                            <td>{{ $guardian->email ?? '-' }}</td>
                            <td>
                                <span class="badge bg-info">{{ $guardian->students->count() }}</span>
                            </td>
                            <td>
                                <a href="{{ route('guardians.edit', $guardian->id) }}" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('guardians.destroy', $guardian->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this guardian?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No guardians found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $guardians->links() }}
        </div>
    </div>
@stop
