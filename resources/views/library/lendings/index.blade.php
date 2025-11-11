@extends('adminlte::page')

@section('title', 'Library Lendings')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-book-reader"></i> Library Lendings</h1>
@stop

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Lending Summary --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $lendingSummary['total'] }}</h3>
                <p>Total Lendings</p>
            </div>
            <div class="icon"><i class="fas fa-book"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $lendingSummary['students'] }}</h3>
                <p>Student Borrowers</p>
            </div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $lendingSummary['staff'] }}</h3>
                <p>Staff Borrowers</p>
            </div>
            <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $lendingSummary['books_lent'] }}</h3>
                <p>Books Lent</p>
            </div>
            <div class="icon"><i class="fas fa-book-open"></i></div>
        </div>
    </div>
</div>

{{-- Create button --}}
<div class="mb-3 text-end">
    <a href="{{ route('library.lendings.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Lending
    </a>
</div>

{{-- Lending List --}}
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-list"></i> Lending Records</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Book Title</th>
                    <th>Borrower</th>
                    <th>Borrower Type</th>
                    <th>Quantity Lent</th>
                    <th>Quantity Returned</th>
                    <th>Lend Date</th>
                    <th>Return Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lendings as $lending)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $lending->book->title ?? 'N/A' }}</td>
                    
                    {{-- Borrower --}}
                    <td>
                        @if($lending->borrower)
                            {{ $lending->borrower->first_name ?? '' }}
                            {{ $lending->borrower->middle_name ?? '' }}
                            {{ $lending->borrower->last_name ?? '' }}
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif
                    </td>

                    <td>
                        <span class="badge bg-{{ $lending->borrower_type === \App\Models\Student::class ? 'success' : 'info' }}">
                            {{ class_basename($lending->borrower_type) }}
                        </span>
                    </td>

                    <td>{{ $lending->quantity }}</td>
                    <td>{{ $lending->returned ? $lending->quantity : 0 }}</td>

                    <td>{{ \Carbon\Carbon::parse($lending->lend_date)->format('d M Y') }}</td>
                    <td>
                        @if($lending->returned)
                            {{ \Carbon\Carbon::parse($lending->returned_at)->format('d M Y') }}
                        @else
                            {{ $lending->return_date ? \Carbon\Carbon::parse($lending->return_date)->format('d M Y') : '-' }}
                        @endif
                    </td>
                    <td>
                        @if(!$lending->returned)
                        <form action="{{ route('library.lendings.return', $lending) }}" method="POST" onsubmit="return confirm('Confirm return of this book?');">
                            @csrf
                            <button class="btn btn-success btn-sm"><i class="fas fa-undo"></i> Return</button>
                        </form>
                        @else
                            <span class="text-success"><i class="fas fa-check"></i> Returned</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-3">
                        <i class="fas fa-inbox"></i> No lending records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
<div class="mt-3">
    {{ $lendings->links() }}
</div>
@stop
