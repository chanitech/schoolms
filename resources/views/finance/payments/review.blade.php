@extends('adminlte::page')

@section('title', 'Payments — Verification Review')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Payments Awaiting Verification</h1>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-check-double mr-2"></i> Pending &amp; Flagged Payments
            </h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                </div>
            @endif

            @if($payments->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Bill</th>
                                <th>Amount (TZS)</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->studentBill->student->first_name ?? '—' }} {{ $payment->studentBill->student->last_name ?? '' }}</td>
                                <td>{{ $payment->schoolClass->name ?? '—' }}</td>
                                <td>{{ $payment->studentBill->bill->title ?? '—' }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->payment_method ?? '—' }}</td>
                                <td>
                                    @if($payment->status === 'flagged')
                                        <span class="badge badge-danger">Flagged</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('finance.payments.verify', $payment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Verify
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-warning flag-btn" data-id="{{ $payment->id }}">
                                        <i class="fas fa-flag"></i> Flag
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $payments->links() }}
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No payments awaiting verification.
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="flagModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-flag"></i> Flag Payment for Treasurer Review</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="flagForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="note">Reason</label>
                        <textarea name="note" id="note" rows="3" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Flag</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.flag-btn').click(function() {
            let paymentId = $(this).data('id');
            $('#flagForm').attr('action', '{{ url("finance/payments") }}/' + paymentId + '/flag');
            $('#flagModal').modal('show');
        });
    });
</script>
@stop
