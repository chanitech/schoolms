@extends('adminlte::page')

@section('title', 'Edit Permission')

@section('content_header')
    <h1>Edit Permission</h1>
    <a href="{{ route('settings.permissions.index') }}" class="btn btn-secondary float-right">Back</a>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('settings.permissions.update', $permission->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Permission Name</label>
                <input type="text" name="name" id="name" class="form-control" 
                       value="{{ old('name', $permission->name) }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Permission</button>
        </form>
    </div>
</div>
@stop