@extends('adminlte::page')

@section('title', $route->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-route mr-2"></i>{{ $route->name }}</h1>
        <div>
            <a href="{{ route('transport.routes.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> All Routes
            </a>
            @can('manage transport')
            <a href="{{ route('transport.routes.edit', $route) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-edit mr-1"></i> Edit Route
            </a>
            @endcan
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    @if($errors->any())
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
    @endif

    <div class="row">
        <div class="col-md-3 col-6">
            <div class="info-box-custom">
                <div class="ib-icon" style="background:rgba(37,99,235,.12);color:#2563eb"><i class="fas fa-bus"></i></div>
                <div class="ib-body">
                    <span class="ib-label">Bus</span>
                    <span class="ib-value" style="font-size:1.1rem">{{ $route->bus->plate_number ?? '— none —' }}</span>
                    <span class="ib-sub">{{ $route->bus->driver ? 'Driver: ' . $route->bus->driver->first_name . ' ' . $route->bus->driver->last_name : 'No driver set' }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="info-box-custom">
                <div class="ib-icon" style="background:rgba(22,163,74,.12);color:#16a34a"><i class="fas fa-wallet"></i></div>
                <div class="ib-body">
                    <span class="ib-label">Monthly Fee</span>
                    <span class="ib-value">{{ number_format($route->monthly_fee) }}</span>
                    <span class="ib-sub">TZS per student</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="info-box-custom">
                <div class="ib-icon" style="background:rgba(124,58,237,.12);color:#7c3aed"><i class="fas fa-user-graduate"></i></div>
                <div class="ib-body">
                    <span class="ib-label">Students</span>
                    <span class="ib-value">{{ $route->activeAssignments->count() }}</span>
                    <span class="ib-sub">Currently assigned</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="info-box-custom">
                <div class="ib-icon" style="background:rgba(13,148,136,.12);color:#0d9488"><i class="fas fa-map-marker-alt"></i></div>
                <div class="ib-body">
                    <span class="ib-label">Stops</span>
                    <span class="ib-value">{{ $route->stops->count() }}</span>
                    <span class="ib-sub">Pickup / dropoff points</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Stops --}}
        <div class="col-lg-5">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-map-marker-alt mr-2"></i>Stops</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm mb-0">
                        <thead class="bg-light">
                            <tr><th>#</th><th>Stop</th><th>Pickup</th><th>Dropoff</th>@can('manage transport')<th></th>@endcan</tr>
                        </thead>
                        <tbody>
                            @forelse($route->stops as $stop)
                            <tr>
                                <td>{{ $stop->stop_order }}</td>
                                <td>{{ $stop->name }}</td>
                                <td>{{ $stop->pickup_time ? \Carbon\Carbon::parse($stop->pickup_time)->format('H:i') : '—' }}</td>
                                <td>{{ $stop->dropoff_time ? \Carbon\Carbon::parse($stop->dropoff_time)->format('H:i') : '—' }}</td>
                                @can('manage transport')
                                <td class="text-right">
                                    <form action="{{ route('transport.stops.destroy', $stop) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this stop?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                                @endcan
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No stops yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @can('manage transport')
                <div class="card-footer">
                    <form method="POST" action="{{ route('transport.routes.stops.store', $route) }}" class="form-row align-items-end">
                        @csrf
                        <div class="form-group col-4 mb-0">
                            <label class="small mb-1">Stop name</label>
                            <input type="text" name="name" class="form-control form-control-sm" required placeholder="e.g. Mbezi Beach">
                        </div>
                        <div class="form-group col-3 mb-0">
                            <label class="small mb-1">Pickup</label>
                            <input type="time" name="pickup_time" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-3 mb-0">
                            <label class="small mb-1">Dropoff</label>
                            <input type="time" name="dropoff_time" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-2 mb-0">
                            <button type="submit" class="btn btn-sm btn-primary btn-block"><i class="fas fa-plus"></i> Add</button>
                        </div>
                    </form>
                </div>
                @endcan
            </div>

            @can('manage transport')
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-2"></i>Generate Monthly Fees</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transport.routes.generate-fees', $route) }}" class="form-inline">
                        @csrf
                        <select name="month" class="form-control form-control-sm mr-2">
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ $m === now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="year" value="{{ now()->year }}" class="form-control form-control-sm mr-2" style="width:90px">
                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-bolt mr-1"></i> Generate for all assigned students</button>
                    </form>
                    <small class="text-muted d-block mt-2">Creates a fee record ({{ number_format($route->monthly_fee) }} TZS) for every actively-assigned student who doesn't already have one for that month — safe to click again, it won't duplicate.</small>
                </div>
            </div>
            @endcan
        </div>

        {{-- Students --}}
        <div class="col-lg-7">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-user-graduate mr-2"></i>Assigned Students</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-light">
                            <tr><th>Student</th><th>Class</th><th>Stop</th>@can('manage transport')<th></th>@endcan</tr>
                        </thead>
                        <tbody>
                            @forelse($route->activeAssignments as $assignment)
                            <tr>
                                <td>{{ trim(($assignment->student->first_name ?? '') . ' ' . ($assignment->student->last_name ?? '')) }}</td>
                                <td>{{ $assignment->student->schoolClass->name ?? '—' }}</td>
                                <td>{{ $assignment->stop->name ?? '—' }}</td>
                                @can('manage transport')
                                <td class="text-right">
                                    <form action="{{ route('transport.assignments.unassign', $assignment) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this student from the route?')">
                                        @csrf
                                        <button class="btn btn-xs btn-outline-danger"><i class="fas fa-times mr-1"></i>Remove</button>
                                    </form>
                                </td>
                                @endcan
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No students assigned to this route yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @can('manage transport')
                <div class="card-footer">
                    <form method="POST" action="{{ route('transport.routes.assign', $route) }}" class="form-row align-items-end">
                        @csrf
                        <div class="form-group col-5 mb-0">
                            <label class="small mb-1">Add student</label>
                            <select name="student_id" class="form-control form-control-sm" required>
                                <option value="">— Select student —</option>
                                @foreach($unassignedStudents as $s)
                                    <option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }} ({{ $s->schoolClass->name ?? 'no class' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-4 mb-0">
                            <label class="small mb-1">Stop</label>
                            <select name="stop_id" class="form-control form-control-sm">
                                <option value="">— Not set —</option>
                                @foreach($route->stops as $stop)
                                    <option value="{{ $stop->id }}">{{ $stop->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-3 mb-0">
                            <button type="submit" class="btn btn-sm btn-primary btn-block"><i class="fas fa-plus"></i> Assign</button>
                        </div>
                    </form>
                    @if($unassignedStudents->isEmpty())
                        <small class="text-muted">All students already have a transport assignment. Remove them from their current route first to reassign.</small>
                    @endif
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
@include('partials.dashboard-widgets-css')
@stop
