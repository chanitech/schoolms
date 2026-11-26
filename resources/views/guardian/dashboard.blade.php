@extends('adminlte::page')

@section('title', 'My Children')

@push('css')
<style>
:root {
    --em:#059669;--em-lt:#d1fae5;--em-dk:#047857;
    --sl-900:#0f172a;--sl-700:#334155;--sl-500:#64748b;
    --sl-400:#94a3b8;--sl-200:#e2e8f0;--sl-100:#f1f5f9;--sl-50:#f8fafc;
    --gold:#f59e0b;--sky:#0284c7;--rose:#e11d48;
    --r:.25rem;--r-sm:8px;
    --sh-sm:0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    --sh-md:0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}


*{box-sizing:border-box;}


/* Hero */
.hero{background:#001f3f;border-radius:var(--r);padding:2.2rem 2rem 1.9rem;margin-bottom:1.4rem;position:relative;overflow:hidden;color:#fff;}
.hero::before{display:none}
.hero-eye{font-size:.67rem;color:var(--em-lt);font-weight:600;margin-bottom:.4rem;display:flex;align-items:center;gap:.4rem;}
.hero h1{font-size:1.95rem;margin:0 0 .35rem;line-height:1.15;}
.hero-sub{opacity:.58;font-size:.8rem;}
.hero-actions{position:relative;margin-top:1.1rem;}
.btn-hero{display:inline-flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);color:#fff;padding:.5rem 1.2rem;border-radius:20px;font-size:.8rem;font-weight:600;text-decoration:none;}
.btn-hero:hover{background:rgba(255,255,255,.22);color:#fff;text-decoration:none;}

/* Overview strip */
.qs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.9rem;margin-bottom:1.4rem;}
.qs{background:#fff;border-radius:var(--r);box-shadow:0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);padding:1rem 1.1rem;position:relative;overflow:hidden;}
.qs:nth-child(1){}.qs:nth-child(2){}.qs:nth-child(3){}
.qs-dot{width:7px;height:7px;border-radius:50%;margin-bottom:.55rem;}
.qs-lbl{font-size:.63rem;font-weight:700;color:var(--sl-400);margin-bottom:.15rem;}
.qs-val{font-size:1.55rem;color:var(--sl-900);line-height:1;}
.qs-sub{font-size:.67rem;color:var(--sl-400);margin-top:.12rem;}
.qs-ico{position:absolute;right:.8rem;top:50%;transform:translateY(-50%);font-size:2.1rem;opacity:.07;}
.c-em .qs-dot{background:var(--em)}.c-sky .qs-dot{background:var(--sky)}.c-go .qs-dot{background:var(--gold)}

/* Section label */
.sec-lbl{font-size:.66rem;font-weight:700;color:var(--sl-400);margin-bottom:.9rem;display:flex;align-items:center;gap:.45rem;}
.sec-lbl::before{content:'';display:block;width:14px;height:2px;background:var(--em);border-radius:2px;}

/* Student cards */
.stu-card{background:#fff;border-radius:var(--r);box-shadow:0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);overflow:hidden;margin-bottom:1.4rem;}
.stu-card:hover{}
.stu-top{background:#28a745;padding:1.3rem 1.5rem;position:relative;overflow:hidden;}
.stu-top::before{display:none}
.stu-top-inner{position:relative;display:flex;align-items:center;gap:1rem;}
.stu-avatar{width:64px;height:64px;flex-shrink:0;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.35);}
.stu-name{font-size:1.2rem;color:#fff;margin:0 0 .2rem;}
.stu-meta{font-size:.74rem;color:rgba(255,255,255,.75);}
.stu-badge{display:inline-block;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.25);border-radius:20px;padding:.1rem .6rem;font-size:.66rem;font-weight:700;color:#fff;margin-top:.35rem;}

.stu-body{padding:1.3rem 1.5rem;}
.fin-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.7rem;margin-bottom:1.1rem;}
.fin-box{border-radius:var(--r-sm);padding:.8rem .6rem;text-align:center;}
.fin-box.warn{background:#fff5f5;}
.fin-box.good{background:#f0fff4;}
.fin-box.info{background:#e3f2fd;}
.fin-ico{font-size:1.3rem;margin-bottom:.25rem;}
.fin-lbl{font-size:.62rem;font-weight:700;color:var(--sl-500);}
.fin-val{font-weight:700;color:var(--sl-900);font-size:.86rem;margin-top:.1rem;}
.fin-track{height:3px;background:#ffe0e0;border-radius:2px;margin-top:.4rem;overflow:hidden;}
.fin-fill{height:3px;border-radius:2px;background:var(--rose);}

.stu-actions{display:flex;justify-content:space-between;align-items:center;gap:.6rem;}
.btn-em{display:inline-flex;align-items:center;gap:.4rem;background:var(--em);border:none;color:#fff;padding:.5rem 1.1rem;border-radius:var(--r-sm);font-size:.82rem;font-weight:600;text-decoration:none;box-shadow:0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);}
.btn-em:hover{box-shadow:0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);color:#fff;text-decoration:none;}
.btn-ghost{display:inline-flex;align-items:center;gap:.4rem;background:var(--sl-50);border:1px solid var(--sl-200);color:var(--sl-700);padding:.5rem 1.1rem;border-radius:var(--r-sm);font-size:.82rem;font-weight:600;text-decoration:none;}
.btn-ghost:hover{background:var(--sl-100);color:var(--sl-700);text-decoration:none;}
.lock-msg{display:flex;align-items:center;gap:.4rem;color:var(--gold);font-size:.8rem;font-weight:600;}

/* Empty state */
.empty-state{background:#fff;border-radius:var(--r);box-shadow:0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);padding:3rem 2rem;text-align:center;}
.empty-state i{font-size:2.6rem;color:var(--sl-200);margin-bottom:1rem;}
.empty-state h5{color:var(--sl-900);}
.empty-state p{color:var(--sl-500);font-size:.88rem;}

@media(max-width:576px){.fin-grid{gap:.5rem;}.fin-val{font-size:.76rem;}}

/* Notice board */
.notice-board{background:#fff;border-radius:var(--r);box-shadow:0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);padding:1.2rem 1.4rem;margin-bottom:1.4rem;}
.notice-item{display:flex;gap:.7rem;padding:.6rem 0;}
.notice-item+.notice-item{border-top:1px solid var(--sl-100);}
.notice-ico{width:32px;height:32px;flex-shrink:0;border-radius:50%;background:var(--em-lt);color:var(--em-dk);display:flex;align-items:center;justify-content:center;font-size:.85rem;}
.notice-item.n-all .notice-ico{background:#e0e7ff;color:#4338ca;}
.notice-item.n-pinned .notice-ico{background:#fef3c7;color:#b45309;}
.notice-title{font-weight:700;color:var(--sl-900);font-size:.86rem;}
.notice-body{color:var(--sl-500);font-size:.78rem;margin-top:.1rem;}
</style>
@endpush

@section('content_header')@stop

@section('content')
<div class="container-fluid px-3 py-2">

    @if(session('success'))
        <div class="alert mb-3" style="border-radius:var(--r);border:none;background:#dcfce7;color:#15803d;">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Hero --}}
    <div class="hero">
        <h1>Welcome, {{ $guardian->first_name }}</h1>
        <div class="hero-actions">
            <a href="{{ route('guardian.fees') }}" class="btn-hero">
                <i class="fas fa-file-invoice-dollar"></i> Detailed Financials
            </a>
        </div>
    </div>

    {{-- Notice Board --}}
    @if($notices->isNotEmpty())
        <div class="notice-board">
            <div class="sec-lbl" style="margin-bottom:.6rem;">Notice Board</div>
            @foreach($notices as $notice)
                <div class="notice-item {{ $notice->pinned ? 'n-pinned' : ($notice->audience === 'all' ? 'n-all' : '') }}">
                    <div class="notice-ico"><i class="fas {{ $notice->pinned ? 'fa-thumbtack' : 'fa-bullhorn' }}"></i></div>
                    <div>
                        <div class="notice-title">
                            {{ $notice->title }}
                            @if($notice->created_at->gt(now()->subDays(3)))
                                <span style="display:inline-block;background:var(--em-lt);color:var(--em-dk);font-size:.58rem;font-weight:700;border-radius:20px;padding:.06rem .45rem;vertical-align:middle;margin-left:.3rem;">New</span>
                            @endif
                        </div>
                        <div class="notice-body">{{ \Illuminate\Support\Str::limit($notice->body, 140) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($students->isEmpty())
        <div class="empty-state">
            <i class="fas fa-user-graduate"></i>
            <h5>No Children Linked</h5>
            <p>Your profile is not yet linked to any student. Please contact the school administration.</p>
        </div>
    @else
        {{-- Overview strip --}}
        @php
            $totalOutstanding = $students->sum('outstanding_fees');
            $totalPocket      = $students->sum('pocket_balance');
        @endphp
        <div class="qs-grid">
            <div class="qs c-em">
                <div class="qs-dot"></div><div class="qs-lbl">Children</div>
                <div class="qs-val">{{ $students->count() }}</div><div class="qs-sub">Linked to your account</div>
                <i class="fas fa-user-graduate qs-ico"></i>
            </div>
            <div class="qs c-sky">
                <div class="qs-dot"></div><div class="qs-lbl">Outstanding</div>
                <div class="qs-val" style="font-size:1.15rem;">TZS {{ number_format($totalOutstanding, 0) }}</div><div class="qs-sub">Across all children</div>
                <i class="fas fa-hand-holding-usd qs-ico"></i>
            </div>
            <div class="qs c-go">
                <div class="qs-dot"></div><div class="qs-lbl">Pocket Money</div>
                <div class="qs-val" style="font-size:1.15rem;">TZS {{ number_format($totalPocket, 0) }}</div><div class="qs-sub">Combined balance</div>
                <i class="fas fa-coins qs-ico"></i>
            </div>
        </div>

        <div class="sec-lbl">Your Children</div>

        <div class="row">
            @foreach($students as $student)
                <div class="col-lg-6">
                    <div class="stu-card">
                        <div class="stu-top">
                            <div class="stu-top-inner">
                                <img src="{{ $student->photo ? asset('storage/'.$student->photo) : asset('images/default-avatar.png') }}"
                                     class="stu-avatar" alt="{{ $student->full_name }}">
                                <div>
                                    <h3 class="stu-name">{{ $student->full_name }}</h3>
                                    <div class="stu-meta">{{ $student->admission_no }} &middot; {{ $student->class->name ?? 'N/A' }}</div>
                                    <span class="stu-badge">{{ ucfirst($student->gender) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="stu-body">
                            <div class="fin-grid">
                                <div class="fin-box warn">
                                    <div class="fin-ico"><i class="fas fa-hand-holding-usd text-warning"></i></div>
                                    <div class="fin-lbl">Outstanding</div>
                                    <div class="fin-val">TZS {{ number_format($student->outstanding_fees, 0) }}</div>
                                    @if($student->outstanding_fees > 0)
                                        <div class="fin-track">
                                            <div class="fin-fill" style="width:{{ ($student->total_billed > 0) ? min(100, round(($student->outstanding_fees / $student->total_billed) * 100)) : 0 }}%"></div>
                                        </div>
                                    @endif
                                </div>
                                <div class="fin-box good">
                                    <div class="fin-ico"><i class="fas fa-check-circle text-success"></i></div>
                                    <div class="fin-lbl">Paid</div>
                                    <div class="fin-val">TZS {{ number_format($student->total_paid, 0) }}</div>
                                </div>
                                <div class="fin-box info">
                                    <div class="fin-ico"><i class="fas fa-coins text-info"></i></div>
                                    <div class="fin-lbl">Pocket</div>
                                    <div class="fin-val">TZS {{ number_format($student->pocket_balance, 0) }}</div>
                                </div>
                            </div>

                            <div class="stu-actions">
                                @if($student->results_locked)
                                    <span class="lock-msg"><i class="fas fa-lock"></i> Results locked &ndash; settle fees to view</span>
                                @else
                                    <a href="{{ route('guardian.result.show', $student) }}" class="btn-em">
                                        <i class="fas fa-chart-bar"></i> View Results
                                    </a>
                                    <button type="button" class="btn-ghost ai-insight-btn" data-student="{{ $student->id }}"
                                            data-url="{{ route('guardian.child.ai.insight', $student) }}">
                                        <i class="fas fa-robot"></i> AI Insight
                                    </button>
                                @endif
                                <a href="{{ route('guardian.fees') }}" class="btn-ghost">
                                    <i class="fas fa-list-alt"></i> All Financials
                                </a>
                            </div>

                            <div class="ai-insight-box" id="ai-insight-{{ $student->id }}" style="display:none; margin-top:12px; padding:14px; border-radius:.25rem; background:#f8f9fc; border:1px solid #e3e6f0;"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();

        marked.setOptions({ breaks: true, gfm: true });

        $('.ai-insight-btn').on('click', function () {
            const btn = $(this);
            const box = $('#ai-insight-' + btn.data('student'));

            box.show().html('<em>Analyzing performance&hellip;</em>');
            btn.prop('disabled', true);

            $.post(btn.data('url'), { _token: '{{ csrf_token() }}' })
                .done(r => box.html(marked.parse(r.analysis || '*No response generated.*')))
                .fail(x => box.html('<span class="text-danger">' +
                    (x.responseJSON?.message || 'Request failed. Please try again.') + '</span>'))
                .always(() => btn.prop('disabled', false));
        });
    });
</script>
@endpush
