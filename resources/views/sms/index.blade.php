@extends('adminlte::page')

@section('title', 'Sent Messages')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-history mr-2"></i>Sent SMS</h1>
        <a href="{{ route('sms.compose') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-paper-plane mr-1"></i> Compose New
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <select name="status" class="form-control form-control-sm mr-2">
                    <option value="">All statuses</option>
                    @foreach(['sent' => 'Sent', 'failed' => 'Failed', 'invalid' => 'Invalid number'] as $sk => $sl)
                        <option value="{{ $sk }}" {{ request('status') === $sk ? 'selected' : '' }}>{{ $sl }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('sms.index') }}" class="btn btn-sm btn-outline-secondary ml-1">Reset</a>
            </form>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Recipient</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Sent By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    @php $badge = ['sent' => 'success', 'failed' => 'danger', 'invalid' => 'secondary'][$log->status]; @endphp
                    <tr>
                        <td class="small">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $log->recipient_name ?: '—' }}</td>
                        <td>{{ $log->recipient_phone }}</td>
                        <td class="small" style="max-width:320px">{{ \Illuminate\Support\Str::limit($log->message, 80) }}</td>
                        <td><span class="badge badge-light border">{{ ucfirst(str_replace('_', ' ', $log->category)) }}</span></td>
                        <td>
                            <span class="badge badge-{{ $badge }}">{{ ucfirst($log->status) }}</span>
                            @if($log->error_message)
                                <div class="small text-muted">{{ $log->error_message }}</div>
                            @endif
                        </td>
                        <td class="small">{{ $log->sender->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No messages sent yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $logs->links() }}</div>
    </div>
</div>
@stop
