@extends('adminlte::page')

@section('title', 'View Payment')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-eye"></i> Payment Details</h1>
@stop

@section('content')
<div class="card shadow">
    <div class="card-body">
        <h5><strong>Student:</strong> {{ $payment->studentBill->student->name ?? '-' }}</h5>
        <p><strong>Bill:</strong> {{ $payment->studentBill->bill->title ?? '-' }}</p>
        <p><strong>Amount:</strong> TZS {{ number_format($payment->amount, 2) }}</p>
        <p><strong>Method:</strong> {{ $payment->payment_method ?? '-' }}</p>
        <p><strong>Reference:</strong> {{ $payment->reference ?? '-' }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y H:i') }}</p>
        <p><strong>Recorded By:</strong> {{ $payment->user->name ?? 'System' }}</p>

        <a href="{{ route('finance.payments.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        <a href="{{ route('finance.payments.receipt', $payment->id) }}" class="btn btn-primary"><i class="fas fa-receipt"></i> View Receipt</a>
    </div>
</div>
@stop
