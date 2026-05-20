<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Statement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .loan-details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { margin-top: 20px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Loan Repayment Statement</h2>
        <p>Loan #{{ $loan->id }}</p>
    </div>
    <div class="loan-details">
        <p><strong>Staff:</strong> {{ $loan->staff->name }}</p>
        <p><strong>Loan Amount:</strong> TZS {{ number_format($loan->amount_approved ?? $loan->amount_applied, 2) }}</p>
        <p><strong>Interest Rate:</strong> {{ $loan->interest_rate_applied }}% p.a.</p>
        <p><strong>Disbursement Date:</strong> {{ $loan->disbursement_date->format('d M Y') }}</p>
    </div>
    <table>
        <thead>
            <tr><th>#</th><th>Due Date</th><th>Amount (TZS)</th><th>Status</th><th>Paid Date</th></tr>
        </thead>
        <tbody>
            @foreach($loan->repayments as $repayment)
            <tr>
                <td>{{ $repayment->installment_number }}</td>
                <td>{{ $repayment->due_date->format('d M Y') }}</td>
                <td>{{ number_format($repayment->amount, 2) }}</td>
                <td>{{ ucfirst($repayment->status) }}</td>
                <td>{{ $repayment->paid_date ? $repayment->paid_date->format('d M Y') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="total">
        <strong>Remaining Balance:</strong> TZS {{ number_format($loan->remaining_balance, 2) }}
    </div>
</body>
</html>