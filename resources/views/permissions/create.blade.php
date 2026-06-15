@extends('adminlte::page')

@section('title', 'Create Permission')

@section('content_header')
    <h1>Create Permission</h1>
    <a href="{{ route('settings.permissions.index') }}" class="btn btn-secondary float-right">Back</a>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('settings.permissions.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Permission Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Permission</button>
        </form>
    </div>
</div>
@stop