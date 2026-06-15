@extends('adminlte::page')

@section('title', 'Bank Statements')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-university"></i> Bank Statements</h1>
        <a href="{{ route('treasurer.bank-statements.create') }}" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload Statement
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow">
        <div class="card-body">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="bank-statements-table">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Staff</th>
                            <th>Month</th>
                            <th>File Name</th>
                            <th>Uploaded By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statements as $stmt)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ optional($stmt->staff)->name ?? 'N/A' }}</td>
                                <td>{{ optional($stmt->statement_month)->format('M Y') ?? 'N/A' }}</td>
                                <td>{{ $stmt->original_name }}</td>
                                <td>{{ optional($stmt->uploader)->name ?? 'System' }}</td>
                                <td>
                                    <a href="{{ Storage::url($stmt->file_path) }}" class="btn btn-sm btn-info" target="_blank">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <form action="{{ route('treasurer.bank-statements.destroy', $stmt) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this statement?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No bank statements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#bank-statements-table').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            pageLength: 25,
        });
    });
</script>
@stop