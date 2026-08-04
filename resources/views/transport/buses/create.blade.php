@extends('adminlte::page')

@section('title', 'Add Bus')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-bus mr-2"></i>Add Bus</h1>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    <div class="card card-outline card-primary shadow-sm">
        <form method="POST" action="{{ route('transport.buses.store') }}">
            @csrf
            <div class="card-body">
                @include('transport.buses._form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Bus</button>
            </div>
        </form>
    </div>
</div>
@stop
