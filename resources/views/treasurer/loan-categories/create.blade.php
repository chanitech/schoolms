@extends('adminlte::page')

@section('title', 'Add Loan Category')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Add Loan Category</h1>
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
                <i class="fas fa-tag mr-2"></i> Category Details
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

            <form action="{{ route('treasurer.loan-categories.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Max Installments (months) <span class="text-danger">*</span></label>
                            <input type="number" name="max_installments" class="form-control @error('max_installments') is-invalid @enderror" 
                                   value="{{ old('max_installments') }}" required>
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
                                       value="{{ old('min_amount') }}" required>
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
                                       value="{{ old('max_amount') }}" required>
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
                                       value="{{ old('interest_rate') }}" required>
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
                                <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                            <small class="form-text text-muted">Inactive categories will not appear in loan applications.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ===================== ELIGIBILITY CRITERIA (key‑value builder) ===================== --}}
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-check-circle mr-2"></i>Eligibility Criteria</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Define requirements a staff must meet to qualify for this loan category. <strong>Leave empty if none.</strong></p>
                        <div id="eligibility-container">
                            {{-- Existing rows will be injected by JS if old data exists --}}
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCriteriaRow('eligibility')">
                            <i class="fas fa-plus"></i> Add Criterion
                        </button>
                        <input type="hidden" name="eligibility_criteria" id="eligibility_json" value="{{ old('eligibility_criteria') }}">
                        <small class="form-text text-muted">Example: <strong>min_salary = 500000</strong> | <strong>min_years_employed = 2</strong></small>
                    </div>
                </div>

                {{-- ===================== RESTRICTIONS (key‑value builder) ===================== --}}
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-ban mr-2"></i>Restrictions</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Specify limitations for this loan category. <strong>Leave empty if none.</strong></p>
                        <div id="restrictions-container">
                            {{-- Existing rows will be injected by JS if old data exists --}}
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCriteriaRow('restrictions')">
                            <i class="fas fa-plus"></i> Add Restriction
                        </button>
                        <input type="hidden" name="restrictions" id="restrictions_json" value="{{ old('restrictions') }}">
                        <small class="form-text text-muted">Example: <strong>allow_multiple_active_loans = false</strong> | <strong>requires_guarantor = true</strong></small>
                    </div>
                </div>

                <div class="form-group text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Category
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

@push('js')
<script>
    // ==========================
    // Key-Value Builder Logic
    // ==========================

    /**
     * Adds a new row (key + value) to the specified container.
     * @param {string} type - 'eligibility' or 'restrictions'
     * @param {string} key - optional pre‑filled key (for old values)
     * @param {string} val - optional pre‑filled value
     */
    function addCriteriaRow(type, key = '', val = '') {
        const container = document.getElementById(type + '-container');
        const row = document.createElement('div');
        row.className = 'input-group mb-2';
        row.innerHTML = `
            <input type="text" class="form-control" placeholder="Key (e.g., min_salary)" value="${escapeHtml(key)}" onchange="updateJson('${type}')">
            <input type="text" class="form-control" placeholder="Value (e.g., 500000)" value="${escapeHtml(val)}" onchange="updateJson('${type}')">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove(); updateJson('${type}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        updateJson(type);
    }

    /**
     * Collects all key-value pairs from a container and updates the hidden JSON input.
     */
    function updateJson(type) {
        const container = document.getElementById(type + '-container');
        const rows = container.querySelectorAll('.input-group');
        const obj = {};

        rows.forEach(row => {
            const inputs = row.querySelectorAll('input');
            const key = inputs[0].value.trim();
            const val = inputs[1].value.trim();
            if (key) {
                // Try to keep numbers as numbers, but string is fine
                obj[key] = isNaN(val) || val === '' ? val : parseFloat(val);
            }
        });

        document.getElementById(type + '_json').value = Object.keys(obj).length ? JSON.stringify(obj) : '';
    }

    /**
     * Escape HTML to prevent XSS in injected values.
     */
    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * On page load, populate existing data (old input) into the builder.
     */
    document.addEventListener('DOMContentLoaded', function () {
        // Eligibility criteria
        const eligibilityJson = document.getElementById('eligibility_json').value;
        if (eligibilityJson) {
            try {
                const criteria = JSON.parse(eligibilityJson);
                for (const [key, value] of Object.entries(criteria)) {
                    addCriteriaRow('eligibility', key, String(value));
                }
            } catch (e) {
                console.error('Invalid eligibility_criteria JSON');
            }
        }

        // Restrictions
        const restrictionsJson = document.getElementById('restrictions_json').value;
        if (restrictionsJson) {
            try {
                const restrictions = JSON.parse(restrictionsJson);
                for (const [key, value] of Object.entries(restrictions)) {
                    addCriteriaRow('restrictions', key, String(value));
                }
            } catch (e) {
                console.error('Invalid restrictions JSON');
            }
        }
    });
</script>
@endpush

@section('css')
    <style>
        .font-monospace { font-family: monospace; }
        .custom-switch { padding-left: 2.25rem; }
    </style>
@stop