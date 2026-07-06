@extends('adminlte::page')

@section('title', 'Pending Procurement Approvals')

@section('content_header')
    <h1 class="m-0 text-dark">Pending Procurement Approvals</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Estimated Cost</th>
                            <th>Requested By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr>
                            <td>{{ $request->item }}{{ $request->threshold_flag ? ' ⚠️' : '' }}</td>
                            <td>{{ $request->quantity }}</td>
                            <td>{{ number_format($request->estimated_cost, 2) }}</td>
                            <td>{{ $request->requestedBy->name ?? '—' }}</td>
                            <td>
                                <form action="{{ route('treasurer.procurement.approve', $request) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button>
                                </form>
                                <form action="{{ route('treasurer.procurement.reject', $request) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Reject</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Nothing pending approval.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
