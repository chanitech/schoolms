@extends('adminlte::page')

@section('title', 'All Invoices')

@section('content_header')
    <h1>All Invoices</h1>
@stop

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    {{-- Summary small boxes --}}
    <div class="row mb-4">
        @php
            $statusColors = [
                'pending' => 'warning',
                'approved_by_do' => 'info',
                'rejected_by_do' => 'danger',
                'paid' => 'success',
            ];
        @endphp

        @foreach(['pending','approved_by_do','rejected_by_do','paid'] as $status)
            <div class="col-lg-3 col-6">
                <div class="small-box bg-{{ $statusColors[$status] }}">
                    <div class="inner">
                        <h3>{{ \App\Models\Invoice::where('status', $status)->count() }}</h3>
                        <p>{{ ucfirst(str_replace('_',' ',$status)) }}</p>
                    </div>
                    <div class="icon"><i class="fas fa-receipt"></i></div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Invoices List</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Budget Item</th>
                        <th>Budget</th>
                        <th>Amount (TZS)</th>
                        <th>Status</th>
                        <th>Approved By</th>
                        <th>Paid By</th>
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
                            <span class="badge badge-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_',' ',$invoice->status)) }}
                            </span>
                        </td>
                        <td>{{ $invoice->approvedBy->name ?? '-' }}</td>
                        <td>{{ optional($invoice->paidBy)->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('finance.invoices.show', $invoice->id) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@stop
