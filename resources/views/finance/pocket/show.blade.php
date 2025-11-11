@extends('adminlte::page')

@section('title', 'Pocket Money Receipt')

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
                    <p class="mb-0">Pocket Money Transaction Receipt</p>
                    <small>{{ config('school.address', '123 Main St, City') }} | {{ config('school.phone', '+255 000 000 000') }}</small>
                </div>
            </div>
            <div class="text-right">
                <h5>Receipt No: <strong>#{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</strong></h5>
                <small>Date: {{ $transaction->created_at->format('d M, Y H:i') }}</small>
            </div>
        </div>

        <hr>

        {{-- Student & Transaction Info --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="text-primary">Student Information</h5>
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Name</th>
                        <td>{{ $transaction->student->first_name ?? '-' }} {{ $transaction->student->last_name ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Admission No</th>
                        <td>{{ $transaction->student->admission_no ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Class</th>
                        <td>{{ $transaction->student->schoolClass->name ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <h5 class="text-primary">Transaction Details</h5>
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Type</th>
                        <td>{{ ucfirst($transaction->type) }}</td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td><strong>TZS {{ number_format($transaction->amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Balance After</th>
                        <td>TZS {{ number_format($transaction->balance_after, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Performed By</th>
                        <td>{{ $transaction->performedBy->name ?? 'System' }}</td>
                    </tr>
                    <tr>
                        <th>Note</th>
                        <td>{{ $transaction->note ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Footer Buttons --}}
        <div class="text-center mt-4">
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
            <a href="{{ route('finance.pocket.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <div class="text-center mt-3">
            <small>Thank you!</small>
        </div>
    </div>
</div>
@stop
