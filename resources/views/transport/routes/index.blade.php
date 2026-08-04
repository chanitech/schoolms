@extends('adminlte::page')

@section('title', 'Bus Routes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-route mr-2"></i>Bus Routes</h1>
        @can('manage transport')
        <a href="{{ route('transport.routes.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Add Route
        </a>
        @endcan
    </div>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Route</th>
                        <th>Bus</th>
                        <th>Monthly Fee (TZS)</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($routes as $route)
                    <tr>
                        <td class="font-weight-bold">{{ $route->name }}</td>
                        <td>{{ $route->bus->plate_number ?? '— unassigned —' }}</td>
                        <td>{{ number_format($route->monthly_fee) }}</td>
                        <td>{{ $route->activeAssignments->count() }}</td>
                        <td><span class="badge badge-{{ $route->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($route->status) }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('transport.routes.show', $route) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye mr-1"></i>Manage
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No routes created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
