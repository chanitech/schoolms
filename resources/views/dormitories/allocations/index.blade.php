@extends('adminlte::page')

@section('title', 'Bed Allocations')

@section('content_header')
    <h1><i class="fas fa-user-check"></i> Bed Allocations</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Allocation History</h3>
        <div class="card-tools">
            <a href="{{ route('dormitories.allocations.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Allocation
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Dormitory</th>
                    <th>Room</th>
                    <th>Bed</th>
                    <th>Allocation Date</th>
                    <th>Status</th>
                    <th>Allocated By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allocations as $allocation)
                <tr>
                    <td>{{ $allocation->student->full_name ?? 'N/A' }} ({{ $allocation->student->admission_no ?? 'N/A' }})</td>
                    <td>{{ $allocation->bed->room->dormitory->name ?? 'N/A' }}</td>
                    <td>{{ $allocation->bed->room->room_number ?? 'N/A' }}</td>
                    <td>{{ $allocation->bed->bed_number ?? 'N/A' }} ({{ ucfirst($allocation->bed->bed_type ?? '') }})</td>
                    <td>{{ $allocation->allocation_date->format('d M Y') }}</td>
                    <td>
                        <span class="badge badge-{{ $allocation->status == 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($allocation->status) }}
                        </span>
                     </td>
                    <td>{{ $allocation->allocator->name ?? 'System' }}</td>
                    <td>
                        @if($allocation->status == 'active')
                        <form action="{{ route('dormitories.allocations.delete', $allocation) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Deallocate this bed?')">
                                <i class="fas fa-bed"></i> Deallocate
                            </button>
                        </form>
                        @endif
                     </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center">No allocations found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $allocations->links() }}
    </div>
</div>
@stop