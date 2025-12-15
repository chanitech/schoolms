@extends('adminlte::page')

@section('title', 'HOD Budget Actions')

@section('content_header')
<h1>HOD – Approved Budgets Ready for Withdrawal</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Compute summary totals --}}
    @php
        $summary = [
            'approved' => 0,
            'used' => 0,
            'rejected' => 0,
            'partially_approved' => 0,
        ];

        $summaryCounts = [
            'approved' => 0,
            'used' => 0,
            'rejected' => 0,
            'partially_approved' => 0,
        ];

        foreach ($budgets as $budget) {
            foreach ($summary as $status => &$amount) {
                $items = $budget->items->where('status', $status);
                $amount += $items->sum('price');
                $summaryCounts[$status] += $items->count();
            }
        }

        $statusColors = [
            'approved' => 'success',
            'withdrawn' => 'warning',
            'rejected' => 'danger',
            'partially_approved' => 'info',
        ];
    @endphp

    {{-- Grand Totals Summary Cards --}}
    <div class="row mb-4">
        @foreach($summary as $status => $amount)
            <div class="col-lg-3 col-6">
                <div class="small-box bg-{{ $statusColors[$status] ?? 'secondary' }}">
                    <div class="inner">
                        <h3>{{ number_format($amount, 2) }} TZS</h3>
                        <p>{{ ucfirst(str_replace('_',' ',$status)) }} Amount</p>
                        <small>{{ $summaryCounts[$status] }} items</small>
                    </div>
                    <div class="icon"><i class="fas fa-coins"></i></div>
                </div>
            </div>
        @endforeach
    </div>

    @forelse($budgets as $budget)
        <div class="card card-primary card-outline mb-4">
            <div class="card-header">
                <h3 class="card-title">Budget #{{ $budget->id }} | {{ $budget->department->name ?? 'N/A' }} Department</h3>
            </div>

            <div class="card-body">
                {{-- Budget Info --}}
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Submitted By:</strong> {{ $budget->staff->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>Month / Year:</strong> {{ $budget->month }} / {{ $budget->year }}</div>
                    <div class="col-md-3"><strong>Status:</strong> 
                        <span class="badge badge-info">{{ ucfirst(str_replace('_',' ',$budget->status)) }}</span>
                    </div>
                    <div class="col-md-3"><strong>Note:</strong> {{ $budget->note ?? '-' }}</div>
                </div>

                {{-- Budget Item Summary Boxes --}}
                <div class="row mb-4">
                    @foreach(['approved','used','rejected','partially_approved'] as $stat)
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-{{ $statusColors[$stat] ?? 'secondary' }}">
                                <div class="inner">
                                    <h3>{{ $budget->items->where('status', $stat)->count() }}</h3>
                                    <p>{{ ucfirst(str_replace('_',' ',$stat)) }}</p>
                                </div>
                                <div class="icon"><i class="fas fa-box"></i></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Items Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Price (TZS)</th>
                                <th>Status</th>
                                <th>Withdraw</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($budget->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->item }}</td>
                                    <td>{{ $item->description ?? '-' }}</td>
                                    <td>{{ number_format($item->price, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $statusColors[$item->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_',' ',$item->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->status === 'approved')
                                            <form action="{{ route('finance.budgets.withdraw', $item->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    Withdraw
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Totals per budget --}}
                            <tr class="table-secondary font-weight-bold">
                                <td colspan="3" class="text-right">Total Amount:</td>
                                <td>{{ number_format($budget->items->sum('price'), 2) }}</td>
                                <td colspan="2"></td>
                            </tr>

                            <tr class="table-success font-weight-bold">
                                <td colspan="3" class="text-right">Total Approved Amount:</td>
                                <td>{{ number_format($budget->items->where('status','approved')->sum('price'), 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">
            No approved budgets available for HOD withdrawal.
        </div>
    @endforelse

    {{-- Grand Totals Table --}}
    <div class="card card-dark">
        <div class="card-header">
            <h3 class="card-title">Grand Totals Summary</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Status</th>
                        <th>Total Amount (TZS)</th>
                        <th>Total Items</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary as $status => $amount)
                        <tr>
                            <td>{{ ucfirst(str_replace('_',' ',$status)) }}</td>
                            <td>{{ number_format($amount, 2) }}</td>
                            <td>{{ $summaryCounts[$status] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@stop
