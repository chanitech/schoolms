@extends('adminlte::page')

@section('title', 'Payment Receipt')

@section('content')
<div class="card shadow-lg p-3" style="position: relative;">

    {{-- Watermark --}}
    <img src="{{ asset('vendor/adminlte/dist/img/MEMA.webp') }}" 
         style="position:absolute; top:30%; left:25%; width:50%; opacity:0.05; z-index:0;" 
         alt="Watermark">

    <div class="card-body" style="position: relative; z-index: 1;">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <img src="{{ asset('vendor/adminlte/dist/img/MEMA.webp') }}" 
                     alt="School Logo" style="height:80px; margin-right:15px;">
                <div>
                    <h3 class="mb-0">{{ config('app.name', 'School Management System') }}</h3>
                    <p class="mb-0">Official Payment Receipt</p>
                    <small>{{ config('school.address', '123 Main St, City') }} | {{ config('school.phone', '+255 000 000 000') }}</small>
                </div>
            </div>
            <div class="text-right">
                <h5>Receipt No: <strong>#{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</strong></h5>
                <small>Date: {{ $payment->payment_date->format('d M, Y H:i') }}</small>
            </div>
        </div>

        <hr>

        {{-- Student & Payment Info --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="text-primary">Student Information</h5>
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Name</th>
                        <td>{{ $payment->student->first_name }} {{ $payment->student->last_name }}</td>
                    </tr>
                    <tr>
                        <th>Admission No</th>
                        <td>{{ $payment->student->admission_no ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Class</th>
                        <td>{{ $payment->student->schoolClass->name ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <h5 class="text-primary">Payment Details</h5>
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Bill</th>
                        <td>{{ $payment->studentBill->bill->title ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Amount Paid</th>
                        <td><strong>TZS {{ number_format($payment->amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Payment Method</th>
                        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Reference</th>
                        <td>{{ $payment->reference ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Received By</th>
                        <td>{{ $payment->user->name ?? 'System' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Balance Summary --}}
        <h5 class="text-success mb-2">Balance Summary</h5>
        <table class="table table-bordered table-striped table-sm">
            <thead class="thead-light">
                <tr>
                    <th>Total Bill (TZS)</th>
                    <th>Total Paid (TZS)</th>
                    <th>Remaining Balance (TZS)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($payment->studentBill->total_amount, 2) }}</td>
                    <td>{{ number_format($payment->studentBill->amount_paid, 2) }}</td>
                    <td class="text-danger">{{ number_format($payment->studentBill->balance, 2) }}</td>
                    <td>{{ ucfirst($payment->studentBill->status) }}</td>
                </tr>
            </tbody>
        </table>

        @if($payment->note)
        <div class="alert alert-info mt-3">
            <strong>Note:</strong> {{ $payment->note }}
        </div>
        @endif

        {{-- Footer Buttons --}}
        <div class="text-center mt-4">
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
            <a href="{{ route('finance.payments.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <div class="text-center mt-3">
            <small>Thank you for your payment!</small>
        </div>
    </div>
</div>
@stop
