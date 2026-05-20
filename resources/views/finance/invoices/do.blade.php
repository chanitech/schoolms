@extends('adminlte::page')

@section('title', 'DO Invoice Approvals')

@section('content_header')
    <h1>Invoices Pending DO Approval</h1>
@stop

@section('content')
<div class="container-fluid">

    <div class="card card-warning card-outline">
        <div class="card-header"><h3 class="card-title">Pending Invoices</h3></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Budget Item</th>
                        <th>Budget</th>
                        <th>Amount (TZS)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $invoice->budgetItem->item ?? '-' }}</td>
                        <td>#{{ $invoice->budgetItem->budget->id ?? '-' }}</td>
                        <td>{{ number_format($invoice->amount, 2) }}</td>
                        <td>
                            <span class="badge badge-warning">{{ ucfirst(str_replace('_',' ',$invoice->status)) }}</span>
                        </td>
                        <td>
                            <form action="{{ route('finance.invoices.approve', $invoice->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="approved_by_do">
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <form action="{{ route('finance.invoices.approve', $invoice->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="rejected_by_do">
                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
