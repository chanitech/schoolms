@extends('adminlte::page')

@section('title', 'Edit Route')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-route mr-2"></i>Edit Route — {{ $route->name }}</h1>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <div class="card card-outline card-primary shadow-sm">
        <form method="POST" action="{{ route('transport.routes.update', $route) }}">
            @csrf @method('PUT')
            <div class="card-body">
                @include('transport.routes._form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Update Route</button>
                <a href="{{ route('transport.routes.show', $route) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@stop
