@extends('adminlte::page')

@section('title', 'Timetable Periods')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Timetable Periods</h1>
        <a href="{{ route('timetable-periods.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Period
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <p class="text-muted">
                These are the periods timetable generation and capacity analysis are computed against.
                Teaching periods (not marked "Break") are what count toward available weekly slots —
                inactive periods are ignored entirely.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Duration</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $period)
                        <tr>
                            <td>{{ $period->order_no }}</td>
                            <td>{{ $period->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}</td>
                            <td>{{ $period->duration_minutes }} min</td>
                            <td>
                                @if($period->is_break)
                                    <span class="badge badge-secondary">Break</span>
                                @else
                                    <span class="badge badge-info">Teaching</span>
                                @endif
                            </td>
                            <td>
                                @if($period->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('timetable-periods.edit', $period) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('timetable-periods.destroy', $period) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this period?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No periods configured yet — timetable generation won't be able to place any
                                subjects until at least one active teaching period exists.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
