@extends('adminlte::page')

@section('title', 'Upload Bank Statement')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-upload"></i> Upload Bank Statement</h1>
        <a href="{{ route('treasurer.bank-statements.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Statements
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow">
                <div class="card-header">
                    <h3 class="card-title">New Statement Upload</h3>
                </div>
                <div class="card-body">

                    {{-- Validation errors --}}
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Validation Errors!</h5>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Flash messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('treasurer.bank-statements.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="staff_id">
                                <i class="fas fa-user-tie text-primary mr-1"></i> Staff Member
                                <span class="text-danger">*</span>
                            </label>
                            <select name="staff_id" id="staff_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Select Staff --</option>
                                @foreach($staffList as $staff)
                                    <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }} ({{ $staff->email ?? $staff->position ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('staff_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="statement_month">
                                <i class="fas fa-calendar-alt text-info mr-1"></i> Statement Month
                                <span class="text-danger">*</span>
                            </label>
                            <input type="month" name="statement_month" id="statement_month"
                                   class="form-control @error('statement_month') is-invalid @enderror"
                                   value="{{ old('statement_month') }}" required>
                            @error('statement_month')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="file">
                                <i class="fas fa-file-upload text-success mr-1"></i> File
                                <small class="text-muted">(PDF, JPG, PNG – max 5MB)</small>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="custom-file">
                                <input type="file" name="file" id="file"
                                       class="custom-file-input @error('file') is-invalid @enderror"
                                       accept=".pdf,.jpg,.png" required>
                                <label class="custom-file-label" for="file">Choose file</label>
                            </div>
                            @error('file')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-cloud-upload-alt mr-1"></i> Upload Statement
                            </button>
                            <a href="{{ route('treasurer.bank-statements.index') }}" class="btn btn-default ml-2">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
    <style>
        .custom-file-label::after { content: "Browse"; }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#staff_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Select a staff member',
                allowClear: false
            });

            // Show filename in custom file input
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName || 'Choose file');
            });
        });
    </script>
@stop