@extends('adminlte::page')

@section('title', 'Transport Fees')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-file-invoice-dollar mr-2"></i>Transport Fees</h1>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    @if($errors->any())
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <select name="route_id" class="form-control form-control-sm mr-2">
                    <option value="">All routes</option>
                    @foreach($routes as $r)
                        <option value="{{ $r->id }}" {{ request('route_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control form-control-sm mr-2">
                    <option value="">All statuses</option>
                    @foreach(['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid'] as $sk => $sl)
                        <option value="{{ $sk }}" {{ request('status') === $sk ? 'selected' : '' }}>{{ $sl }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('transport.fees.index') }}" class="btn btn-sm btn-outline-secondary ml-1">Reset</a>
            </form>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Student</th>
                        <th>Route</th>
                        <th>Period</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Balance</th>
                        <th>Status</th>
                        @can('record transport payments')<th></th>@endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($fees as $fee)
                    @php $badge = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success'][$fee->status]; @endphp
                    <tr>
                        <td>{{ trim(($fee->student->first_name ?? '') . ' ' . ($fee->student->last_name ?? '')) }}</td>
                        <td>{{ $fee->route->name ?? '—' }}</td>
                        <td>{{ $fee->periodLabel() }}</td>
                        <td class="text-right">{{ number_format($fee->amount) }}</td>
                        <td class="text-right">{{ number_format($fee->amount_paid) }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($fee->balance) }}</td>
                        <td><span class="badge badge-{{ $badge }}">{{ ucfirst($fee->status) }}</span></td>
                        @can('record transport payments')
                        <td class="text-right">
                            @if($fee->status !== 'paid')
                            <button type="button" class="btn btn-xs btn-primary pay-btn"
                                data-action="{{ route('transport.fees.pay', $fee) }}"
                                data-student="{{ trim(($fee->student->first_name ?? '') . ' ' . ($fee->student->last_name ?? '')) }}"
                                data-balance="{{ $fee->balance }}">
                                <i class="fas fa-money-bill-wave mr-1"></i>Record Payment
                            </button>
                            @endif
                        </td>
                        @endcan
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No transport fees generated yet — open a route and click "Generate Monthly Fees".</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $fees->links() }}</div>
    </div>
</div>

{{-- Record payment modal --}}
<div class="modal fade" id="payModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave"></i> Record Payment</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="payForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p id="payStudentLabel" class="font-weight-bold"></p>
                    <p class="text-muted small">Outstanding balance: <strong id="payBalanceLabel"></strong> TZS</p>
                    <div class="form-group">
                        <label>Amount (TZS)</label>
                        <input type="number" name="amount" id="payAmount" step="0.01" min="0.01" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Reference (optional)</label>
                        <input type="text" name="reference" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="form-group">
                        <label>Note (optional)</label>
                        <textarea name="note" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $('.pay-btn').click(function() {
        $('#payForm').attr('action', $(this).data('action'));
        $('#payStudentLabel').text($(this).data('student'));
        $('#payBalanceLabel').text(Number($(this).data('balance')).toLocaleString());
        $('#payAmount').attr('max', $(this).data('balance')).val($(this).data('balance'));
        $('#payModal').modal('show');
    });
</script>
@stop
