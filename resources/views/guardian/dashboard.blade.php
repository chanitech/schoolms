@extends('adminlte::page')

@section('title', 'My Children')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-success mb-0" style="font-weight:600;">👋 Welcome, {{ $guardian->first_name }}</h1>
            <p class="text-muted mb-0">Academic & financial overview for your children</p>
        </div>
        <a href="{{ route('guardian.fees') }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-invoice-dollar mr-1"></i> Detailed Financials
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if($students->isEmpty())
        <div class="callout callout-info">
            <h5><i class="fas fa-info-circle"></i> No children linked</h5>
            <p>Your profile is not yet linked to any student. Please contact the school administration.</p>
        </div>
    @else
        <div class="row">
            @foreach($students as $student)
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius:12px; overflow:hidden;">
                        <!-- Card Top: Student Info -->
                        <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 1.25rem 1.5rem;">
                            <div class="d-flex align-items-center">
                                <img src="{{ $student->photo ? asset('storage/'.$student->photo) : asset('images/default-avatar.png') }}"
                                     class="rounded-circle mr-3"
                                     style="width: 60px; height: 60px; object-fit: cover; border: 3px solid rgba(255,255,255,0.3);">
                                <div class="text-white">
                                    <h5 class="mb-0" style="font-weight:600;">{{ $student->full_name }}</h5>
                                    <small class="d-block" style="opacity:0.85;">{{ $student->admission_no }} · {{ $student->class->name ?? 'N/A' }}</small>
                                    <span class="badge badge-light mt-1">{{ ucfirst($student->gender) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body: Financial Summary -->
                        <div class="card-body p-3">
                            <div class="row text-center">
                                <!-- Outstanding -->
                                <div class="col-4 px-2">
                                    <div class="p-2 rounded-lg" style="background:#fff5f5;">
                                        <i class="fas fa-hand-holding-usd text-warning mb-1" style="font-size:1.4rem;"></i>
                                        <h6 class="mb-0 text-muted" style="font-size:0.7rem;">OUTSTANDING</h6>
                                        <strong class="text-dark">TZS {{ number_format($student->outstanding_fees, 2) }}</strong>
                                        @if($student->outstanding_fees > 0)
                                            <div class="progress mt-1" style="height: 3px; background:#ffe0e0;">
                                                <div class="progress-bar bg-danger" style="width:{{ ($student->total_billed > 0) ? min(100, round(($student->outstanding_fees / $student->total_billed) * 100)) : 0 }}%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <!-- Paid -->
                                <div class="col-4 px-2">
                                    <div class="p-2 rounded-lg" style="background:#f0fff4;">
                                        <i class="fas fa-check-circle text-success mb-1" style="font-size:1.4rem;"></i>
                                        <h6 class="mb-0 text-muted" style="font-size:0.7rem;">PAID</h6>
                                        <strong class="text-dark">TZS {{ number_format($student->total_paid, 2) }}</strong>
                                    </div>
                                </div>
                                <!-- Pocket Money -->
                                <div class="col-4 px-2">
                                    <div class="p-2 rounded-lg" style="background:#e3f2fd;">
                                        <i class="fas fa-coins text-info mb-1" style="font-size:1.4rem;"></i>
                                        <h6 class="mb-0 text-muted" style="font-size:0.7rem;">POCKET</h6>
                                        <strong class="text-dark">TZS {{ number_format($student->pocket_balance, 2) }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                @if($student->results_locked)
                                    <span class="text-warning" style="font-size:0.85rem;">
                                        <i class="fas fa-lock mr-1"></i> Results Locked – settle fees to view
                                    </span>
                                @else
                                    <a href="{{ route('guardian.result.show', $student) }}" class="btn btn-success btn-sm px-3">
                                        <i class="fas fa-chart-bar mr-1"></i> View Results
                                    </a>
                                @endif
                                <a href="{{ route('guardian.fees') }}" class="btn btn-outline-secondary btn-sm px-3">
                                    <i class="fas fa-list-alt mr-1"></i> All Financials
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@stop

@push('css')
<style>
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1) !important;
    }
    .rounded-lg {
        border-radius: 10px;
    }
</style>
@endpush

@push('js')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush