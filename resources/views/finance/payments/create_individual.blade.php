@extends('adminlte::page')

@section('title', 'Record Payment')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-money-bill-wave"></i> Record Payment for {{ $studentBill->student->first_name }} {{ $studentBill->student->last_name }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- Success message --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Student Bill Details --}}
        <table class="table table-bordered mb-4">
            <tr>
                <th>Student Name</th>
                <td>{{ $studentBill->student->first_name }} {{ $studentBill->student->last_name }}</td>
            </tr>
            <tr>
                <th>Bill</th>
                <td>{{ $studentBill->bill->title ?? 'Unnamed Bill' }}</td>
            </tr>
            <tr>
                <th>Total Amount</th>
                <td>{{ number_format($studentBill->total_amount, 2) }}</td>
            </tr>
            <tr>
                <th>Amount Paid</th>
                <td>{{ number_format($studentBill->amount_paid, 2) }}</td>
            </tr>
            <tr>
                <th>Remaining Balance</th>
                <td>{{ number_format($studentBill->balance, 2) }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ ucfirst($studentBill->status ?? 'unpaid') }}</td>
            </tr>
            <tr>
                <th>Due Date</th>
                <td>{{ $studentBill->due_date ? $studentBill->due_date->format('Y-m-d') : '-' }}</td>
            </tr>
        </table>

        {{-- Payment Form --}}
        <form action="{{ route('finance.payments.store.individual') }}" method="POST">
            @csrf
            <input type="hidden" name="student_bill_id" value="{{ $studentBill->id }}">

            <div class="form-group mb-3">
                <label for="amount">Payment Amount</label>
                <input 
                    type="number" 
                    name="amount" 
                    class="form-control" 
                    id="amount" 
                    max="{{ $studentBill->balance }}" 
                    step="0.01" 
                    value="{{ old('amount') }}" 
                    required
                >
            </div>

            <div class="form-group mb-3">
                <label for="payment_method">Payment Method</label>
                <select name="payment_method" id="payment_method" class="form-control">
                    <option value="">-- Select Payment Method --</option>
                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="reference">Reference (optional)</label>
                <input 
                    type="text" 
                    name="reference" 
                    class="form-control" 
                    id="reference" 
                    placeholder="Transaction reference or note"
                    value="{{ old('reference') }}"
                >
            </div>

            <div class="form-group mb-3">
                <label for="note">Note (optional)</label>
                <textarea 
                    name="note" 
                    id="note" 
                    class="form-control" 
                    rows="2" 
                    placeholder="Any additional info"
                >{{ old('note') }}</textarea>
            </div>

            <button type="submit" class="btn btn-success">
                <i class="fas fa-check-circle"></i> Record Payment
            </button>
            <a href="{{ route('finance.payments.create') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </form>
    </div>
</div>
@stop
