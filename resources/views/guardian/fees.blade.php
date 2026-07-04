@extends('adminlte::page')

@section('title', 'Financial Details')

@push('css')
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<style>
:root {
    --em:#059669;--em-lt:#d1fae5;--em-dk:#047857;
    --sl-900:#0f172a;--sl-700:#334155;--sl-500:#64748b;
    --sl-400:#94a3b8;--sl-200:#e2e8f0;--sl-100:#f1f5f9;--sl-50:#f8fafc;
    --gold:#f59e0b;--sky:#0284c7;--rose:#e11d48;
    --r:14px;--r-sm:8px;
    --sh-sm:0 1px 3px rgba(0,0,0,.06),0 4px 12px rgba(0,0,0,.07);
    --sh-md:0 2px 8px rgba(0,0,0,.08),0 12px 28px rgba(0,0,0,.11);
}
body{font-family:'DM Sans',sans-serif;background:#f0f4f8;color:var(--sl-700);}
.content-wrapper{background:#f0f4f8!important;}
*{box-sizing:border-box;}
@keyframes slideUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}

/* Hero */
.hero{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 55%,#064e3b 100%);border-radius:var(--r);padding:2rem 2rem 1.7rem;margin-bottom:1.4rem;position:relative;overflow:hidden;color:#fff;animation:slideUp .38s ease both;}
.hero::before{content:'';position:absolute;inset:0;pointer-events:none;background:radial-gradient(ellipse at 82% 40%,rgba(5,150,105,.28) 0%,transparent 55%),radial-gradient(ellipse at 14% 88%,rgba(2,132,199,.16) 0%,transparent 45%);}
.hero-eye{font-size:.67rem;letter-spacing:.16em;text-transform:uppercase;color:var(--em-lt);font-weight:600;margin-bottom:.4rem;display:flex;align-items:center;gap:.4rem;}
.hero h1{font-family:'DM Serif Display',serif;font-size:1.85rem;margin:0 0 .3rem;line-height:1.15;}
.hero-sub{opacity:.58;font-size:.8rem;}

.btn-back{display:inline-flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);color:#fff;padding:.4rem 1.1rem;border-radius:20px;font-size:.78rem;font-weight:600;text-decoration:none;transition:background .2s;margin-top:1rem;position:relative;}
.btn-back:hover{background:rgba(255,255,255,.22);color:#fff;text-decoration:none;}

/* Student header bar */
.stu-bar{background:#fff;border-radius:var(--r);box-shadow:var(--sh-sm);padding:1.1rem 1.4rem;margin-bottom:1rem;display:flex;align-items:center;gap:1rem;border-left:5px solid var(--em);animation:slideUp .42s ease both;}
.stu-bar img{width:48px;height:48px;border-radius:50%;object-fit:cover;border:3px solid var(--em-lt);}
.stu-bar h3{font-family:'DM Serif Display',serif;font-size:1.15rem;color:var(--sl-900);margin:0;}
.stu-bar small{color:var(--sl-500);font-size:.78rem;}

/* Big stat cards */
.fin-strip{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.9rem;margin-bottom:1.2rem;}
.big-stat{border-radius:var(--r);padding:1.2rem 1.3rem;position:relative;overflow:hidden;box-shadow:var(--sh-md);color:#fff;animation:slideUp .46s ease both;}
.bs-lbl{font-size:.66rem;letter-spacing:.1em;text-transform:uppercase;font-weight:600;margin-bottom:.3rem;opacity:.75;}
.bs-val{font-family:'DM Serif Display',serif;font-size:1.7rem;line-height:1;}
.bs-note{font-size:.7rem;opacity:.6;margin-top:.2rem;}
.bs-ico{position:absolute;right:1rem;bottom:.7rem;font-size:2.6rem;opacity:.12;}
.bg-warn{background:linear-gradient(135deg,#7c2d12,#ea580c 85%);}
.bg-good{background:linear-gradient(135deg,#064e3b,#059669 85%);}
.bg-info{background:linear-gradient(135deg,#0c4a6e,#0284c7 85%);}

/* Section card */
.sec-card{background:#fff;border-radius:var(--r);box-shadow:var(--sh-sm);overflow:hidden;margin-bottom:1.4rem;animation:slideUp .48s ease both;}
.sec-head{padding:.85rem 1.2rem;display:flex;align-items:center;gap:.55rem;border-bottom:1px solid var(--sl-100);}
.sec-ico{width:29px;height:29px;border-radius:var(--r-sm);display:flex;align-items:center;justify-content:center;font-size:.78rem;flex-shrink:0;}
.sec-ttl{font-family:'DM Serif Display',serif;font-size:.98rem;color:var(--sl-900);margin:0;}

/* Table */
.rtbl{width:100%;border-collapse:separate;border-spacing:0;font-size:.83rem;}
.rtbl thead th{background:var(--sl-50);color:var(--sl-500);font-size:.64rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;padding:.7rem .95rem;border-bottom:2px solid var(--sl-200);text-align:left;}
.rtbl tbody td{padding:.7rem .95rem;border-bottom:1px solid var(--sl-100);vertical-align:middle;color:var(--sl-700);}
.rtbl tbody tr:last-child td{border-bottom:none;}
.rtbl tbody tr:hover{background:#f8fafc;}
.empty-row{padding:1.6rem;text-align:center;color:var(--sl-400);font-size:.85rem;}

.badge-em{display:inline-block;padding:.2rem .6rem;border-radius:20px;font-size:.68rem;font-weight:700;}
.b-deposit{background:#dcfce7;color:#15803d;}
.b-withdraw{background:#fee2e2;color:#b91c1c;}
.btn-sm-em{display:inline-flex;align-items:center;gap:.3rem;background:var(--em-lt);color:var(--em-dk);border:none;padding:.3rem .7rem;border-radius:6px;font-size:.74rem;font-weight:700;text-decoration:none;}
.btn-sm-em:hover{background:#a7f3d0;color:var(--em-dk);text-decoration:none;}

@media(max-width:768px){.hero h1{font-size:1.45rem}}
</style>
@endpush

@section('content_header')@stop

@section('content')
<div class="container-fluid px-3 py-2">

    <div class="hero">
        <div class="hero-eye"><i class="fas fa-wallet"></i> Financial Overview</div>
        <h1>Financial Details</h1>
        <p class="hero-sub">Fees, pocket money and payment history for your children</p>
        <a href="{{ route('guardian.dashboard') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    @if($students->isEmpty())
        <div class="sec-card">
            <div class="sec-head"><h6 class="sec-ttl">No children linked to your account.</h6></div>
        </div>
    @else
        @foreach($students as $student)
            <div class="stu-bar">
                <img src="{{ $student->photo ? asset('storage/'.$student->photo) : asset('images/default-avatar.png') }}" alt="{{ $student->full_name }}">
                <div>
                    <h3>{{ $student->full_name }}</h3>
                    <small>{{ $student->admission_no }}</small>
                </div>
            </div>

            <div class="fin-strip">
                <div class="big-stat bg-warn">
                    <div class="bs-lbl"><i class="fas fa-exclamation-triangle mr-1"></i> Outstanding</div>
                    <div class="bs-val">{{ number_format($student->outstanding, 0) }}</div>
                    <div class="bs-note">TZS balance due</div>
                    <i class="fas fa-hand-holding-usd bs-ico"></i>
                </div>
                <div class="big-stat bg-good">
                    <div class="bs-lbl"><i class="fas fa-check-circle mr-1"></i> Total Paid</div>
                    <div class="bs-val">{{ number_format($student->total_paid, 0) }}</div>
                    <div class="bs-note">TZS paid to date</div>
                    <i class="fas fa-check-circle bs-ico"></i>
                </div>
                <div class="big-stat bg-info">
                    <div class="bs-lbl"><i class="fas fa-coins mr-1"></i> Pocket Money</div>
                    <div class="bs-val">{{ number_format($student->pocket_balance, 0) }}</div>
                    <div class="bs-note">TZS current balance</div>
                    <i class="fas fa-piggy-bank bs-ico"></i>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="sec-card">
                        <div class="sec-head">
                            <div class="sec-ico" style="background:#ffedd5;color:#c2410c;"><i class="fas fa-file-invoice-dollar"></i></div>
                            <h6 class="sec-ttl">Recent Bills</h6>
                        </div>
                        @if($student->studentBills->isNotEmpty())
                            <div style="overflow-x:auto;">
                                <table class="rtbl">
                                    <thead>
                                        <tr><th>Bill</th><th>Amount</th><th>Paid</th><th>Due</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($student->studentBills->take(5) as $studentBill)
                                            <tr>
                                                <td>{{ $studentBill->bill->title ?? 'N/A' }}</td>
                                                <td>{{ number_format($studentBill->total_amount, 2) }}</td>
                                                <td>{{ number_format($studentBill->amount_paid, 2) }}</td>
                                                <td>{{ $studentBill->due_date ? $studentBill->due_date->format('d M Y') : 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-row">No bills found.</div>
                        @endif
                    </div>

                    <div class="sec-card">
                        <div class="sec-head">
                            <div class="sec-ico" style="background:#e0f2fe;color:var(--sky);"><i class="fas fa-coins"></i></div>
                            <h6 class="sec-ttl">Recent Pocket Transactions</h6>
                        </div>
                        @if($student->pocketTransactions->isNotEmpty())
                            <div style="overflow-x:auto;">
                                <table class="rtbl">
                                    <thead>
                                        <tr><th>Date</th><th>Type</th><th>Amount</th><th>Balance</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($student->pocketTransactions->take(5) as $txn)
                                            <tr>
                                                <td>{{ $txn->created_at->format('d M Y') }}</td>
                                                <td>
                                                    @if($txn->type == 'deposit')
                                                        <span class="badge-em b-deposit">Deposit</span>
                                                    @elseif($txn->type == 'withdrawal')
                                                        <span class="badge-em b-withdraw">Withdrawal</span>
                                                    @else
                                                        {{ $txn->type }}
                                                    @endif
                                                </td>
                                                <td>{{ number_format($txn->amount, 2) }}</td>
                                                <td>{{ number_format($txn->balance_after, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-row">No pocket money transactions.</div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="sec-card">
                        <div class="sec-head">
                            <div class="sec-ico" style="background:#dcfce7;color:var(--em-dk);"><i class="fas fa-receipt"></i></div>
                            <h6 class="sec-ttl">Payment History</h6>
                        </div>
                        @if($student->payments->isNotEmpty())
                            <div style="overflow-x:auto;">
                                <table class="rtbl">
                                    <thead>
                                        <tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th><th></th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($student->payments->take(5) as $payment)
                                            <tr>
                                                <td>{{ $payment->created_at->format('d M Y') }}</td>
                                                <td>{{ number_format($payment->amount, 2) }}</td>
                                                <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                                                <td>{{ $payment->reference ?? 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('guardian.payment.receipt', $payment) }}" class="btn-sm-em">
                                                        <i class="fas fa-receipt"></i> Receipt
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-row">No payment records found.</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@stop
