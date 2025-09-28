@extends('adminlte::page')

@section('title', 'Divisions')

@section('content_header')
    <h1>Divisions</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <a href="{{ route('divisions.create') }}" class="btn btn-primary mb-3">Add Division</a>

        @if($divisions->count())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Min Points</th>
                    <th>Max Points</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($divisions as $division)
                <tr>
                    <td>{{ $division->name }}</td>
                    <td>{{ $division->min_points }}</td>
                    <td>{{ $division->max_points }}</td>
                    <td>{{ $division->description }}</td>
                    <td>
                        <a href="{{ route('divisions.edit', $division) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('divisions.destroy', $division) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this division?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $divisions->links() }}
        @else
            <p>No divisions found.</p>
        @endif
    </div>
</div>
@stop
