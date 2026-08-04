@extends('adminlte::page')

@section('title', 'Buses')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-bus mr-2"></i>School Buses</h1>
        @can('manage transport')
        <a href="{{ route('transport.buses.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Add Bus
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
                        <th>Plate Number</th>
                        <th>Name</th>
                        <th>Capacity</th>
                        <th>Driver</th>
                        <th>Routes</th>
                        <th>Status</th>
                        @can('manage transport')<th></th>@endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($buses as $bus)
                    @php $badge = ['active' => 'success', 'maintenance' => 'warning', 'inactive' => 'secondary'][$bus->status]; @endphp
                    <tr>
                        <td class="font-weight-bold">{{ $bus->plate_number }}</td>
                        <td>{{ $bus->name ?: '—' }}</td>
                        <td>{{ $bus->capacity }}</td>
                        <td>{{ $bus->driver ? trim($bus->driver->first_name . ' ' . $bus->driver->last_name) : '—' }}</td>
                        <td>{{ $bus->routes->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td><span class="badge badge-{{ $badge }}">{{ ucfirst($bus->status) }}</span></td>
                        @can('manage transport')
                        <td class="text-right">
                            <a href="{{ route('transport.buses.edit', $bus) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('transport.buses.destroy', $bus) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this bus?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                        @endcan
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No buses added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
