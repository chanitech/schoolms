@extends('adminlte::page')

@section('title', 'Interest Inventory')

@section('content_header')
    <h1><i class="fas fa-brain"></i> Interest Inventory</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">All Records</h3>
        <div>
            <a href="{{ route('interest-inventories.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New
            </a>
        </div>
    </div>

    <div class="card-body">
        <form class="mb-3" method="GET" action="{{ route('interest-inventories.index') }}">
            <div class="row g-2">
                <div class="col-md-4">
                    <select name="student_id" class="form-control">
                        <option value="">-- All students --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->first_name }} {{ $student->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>

                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-info w-100"><i class="fas fa-filter"></i> Filter</button>
                </div>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Date</th>
                    <th>Created By</th>
                    <th>Preview</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                <tr>
                    <td>{{ $loop->iteration + ($records->currentPage()-1) * $records->perPage() }}</td>
                    <td>{{ $record->student?->first_name }} {{ $record->student?->last_name }}</td>
                    <td>{{ $record->date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $record->creator?->name ?? '—' }}</td>
                    <td>
                        {{-- show short preview of q1 --}}
                        {{ \Illuminate\Support\Str::limit($record->q1, 60) }}
                    </td>
                    <td>
                        <a href="{{ route('interest-inventories.show', $record->id) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('interest-inventories.edit', $record->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('interest-inventories.destroy', $record->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this record?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        {{-- optional export button if PDF implemented --}}
                        {{-- <a href="{{ route('interest-inventories.export', $record->id) }}" class="btn btn-sm btn-secondary">Export</a> --}}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $records->withQueryString()->links() }}
        </div>
    </div>
</div>
@stop
