@extends('adminlte::page')

@section('title', 'Edit Role')

@section('content_header')
    <h1>Edit Role</h1>
    <a href="{{ route('roles.index') }}" class="btn btn-secondary float-right">Back</a>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Role Name</label>
                <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
            </div>

            <div class="form-group mt-2">
                <label>Assign Permissions</label>
                <div class="row">
                    @foreach($permissions as $permission)
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input" id="perm-{{ $permission->id }}"
                                    {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Update Role</button>
        </form>
    </div>
</div>
@stop
