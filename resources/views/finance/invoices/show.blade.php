@extends('adminlte::page')

@section('title', 'Invoice')

@section('content')
<div class="card shadow-lg p-3" style="position: relative;">

    {{-- Watermark --}}
    <img src="{{ asset('vendor/adminlte/dist/img/MEMA.webp') }}" 
         style="position:absolute; top:10%; left:25%; width:50%; opacity:0.05; z-index:0;" 
         alt="Watermark">

    <div class="card-body" style="position: relative; z-index: 1;">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <img src="{{ asset('vendor/adminlte/dist/img/MEMA.webp') }}" 
                     alt="School Logo" style="height:80px; margin-right:15px;">
                <div>
                    <h3 class="mb-0">{{ config('app.name', 'School Management System') }}</h3>
                    <p class="mb-0">Official Invoice</p>
                    <small>{{ config('school.address', 'Kisarawe, Pwani') }} | {{ config('school.phone', '+255 000 000 000') }}</small>
                </div>
            </div>
            <div class="text-right">
                <h5>Invoice No: <strong>#{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</strong></h5>
                <small>Date: {{ $invoice->payment_date ? $invoice->payment_date->format('d M, Y H:i') : 'N/A' }}</small>
            </div>
        </div>

        <hr>

        {{-- Budget & Payment Info --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="text-primary">Budget Information</h5>
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Department</th>
                        <td>{{ $invoice->budget->department->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Month / Year</th>
                        <td>{{ $invoice->budget->month }} / {{ $invoice->budget->year }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</td>
                    </tr>
                    <tr>
                        <th>Note</th>
                        <td>{{ $invoice->budget->note ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <h5 class="text-primary">Payment Information</h5>
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Amount</th>
                        <td><strong>TZS {{ number_format($invoice->amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Approved By</th>
                        <td>{{ $invoice->approvedBy->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Paid By</th>
                        <td>{{ $invoice->paidBy->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Payment Date</th>
                        <td>{{ $invoice->payment_date ? $invoice->payment_date->format('d M, Y H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Note</th>
                        <td>{{ $invoice->note ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Invoice Items --}}
        <h5 class="text-success mb-2">Invoice Items</h5>
        <table class="table table-bordered table-striped table-sm">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Amount (TZS)</th>
                    <th>Status</th>
                    <th>Approved By</th>
                    <th>Note / Comment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoiceItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->item }}</td>
                    <td>{{ $item->description ?? '-' }}</td>
                    <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $item->status)) }}</td>
                    <td>{{ $item->approvedBy->name ?? '-' }}</td>
                    <td>{{ $item->note ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="row mt-4">
            <div class="col-md-6 offset-md-6">
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Total Amount (TZS)</th>
                        <td>{{ number_format($totalAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Paid (TZS)</th>
                        <td>{{ number_format($paidAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Remaining (TZS)</th>
                        <td>{{ number_format($remainingAmount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Signature Area --}}
        <div class="row mt-5">
            <div class="col-md-4 text-center">
                <p>__________________________</p>
                <p>Approved By (DO)</p>
            </div>
            <div class="col-md-4 text-center">
                <p>__________________________</p>
                <p>Paid By (Finance)</p>
            </div>
            <div class="col-md-4 text-center">
                <p>__________________________</p>
                <p>Received By</p>
            </div>
        </div>

        {{-- Footer Buttons --}}
        <div class="text-center mt-4">
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
            <a href="{{ route('finance.invoices.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <div class="text-center mt-3">
            <small>Thank you for your payment!</small>
        </div>
    </div>
</div>
@stop
