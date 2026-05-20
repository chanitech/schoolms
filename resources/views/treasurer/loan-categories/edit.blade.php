@extends('adminlte::page')

@section('title', 'Edit Loan Category')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Edit Loan Category</h1>
        <a href="{{ route('treasurer.loan-categories.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Categories
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit mr-2"></i> Edit Category: {{ $loanCategory->name }}
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Validation Errors!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                </div>
            @endif

            <form action="{{ route('treasurer.loan-categories.update', $loanCategory) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $loanCategory->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Max Installments (months) <span class="text-danger">*</span></label>
                            <input type="number" name="max_installments" class="form-control @error('max_installments') is-invalid @enderror" 
                                   value="{{ old('max_installments', $loanCategory->max_installments) }}" required>
                            @error('max_installments')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Minimum Amount (TZS) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">TZS</span>
                                </div>
                                <input type="number" step="0.01" name="min_amount" class="form-control @error('min_amount') is-invalid @enderror" 
                                       value="{{ old('min_amount', $loanCategory->min_amount) }}" required>
                            </div>
                            @error('min_amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Maximum Amount (TZS) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">TZS</span>
                                </div>
                                <input type="number" step="0.01" name="max_amount" class="form-control @error('max_amount') is-invalid @enderror" 
                                       value="{{ old('max_amount', $loanCategory->max_amount) }}" required>
                            </div>
                            @error('max_amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Interest Rate (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="interest_rate" class="form-control @error('interest_rate') is-invalid @enderror" 
                                       value="{{ old('interest_rate', $loanCategory->interest_rate) }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            @error('interest_rate')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="is_active" 
                                       {{ old('is_active', $loanCategory->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                            <small class="form-text text-muted">Inactive categories will not appear in loan applications.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $loanCategory->description) }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Eligibility Criteria (JSON)</label>
                    <textarea name="eligibility_criteria" rows="2" class="form-control font-monospace @error('eligibility_criteria') is-invalid @enderror" 
                              placeholder='{"min_salary":500000, "min_years_employed":2}'>{{ old('eligibility_criteria', json_encode($loanCategory->eligibility_criteria, JSON_PRETTY_PRINT)) }}</textarea>
                    <small class="form-text text-muted">
                        Example: <code>{"min_salary": 500000, "min_years_employed": 2}</code>
                    </small>
                    @error('eligibility_criteria')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Restrictions (JSON)</label>
                    <textarea name="restrictions" rows="2" class="form-control font-monospace @error('restrictions') is-invalid @enderror" 
                              placeholder='{"allow_multiple_active_loans": false, "requires_guarantor": true}'>{{ old('restrictions', json_encode($loanCategory->restrictions, JSON_PRETTY_PRINT)) }}</textarea>
                    <small class="form-text text-muted">
                        Example: <code>{"allow_multiple_active_loans": false, "requires_guarantor": true}</code>
                    </small>
                    @error('restrictions')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                    <a href="{{ route('treasurer.loan-categories.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .font-monospace {
            font-family: monospace;
        }
        .custom-switch {
            padding-left: 2.25rem;
        }
    </style>
@stop