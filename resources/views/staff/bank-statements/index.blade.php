@extends('adminlte::page')

@section('title', 'My Bank Statements')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">My Bank Statements</h1>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-university mr-2"></i> Uploaded Bank Statements
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

            @if($statements->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No bank statements have been uploaded for you yet.
                    <br><small>Statements are uploaded by the Treasurer's office. Please contact the Finance department if you believe this is an error.</small>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Month</th>
                                <th>File Name</th>
                                <th>Uploaded On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statements as $index => $stmt)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $stmt->statement_month->format('F Y') }}</td>
                                <td>{{ $stmt->original_name }}</td>
                                <td>{{ $stmt->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <a href="{{ asset('storage/' . $stmt->file_path) }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i> View / Download
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
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