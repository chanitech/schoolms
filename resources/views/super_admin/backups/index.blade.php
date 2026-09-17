@extends('adminlte::page')

@section('title', 'Backups — Super Admin')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0"><i class="fas fa-database mr-2"></i>Database Backups</h1>
        <form action="{{ route('super.backups.create') }}" method="POST" onsubmit="return confirm('Create a new full-database backup now? This may take a minute.');">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Create Backup Now
            </button>
        </form>
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="callout callout-warning py-2">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        Each backup contains <strong>every school's data</strong> in one file — this page is intentionally
        restricted to super admins only, and is not exposed anywhere in a school's own admin area.
    </div>

    <div class="card card-outline card-primary">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Size</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($files as $file)
                        <tr>
                            <td><i class="fas fa-file-archive mr-1 text-muted"></i>{{ $file['name'] }}</td>
                            <td>{{ number_format($file['size'] / 1048576, 2) }} MB</td>
                            <td>{{ \Illuminate\Support\Carbon::createFromTimestamp($file['date'])->format('d M Y, H:i') }}</td>
                            <td class="text-right">
                                <a href="{{ route('super.backups.download', $file['name']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download mr-1"></i>Download
                                </a>
                                <form action="{{ route('super.backups.destroy', $file['name']) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this backup permanently? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No backups yet — click "Create Backup Now" to make the first one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
