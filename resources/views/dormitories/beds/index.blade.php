@extends('adminlte::page')

@section('title', 'Beds - Room ' . $room->room_number)

@section('content_header')
    <h1><i class="fas fa-bed"></i> Beds in Room {{ $room->room_number }} ({{ $room->dormitory->name }})</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-bed"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Beds</span>
                <span class="info-box-number">{{ $room->beds->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Available Beds</span>
                <span class="info-box-number">{{ $room->available_beds_count }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Bed List</h3>
        <div class="card-tools">
            <a href="{{ route('dormitories.beds.create', $room->id) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Bed
            </a>
            <a href="{{ route('dormitories.rooms', $room->dormitory_id) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Rooms
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Bed Number</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Current Student</th>
                    <th>Features</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($beds as $bed)
                <tr>
                    <td><strong>{{ $bed->bed_number }}</strong></td>
                    <td>{{ ucfirst(str_replace('_', ' ', $bed->bed_type)) }}</td>
                    <td>
                        <span class="badge 
                            {{ $bed->status == 'available' ? 'badge-success' : '' }}
                            {{ $bed->status == 'occupied' ? 'badge-danger' : '' }}
                            {{ $bed->status == 'maintenance' ? 'badge-warning' : '' }}
                            {{ $bed->status == 'reserved' ? 'badge-info' : '' }}">
                            {{ ucfirst($bed->status) }}
                        </span>
                    </td>
                    <td>
                        @if($bed->current_student_id)
                            <a href="{{ route('students.show', $bed->current_student_id) }}">
                                {{ $bed->currentStudent->full_name ?? 'N/A' }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $bed->features ?? '-' }}</td>
                    <td>
                        <a href="{{ route('dormitories.beds.edit', $bed) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if($bed->status != 'occupied')
                        <form action="{{ route('dormitories.beds.delete', $bed) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center">No beds found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{-- Modern Pagination with Filter Preservation --}}
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-4 pt-2">
            <div class="text-muted mb-2 mb-sm-0">
                <small>
                    <i class="fas fa-table mr-1"></i>
                    Showing <strong>{{ $beds->firstItem() ?? 0 }}</strong> to <strong>{{ $beds->lastItem() ?? 0 }}</strong> of <strong>{{ $beds->total() }}</strong> results
                </small>
            </div>
            <div>
                @if ($beds->hasPages())
                    <nav aria-label="Pagination Navigation">
                        <ul class="pagination pagination-sm m-0">
                            {{-- Previous Page Link --}}
                            @if ($beds->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $beds->appends(request()->query())->previousPageUrl() }}" rel="prev">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @php
                                $start = max(1, $beds->currentPage() - 2);
                                $end = min($start + 4, $beds->lastPage());
                                if ($end - $start < 4 && $beds->lastPage() > 5) {
                                    $start = max(1, $beds->lastPage() - 4);
                                    $end = $beds->lastPage();
                                }
                            @endphp

                            @if ($start > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $beds->appends(request()->query())->url(1) }}">1</a>
                                </li>
                                @if ($start > 2)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $beds->currentPage())
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link">{{ $i }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $beds->appends(request()->query())->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endif
                            @endfor

                            @if ($end < $beds->lastPage())
                                @if ($end < $beds->lastPage() - 1)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ $beds->appends(request()->query())->url($beds->lastPage()) }}">{{ $beds->lastPage() }}</a>
                                </li>
                            @endif

                            {{-- Next Page Link --}}
                            @if ($beds->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $beds->appends(request()->query())->nextPageUrl() }}" rel="next">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
        </div>
    </div>
</div>
@stop