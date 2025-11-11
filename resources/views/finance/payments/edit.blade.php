@extends('adminlte::page')

@section('title', 'Edit Payment')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-edit"></i> Edit Payment Record</h1>
@stop

@section('content')
<div class="card shadow">
    <div class="card-body">
        <form action="{{ route('finance.payments.update', $payment->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label for="amount">Amount (TZS)</label>
                <input type="number" name="amount" id="amount" class="form-control" 
                       value="{{ old('amount', $payment->amount) }}" step="0.01" min="0.01" required>
            </div>

            <div class="form-group mb-3">
                <label for="payment_method">Payment Method</label>
                <input type="text" name="payment_method" id="payment_method" class="form-control" 
                       value="{{ old('payment_method', $payment->payment_method) }}">
            </div>

            <div class="form-group mb-3">
                <label for="reference">Reference</label>
                <input type="text" name="reference" id="reference" class="form-control" 
                       value="{{ old('reference', $payment->reference) }}">
            </div>

            <div class="form-group mb-3">
                <label for="note">Note (optional)</label>
                <textarea name="note" id="note" class="form-control" rows="2">{{ old('note', $payment->note) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Payment</button>
            <a href="{{ route('finance.payments.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop
