@extends('adminlte::page')

@section('title', 'Finance Office — Job Descriptions')

@section('content_header')
    <h1 class="m-0 text-dark">Finance Office — Job Descriptions</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        </div>
    @endif

    @foreach($descriptions as $role)
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title text-capitalize">{{ str_replace('-', ' ', str_replace('_', ' ', $role['role_name'])) }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('treasurer.job-descriptions.update', $role['role_name']) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <textarea name="description" rows="4" class="form-control" required>{{ old('description', $role['description']) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save</button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@stop
