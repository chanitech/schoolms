<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $school->name ?? 'School' }} – Loan Repayment Statement</title>
    <style>
        @page {
            margin: 15mm 10mm 20mm 10mm;
            size: A4 portrait;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            position: relative;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.06;
        }
        .watermark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 12px;
            position: relative;
        }
        .school-details {
            text-align: center;
        }
        .school-details h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .school-details h2 {
            margin: 0;
            font-size: 14px;
            font-style: italic;
            font-weight: normal;
        }
        .school-details div {
            font-size: 11px;
            margin-top: 2px;
        }

        .statement-title {
            margin: 10px 0 5px 0;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }

        /* Loan Info Box */
        .info-box {
            border: 1px solid #333;
            padding: 10px;
            margin-bottom: 15px;
            background: #f9f9f9;
            font-size: 11px;
        }
        .info-box table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }
        .info-box td {
            border: none;
            padding: 3px 6px;
        }
        .info-box .label {
            font-weight: bold;
            width: 35%;
        }

        /* Repayment Table */
        table.repayment {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }
        table.repayment th, table.repayment td {
            border: 0.7px solid #333;
            padding: 5px 8px;
            text-align: center;
        }
        table.repayment th {
            background: #f0f0f0;
            font-weight: bold;
        }
        table.repayment td {
            vertical-align: middle;
        }

        /* Summary & Balance */
        .summary-box {
            margin-top: 10px;
            padding: 8px 12px;
            border: 1px solid #333;
            background: #f8f8f8;
            font-size: 12px;
            text-align: right;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 35px;
            font-size: 10px;
            color: #555;
            border-top: 0.5px solid #999;
            padding: 5px 10px;
            text-align: right;
        }

        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 4px;
            font-size: 10px;
        }
    </style>
</head>
<body>

{{-- Watermark from school logo or default --}}
@php
    $wmPath    = public_path('vendor/adminlte/dist/img/MEMA.png');
    $wmB64     = file_exists($wmPath) ? base64_encode(file_get_contents($wmPath)) : null;
@endphp
@if($wmB64)
<div class="watermark">
    <img src="data:image/png;base64,{{ $wmB64 }}" alt="Watermark">
</div>
@endif

{{-- Header --}}
<div class="header">
    <div class="school-details">
        <h1>{{ $school->name ?? 'School Name' }}</h1>
        <h2>{{ $school->motto ?? 'Motto' }}</h2>
        <div>
            {{ $school->address ?? 'Address' }} |
            {{ $school->phone ?? 'Phone' }} |
            {{ $school->email ?? 'Email' }}
        </div>
    </div>
    <div class="statement-title">
        LOAN REPAYMENT STATEMENT
    </div>
</div>

{{-- Loan Information --}}
<div class="info-box">
    <table>
        <tr>
            <td class="label">Staff Name:</td>
            <td>{{ optional($loan->staff)->name ?? 'N/A' }}</td>
            <td class="label">Loan ID:</td>
            <td>#{{ $loan->id }}</td>
        </tr>
        <tr>
            <td class="label">Loan Amount:</td>
            <td>TZS {{ number_format($loan->amount_approved ?? $loan->amount_applied, 2) }}</td>
            <td class="label">Interest Rate:</td>
            <td>{{ $loan->interest_rate_applied }}% p.a.</td>
        </tr>
        <tr>
            <td class="label">Disbursement Date:</td>
            <td>{{ optional($loan->disbursement_date)->format('d M Y') ?? 'Not yet' }}</td>
            <td class="label">Status:</td>
            <td>{{ ucfirst($loan->status) }}</td>
        </tr>
        <tr>
            <td class="label">Installments:</td>
            <td>{{ $loan->installments }}</td>
            <td class="label">Application Date:</td>
            <td>{{ $loan->application_date->format('d M Y') }}</td>
        </tr>
    </table>
</div>

{{-- Repayment Schedule --}}
<table class="repayment">
    <thead>
        <tr>
            <th>#</th>
            <th>Due Date</th>
            <th>Amount (TZS)</th>
            <th>Status</th>
            <th>Paid Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($loan->repayments as $repayment)
        <tr>
            <td>{{ $repayment->installment_number }}</td>
            <td>{{ optional($repayment->due_date)->format('d M Y') ?? 'N/A' }}</td>
            <td>{{ number_format($repayment->amount, 2) }}</td>
            <td>{{ ucfirst($repayment->status) }}</td>
            <td>
                @if($repayment->paid_date)
                    {{ $repayment->paid_date->format('d M Y') }}
                @else
                    -
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5">No repayment schedule available yet.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- Summary --}}
<div class="summary-box">
    <strong>Remaining Balance:</strong> TZS {{ number_format($loan->remaining_balance ?? ($loan->amount_approved ?? $loan->amount_applied), 2) }}
    @if($loan->repayments->isNotEmpty())
        &nbsp;|&nbsp;
        <strong>Total Paid:</strong> TZS {{ number_format($loan->repayments->where('status', 'paid')->sum('amount'), 2) }}
    @endif
</div>

{{-- Signatures (optional, for official use) --}}
<div class="signature-section">
    <div class="signature-box">
        Prepared by
    </div>
    <div class="signature-box">
        Approved by
    </div>
</div>

<div class="footer">
    Generated by {{ $school->name ?? 'School' }} | {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}
    &nbsp;|&nbsp; Powered by ShulePRO — a Chani Technologies product · +255 713 209 535 · www.chanitech.co.tz
</div>

</body>
</html>