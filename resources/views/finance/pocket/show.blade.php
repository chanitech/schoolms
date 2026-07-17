@extends('adminlte::page')

@section('title', 'Receipt #' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">
            <i class="fas fa-receipt"></i> Pocket Money Receipt
        </h1>
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('finance.pocket.transactions.index') }}">Transactions</a></li>
            <li class="breadcrumb-item active">Receipt</li>
        </ol>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Receipt Card --}}
            <div class="card shadow-lg" style="position: relative;">

                {{-- Watermark — the school's own logo (from School Info settings) --}}
                @if(!empty($school->logo))
                    <img src="{{ asset('storage/' . $school->logo) }}"
                         style="position:absolute; top:30%; left:25%; width:50%; opacity:0.04; z-index:0;"
                         alt="Watermark">
                @endif

                <div class="card-body" style="position: relative; z-index: 1;">
                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            @if(!empty($school->logo))
                            <img src="{{ asset('storage/' . $school->logo) }}" alt="School Logo" style="height:80px; margin-right:15px;">
                            @endif
                            <div>
                                <h3 class="mb-0">{{ $school->name ?? config('app.name', 'School Name') }}</h3>
                                @if(!empty($school->motto))
                                    <p class="mb-0 font-italic">{{ $school->motto }}</p>
                                @endif
                                <small>{{ collect([$school->address ?? null, $school->phone ?? null, $school->email ?? null])->filter()->implode(' | ') }}</small>
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
                            <h5 class="text-primary"><i class="fas fa-user-graduate mr-1"></i> Student Information</h5>
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th>Name</th>
                                    <td>{{ optional($transaction->student)->first_name ?? '-' }} {{ optional($transaction->student)->last_name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th>Admission No</th>
                                    <td>{{ optional($transaction->student)->admission_no ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Class</th>
                                    <td>{{ optional($transaction->student)->schoolClass->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-primary"><i class="fas fa-money-check-alt mr-1"></i> Transaction Details</h5>
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th>Type</th>
                                    <td>
                                        <span class="badge badge-{{ $transaction->type == 'deposit' ? 'success' : 'danger' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
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
                                    <td>{{ optional($transaction->performedBy)->name ?? 'System' }}</td>
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
                        <button onclick="window.print()" class="btn btn-primary shadow-sm">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <a href="{{ route('finance.pocket.transactions.index') }}" class="btn btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        {{-- Optionally add a PDF download --}}
                        {{-- <a href="{{ route('finance.pocket.transactions.download', $transaction) }}" class="btn btn-info shadow-sm">
                            <i class="fas fa-download"></i> PDF
                        </a> --}}
                    </div>

                    <div class="text-center mt-3">
                        <small>Thank you!</small>
                    </div>
                    @include('partials.print-powered-by')
                </div>
            </div>
        </div>
    </div>
</div>
@stop