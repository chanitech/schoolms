@extends('adminlte::page')

@section('title', 'Create Role')

@section('content_header')
    <h1>Create Role</h1>
    <a href="{{ route('roles.index') }}" class="btn btn-secondary float-right">Back</a>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Role Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group mt-2">
                <label>Assign Permissions</label>
                <div class="row">
                    @foreach($permissions as $permission)
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input" id="perm-{{ $permission->id }}">
                                <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-success mt-3">Create Role</button>
        </form>
    </div>
</div>
@stop
