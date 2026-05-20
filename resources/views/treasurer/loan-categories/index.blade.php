@extends('adminlte::page')

@section('title', 'Loan Categories')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Loan Categories</h1>
        <a href="{{ route('treasurer.loan-categories.create') }}" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> New Category
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tags mr-2"></i> Manage Loan Categories
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Min / Max Amount (TZS)</th>
                            <th>Max Installments</th>
                            <th>Interest Rate (%)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td>{{ $cat->name }}</td>
                            <td>{{ number_format($cat->min_amount) }} – {{ number_format($cat->max_amount) }}</td>
                            <td>{{ $cat->max_installments }} months</td>
                            <td>{{ $cat->interest_rate }}%</td>
                            <td>
                                @if($cat->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('treasurer.loan-categories.edit', $cat) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('treasurer.loan-categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category? This action cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No loan categories found. 
                                <a href="{{ route('treasurer.loan-categories.create') }}">Create the first category</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-sm i {
            margin-right: 4px;
        }
    </style>
@stop