@extends('adminlte::page')

@section('title', 'Active Loans')

@section('content_header')
    <h1>Active Loans – Record Repayments</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr><th>Staff</th><th>Category</th><th>Amount</th><th>Remaining Balance</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($loans as $loan)
                <tr>
                    <td>{{ $loan->staff->name }}</td>
                    <td>{{ $loan->category->name }}</td>
                    <td>{{ number_format($loan->amount_approved, 2) }}</td>
                    <td>{{ number_format($loan->remaining_balance, 2) }}</td>
                    <td>
                        <a href="{{ route('treasurer.loans.statement', $loan) }}" class="btn btn-sm btn-primary">
                            Record Repayments
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $loans->links() }}
    </div>
</div>
@stop