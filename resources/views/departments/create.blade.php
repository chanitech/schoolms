@extends('adminlte::page')

@section('title', 'Add Department')

@section('content_header')
<h1 class="text-center text-success">Add Department</h1>
@stop

@section('content')
<div class="container-fluid">
    <form action="{{ route('departments.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <button class="btn btn-success">Save Department</button>
    </form>
</div>
@stop
