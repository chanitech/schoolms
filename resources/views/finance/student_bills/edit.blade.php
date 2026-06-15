@extends('adminlte::page')

@section('title', 'Edit Bill #' . $studentBill->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-file-invoice-dollar"></i> Edit Bill</h1>
        <a href="{{ route('finance.student-bills.show', $studentBill) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Bill
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow">
                <div class="card-header">
                    <h3 class="card-title">Bill #{{ $studentBill->id }}</h3>
                </div>
                <div class="card-body">

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

                    <form action="{{ route('finance.student-bills.update', $studentBill) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="student_id">
                                <i class="fas fa-user-graduate text-primary mr-1"></i> Student
                                <span class="text-danger">*</span>
                            </label>
                            <select name="student_id" id="student_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Select Student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ (old('student_id', $studentBill->student_id) == $student->id) ? 'selected' : '' }}>
                                        {{ $student->first_name }} {{ $student->last_name }} ({{ $student->admission_no ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bill_number">
                                        <i class="fas fa-hashtag text-info mr-1"></i> Bill Number
                                    </label>
                                    <input type="text" name="bill_number" id="bill_number"
                                           class="form-control @error('bill_number') is-invalid @enderror"
                                           value="{{ old('bill_number', $studentBill->bill_number) }}">
                                    @error('bill_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">
                                        <i class="fas fa-money-bill-wave text-success mr-1"></i> Amount (TZS)
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">TZS</span>
                                        </div>
                                        <input type="number" step="0.01" name="amount" id="amount"
                                               class="form-control @error('amount') is-invalid @enderror"
                                               value="{{ old('amount', $studentBill->total_amount ?? $studentBill->amount) }}" required>
                                    </div>
                                    @error('amount')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">
                                <i class="fas fa-align-left text-muted mr-1"></i> Description
                            </label>
                            <textarea name="description" id="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="e.g., Tuition fee, Library charges">{{ old('description', $studentBill->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="due_date">
                                <i class="fas fa-calendar-alt text-warning mr-1"></i> Due Date
                            </label>
                            <input type="date" name="due_date" id="due_date"
                                   class="form-control @error('due_date') is-invalid @enderror"
                                   value="{{ old('due_date', optional($studentBill->due_date)->format('Y-m-d')) }}">
                            @error('due_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">
                                <i class="fas fa-info-circle text-secondary mr-1"></i> Status
                            </label>
                            <select name="status" id="status" class="form-control">
                                <option value="pending" {{ old('status', $studentBill->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ old('status', $studentBill->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="overdue" {{ old('status', $studentBill->status) == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Update Bill
                            </button>
                            <a href="{{ route('finance.student-bills.show', $studentBill) }}" class="btn btn-default ml-2">
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
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#student_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Search for a student',
                allowClear: false
            });
        });
    </script>
@stop