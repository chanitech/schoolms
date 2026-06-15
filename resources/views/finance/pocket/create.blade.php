@extends('adminlte::page')

@section('title', 'Record Pocket Money Transaction')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-wallet"></i> Record Pocket Money Transaction</h1>
        <a href="{{ route('finance.pocket.transactions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Transactions
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow">
                <div class="card-header">
                    <h3 class="card-title">Transaction Details</h3>
                </div>
                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            {{ session('success') }}
                        </div>
                    @endif

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

                    <form action="{{ route('finance.pocket.transactions.store') }}" method="POST">
                        @csrf

                        <!-- Select Class -->
                        <div class="form-group">
                            <label for="class_id">
                                <i class="fas fa-chalkboard text-info"></i> Class
                            </label>
                            <select id="class_id" class="form-control">
                                <option value="">-- Select Class --</option>
                                @foreach($classes ?? [] as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Select Student -->
                        <div class="form-group">
                            <label for="student_id">
                                <i class="fas fa-user-graduate text-primary"></i> Student
                                <span class="text-danger">*</span>
                            </label>
                            <select name="student_id" id="student_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Select Student --</option>
                                <!-- Populated by AJAX -->
                            </select>
                            @error('student_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Transaction Type -->
                        <div class="form-group">
                            <label for="type">
                                <i class="fas fa-exchange-alt text-info"></i> Transaction Type
                                <span class="text-danger">*</span>
                            </label>
                            <select name="type" id="type" class="form-control" required>
                                <option value="deposit" {{ old('type') == 'deposit' ? 'selected' : '' }}>Deposit</option>
                                <option value="withdrawal" {{ old('type') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                            </select>
                        </div>

                        <!-- Amount -->
                        <div class="form-group">
                            <label for="amount">
                                <i class="fas fa-money-bill-wave text-success"></i> Amount (TZS)
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">TZS</span>
                                </div>
                                <input type="number" step="0.01" name="amount" id="amount"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount') }}" placeholder="0.00" required>
                            </div>
                            @error('amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Current Balance -->
                        <div id="balance-info" class="alert alert-info d-none">
                            <i class="fas fa-coins"></i> Current Balance: <strong>TZS <span id="current_balance">0.00</span></strong>
                        </div>

                        <!-- Note -->
                        <div class="form-group">
                            <label for="note">
                                <i class="fas fa-sticky-note text-muted"></i> Note (optional)
                            </label>
                            <textarea name="note" id="note" rows="2" class="form-control @error('note') is-invalid @enderror"
                                      placeholder="Optional description">{{ old('note') }}</textarea>
                            @error('note')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> Record Transaction
                            </button>
                            <a href="{{ route('finance.pocket.transactions.index') }}" class="btn btn-default ml-2">
                                <i class="fas fa-times"></i> Cancel
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
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 on student dropdown (will be populated by AJAX)
            $('#student_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Select a student',
                allowClear: false
            });

            // When class is selected, fetch students
            $('#class_id').change(function() {
                let classId = $(this).val();
                // Clear student dropdown and balance
                $('#student_id').empty().trigger('change');
                $('#current_balance').text('0.00');
                $('#balance-info').addClass('d-none');

                if (classId) {
                    $.get("{{ route('finance.pocket.students-by-class') }}", { class_id: classId })
                        .done(function(data) {
                            let options = '<option value="">-- Select Student --</option>';
                            data.forEach(function(student) {
                                options += `<option value="${student.id}">${student.first_name} ${student.last_name} (${student.admission_no || ''})</option>`;
                            });
                            $('#student_id').html(options);
                        })
                        .fail(function() {
                            alert('Error loading students.');
                        });
                }
            });

            // When student is selected, fetch last balance
            $('#student_id').on('change', function() {
                let studentId = $(this).val();
                if (!studentId) {
                    $('#current_balance').text('0.00');
                    $('#balance-info').addClass('d-none');
                    return;
                }

                $.get("{{ route('finance.pocket.last-balance') }}", { student_id: studentId })
                    .done(function(data) {
                        $('#current_balance').text(parseFloat(data.balance).toFixed(2));
                        $('#balance-info').removeClass('d-none');
                    })
                    .fail(function() {
                        $('#balance-info').addClass('d-none');
                    });
            });

            // If student_id is preselected (e.g., old value), trigger balance check
            @if(old('student_id'))
                $('#student_id').val({{ old('student_id') }}).trigger('change');
            @endif
        });
    </script>
@stop