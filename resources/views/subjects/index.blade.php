@extends('adminlte::page')

@section('title', 'Subjects')

@section('content_header')
    <h1>Subjects</h1>
    <a href="{{ route('subjects.create') }}" class="btn btn-primary">Add New Subject</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" class="mb-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search subjects..." class="form-control w-25 d-inline">
            <button class="btn btn-info">Search</button>
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Classes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                <tr>
                    <td>{{ $subject->name }}</td>
                    <td>{{ $subject->code }}</td>
                    <td>{{ ucfirst($subject->type) }}</td>
                    <td>
                        @foreach($subject->classes as $class)
                            <span class="badge bg-success">{{ $class->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('subjects.edit', $subject->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $subjects->links() }}
    </div>
</div>
@endsection
