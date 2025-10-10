@extends('adminlte::page')

@section('title', 'Events Calendar')

@section('content_header')
    <h1>Events Calendar</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- Top Buttons --}}
        <div class="d-flex justify-content-between mb-3">
            <div>
                <a href="{{ route('events.create') }}" class="btn btn-primary">Add Event</a>
                <a href="{{ route('events.calendar') }}" class="btn btn-success">View Calendar</a>
            </div>
            <div>
                <a href="{{ route('events.export.excel') }}" class="btn btn-outline-success">Export Excel</a>
                <a href="{{ route('events.export.pdf') }}" class="btn btn-outline-danger">Export PDF</a>
            </div>
        </div>

        {{-- Event Summary --}}
        <div class="row mb-3">
            @foreach([
                'total' => ['label' => 'Total Events', 'color' => 'info', 'icon' => 'fas fa-calendar-alt'],
                'academic' => ['label' => 'Academic', 'color' => 'success', 'icon' => 'fas fa-book'],
                'sport' => ['label' => 'Sports', 'color' => 'warning', 'icon' => 'fas fa-football-ball'],
                'cultural' => ['label' => 'Cultural', 'color' => 'primary', 'icon' => 'fas fa-theater-masks'],
                'holiday' => ['label' => 'Holidays', 'color' => 'danger', 'icon' => 'fas fa-umbrella-beach'],
                'other' => ['label' => 'Other', 'color' => 'secondary', 'icon' => 'fas fa-star']
            ] as $key => $data)
                <div class="col-md-2">
                    <div class="small-box bg-{{ $data['color'] }}">
                        <div class="inner">
                            <h3>{{ $summary[$key] ?? 0 }}</h3>
                            <p>{{ $data['label'] }}</p>
                        </div>
                        <div class="icon">
                            <i class="{{ $data['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('events.index') }}" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <select name="department_id" class="form-control">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        @foreach(['academic','sport','cultural','holiday','other'] as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Start Date">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="End Date">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary btn-block">Filter</button>
                </div>
            </div>
        </form>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Events Table --}}
        @if($events->count())
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                        <tr>
                            <td>{{ $event->title }}</td>
                            <td>{{ $event->department?->name ?? 'All' }}</td>
                            <td>{{ ucfirst($event->type) }}</td>
                            <td>{{ $event->start_date->format('Y-m-d') }}</td>
                            <td>{{ $event->end_date->format('Y-m-d') }}</td>
                            <td>{{ $event->description }}</td>
                            <td>
                                <a href="{{ route('events.edit', $event) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('events.destroy', $event) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this event?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $events->links() }}
        @else
            <p>No events found.</p>
        @endif

    </div>
</div>
@stop
