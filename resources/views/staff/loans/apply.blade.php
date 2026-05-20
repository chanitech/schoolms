@extends('adminlte::page')

@section('title', 'Apply for Loan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Apply for a Loan</h1>
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.loans.index') }}">My Loans</a></li>
            <li class="breadcrumb-item active">Apply</li>
        </ol>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary shadow-lg">
                <div class="card-header bg-gradient-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-hand-holding-usd mr-2"></i> New Loan Application
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus text-white"></i>
                        </button>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <i class="icon fas fa-ban"></i>
                            <strong>Validation Errors!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="loanApplicationForm" action="{{ route('staff.loans.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="loan_category_id">
                                <i class="fas fa-tags text-primary mr-1"></i> Loan Category
                                <span class="text-danger">*</span>
                            </label>
                            <select name="loan_category_id" id="loan_category_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Select a loan type --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                            data-min="{{ $cat->min_amount }}"
                                            data-max="{{ $cat->max_amount }}"
                                            data-installments="{{ $cat->max_installments }}"
                                            data-interest="{{ $cat->interest_rate }}">
                                        {{ $cat->name }}
                                        ({{ number_format($cat->min_amount) }} – {{ number_format($cat->max_amount) }} TZS,
                                        {{ $cat->interest_rate }}% interest)
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Select the type of loan you wish to apply for.</small>
                        </div>

                        <div class="form-group">
                            <label for="amount_applied">
                                <i class="fas fa-money-bill-wave text-success mr-1"></i> Amount Applied (TZS)
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">TZS</span>
                                </div>
                                <input type="number" step="0.01" name="amount_applied" id="amount_applied"
                                       class="form-control" placeholder="e.g., 5000000" required>
                            </div>
                            <small id="amount_help" class="form-text text-muted"></small>
                        </div>

                        <div class="form-group">
                            <label for="installments">
                                <i class="fas fa-calendar-alt text-info mr-1"></i> Number of Installments (months)
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="installments" id="installments" class="form-control" placeholder="e.g., 12" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">months</span>
                                </div>
                            </div>
                            <small id="installment_help" class="form-text text-muted"></small>
                        </div>

                        <div class="alert alert-light border rounded-lg p-3 mt-4 bg-gradient-light">
                            <div class="row">
                                <div class="col-sm-6">
                                    <i class="fas fa-user-circle fa-2x float-left mr-3 text-primary"></i>
                                    <div>
                                        <strong>{{ $staff->name }}</strong><br>
                                        <small>{{ $staff->position ?? 'Staff Member' }}</small>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <i class="fas fa-wallet text-success"></i>
                                    <strong>Basic Salary:</strong><br>
                                    <span class="text-success font-weight-bold">TZS {{ number_format($staff->basic_salary, 2) }}</span>
                                </div>
                                <div class="col-sm-3">
                                    <i class="fas fa-briefcase text-warning"></i>
                                    <strong>Years Employed:</strong><br>
                                    {{ $staff->years_employed }}
                                </div>
                            </div>
                        </div>

                        <!-- Real-time calculation summary (hidden initially) -->
                        <div id="loanSummary" class="card bg-light d-none mt-3">
                            <div class="card-body p-3">
                                <h6 class="card-title"><i class="fas fa-calculator"></i> Loan Summary</h6>
                                <div class="row mt-2">
                                    <div class="col-6">Principal:</div>
                                    <div class="col-6 text-right" id="summaryPrincipal">TZS 0</div>
                                    <div class="col-6">Total Interest:</div>
                                    <div class="col-6 text-right" id="summaryInterest">TZS 0</div>
                                    <div class="col-6 font-weight-bold">Total Payable:</div>
                                    <div class="col-6 text-right font-weight-bold" id="summaryTotal">TZS 0</div>
                                    <div class="col-6">Monthly Installment:</div>
                                    <div class="col-6 text-right text-primary" id="summaryMonthly">TZS 0</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-right mt-4">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="submitBtn">
                                <i class="fas fa-paper-plane mr-2"></i> Submit Application
                            </button>
                            <a href="{{ route('staff.loans.index') }}" class="btn btn-default btn-lg ml-2">
                                <i class="fas fa-times-circle mr-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>

        <!-- Right sidebar: Eligibility hints -->
        <div class="col-lg-4">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-1"></i> Eligibility Tips
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="callout callout-info">
                        <h5><i class="fas fa-check-circle"></i> General Requirements</h5>
                        <ul class="mb-0">
                            <li>Minimum 2 years of service (varies by loan type)</li>
                            <li>No active defaulted loans</li>
                            <li>Monthly installment ≤ 40% of basic salary</li>
                        </ul>
                    </div>
                    <div class="callout callout-warning">
                        <h5><i class="fas fa-lightbulb"></i> Before You Apply</h5>
                        <p>Make sure your bank statements are up to date. The Treasurer may request additional documents.</p>
                    </div>
                    <div class="text-center mt-3">
                        <i class="fas fa-shield-alt fa-3x text-muted"></i>
                        <p class="small text-muted mt-2">All applications are processed securely and confidentially.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .select2-container--default .select2-selection--single {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
            height: calc(2.25rem + 2px);
            padding: 0.375rem 0.75rem;
        }
        .card-header .card-title {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .bg-gradient-primary {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
        }
        .bg-gradient-light {
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
        }
        .btn-primary {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .card.shadow-lg {
            box-shadow: 0 1rem 3rem rgba(0,0,0,.175);
        }
        .callout {
            border-radius: 0.5rem;
        }
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for better dropdown
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Select a loan type',
            allowClear: false
        });

        const categorySelect = $('#loan_category_id');
        const amountInput = $('#amount_applied');
        const installmentsInput = $('#installments');
        const amountHelp = $('#amount_help');
        const installmentHelp = $('#installment_help');
        const loanSummary = $('#loanSummary');
        const summaryPrincipal = $('#summaryPrincipal');
        const summaryInterest = $('#summaryInterest');
        const summaryTotal = $('#summaryTotal');
        const summaryMonthly = $('#summaryMonthly');
        const submitBtn = $('#submitBtn');

        function calculateLoan() {
            const amount = parseFloat(amountInput.val());
            const months = parseInt(installmentsInput.val());
            const selectedOption = categorySelect.find(':selected');
            const interestRate = parseFloat(selectedOption.data('interest')) || 0;

            if (!isNaN(amount) && !isNaN(months) && months > 0 && amount > 0) {
                // Simple interest calculation: total interest = principal * rate * (years)
                const years = months / 12;
                const totalInterest = amount * (interestRate / 100) * years;
                const totalPayable = amount + totalInterest;
                const monthlyInstallment = totalPayable / months;

                summaryPrincipal.text('TZS ' + amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                summaryInterest.text('TZS ' + totalInterest.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                summaryTotal.text('TZS ' + totalPayable.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                summaryMonthly.text('TZS ' + monthlyInstallment.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                loanSummary.removeClass('d-none');
            } else {
                loanSummary.addClass('d-none');
            }
        }

        function updateRangeHelp() {
            const option = categorySelect.find(':selected');
            const min = option.data('min');
            const max = option.data('max');
            const maxInst = option.data('installments');

            if (min && max) {
                amountHelp.html(`Allowed range: <strong>${Number(min).toLocaleString()}</strong> – <strong>${Number(max).toLocaleString()}</strong> TZS`);
                amountInput.attr('min', min);
                amountInput.attr('max', max);
            } else {
                amountHelp.html('');
            }

            if (maxInst) {
                installmentHelp.html(`Maximum installments allowed: <strong>${maxInst}</strong> months`);
                installmentsInput.attr('max', maxInst);
            } else {
                installmentHelp.html('');
            }
            calculateLoan();
        }

        categorySelect.on('change', updateRangeHelp);
        amountInput.on('input', calculateLoan);
        installmentsInput.on('input', calculateLoan);

        // Trigger on page load if any category preselected
        if (categorySelect.val()) {
            updateRangeHelp();
        }

        // Disable submit button while submitting (prevent double submission)
        $('#loanApplicationForm').on('submit', function() {
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...');
            return true;
        });
    });
</script>
@stop