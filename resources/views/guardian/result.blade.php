@extends('adminlte::page')

@section('title', 'Student Results')

{{--
  Variables from showResult():
    $student, $exam, $exams, $departments, $sessions,
    $selected_session_id, $selected_exam_id, $selected_department_id,
    $subjectsData, $totalPoints (null=incomplete), $result, $rank,
    $isIncomplete, $attemptedCount, $requiredCount,
    $gpaTrend, $subjectTrend, $bestSubjectsOverall
--}}

@push('css')
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<style>
:root {
    --em:#059669;--em-lt:#d1fae5;--em-dk:#047857;
    --sl-900:#0f172a;--sl-700:#334155;--sl-500:#64748b;
    --sl-400:#94a3b8;--sl-200:#e2e8f0;--sl-100:#f1f5f9;--sl-50:#f8fafc;
    --gold:#f59e0b;--sky:#0284c7;--vio:#7c3aed;--rose:#e11d48;--or:#ea580c;
    --r:14px;--r-sm:8px;
    --sh-sm:0 1px 3px rgba(0,0,0,.06),0 4px 12px rgba(0,0,0,.07);
    --sh-md:0 2px 8px rgba(0,0,0,.08),0 12px 28px rgba(0,0,0,.11);
    --sh-lg:0 4px 16px rgba(0,0,0,.10),0 24px 48px rgba(0,0,0,.14);
}
body{font-family:'DM Sans',sans-serif;background:#f0f4f8;color:var(--sl-700);}
.content-wrapper{background:#f0f4f8!important;}
*{box-sizing:border-box;}
@keyframes slideUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
@keyframes countUp{from{opacity:0;transform:scale(.85)}to{opacity:1;transform:scale(1)}}

/* Hero */
.hero{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 55%,#064e3b 100%);border-radius:var(--r);padding:2.2rem 2rem 1.9rem;margin-bottom:1.4rem;position:relative;overflow:hidden;color:#fff;animation:slideUp .38s ease both;}
.hero::before{content:'';position:absolute;inset:0;pointer-events:none;background:radial-gradient(ellipse at 82% 40%,rgba(5,150,105,.28) 0%,transparent 55%),radial-gradient(ellipse at 14% 88%,rgba(2,132,199,.16) 0%,transparent 45%);}
.hero-eye{font-size:.67rem;letter-spacing:.16em;text-transform:uppercase;color:var(--em-lt);font-weight:600;margin-bottom:.4rem;display:flex;align-items:center;gap:.4rem;}
.hero h1{font-family:'DM Serif Display',serif;font-size:1.95rem;margin:0 0 .35rem;line-height:1.15;}
.hero-sub{opacity:.58;font-size:.8rem;}

/* Back button */
.btn-back{display:inline-flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);color:#fff;padding:.35rem 1rem;border-radius:20px;font-size:.78rem;font-weight:600;text-decoration:none;transition:background .2s;margin-bottom:1rem;}
.btn-back:hover{background:rgba(255,255,255,.22);color:#fff;text-decoration:none;}

/* Student card */
.stu-card{background:#fff;border-radius:var(--r);box-shadow:var(--sh-sm);border-left:5px solid var(--em);padding:1.4rem 1.8rem;margin-bottom:1.4rem;display:flex;align-items:center;gap:1.6rem;animation:slideUp .42s ease .04s both;}
.stu-avatar{width:106px;height:106px;flex-shrink:0;border-radius:50%;object-fit:cover;border:4px solid var(--em-lt);box-shadow:0 0 0 3px var(--em),var(--sh-sm);}
.stu-name{font-family:'DM Serif Display',serif;font-size:1.45rem;color:var(--sl-900);margin:0 0 .45rem;}
.stu-meta{display:flex;flex-wrap:wrap;gap:.3rem .9rem;font-size:.78rem;}
.stu-meta span{display:flex;align-items:center;gap:.35rem;color:var(--sl-700);}
.stu-meta i{color:var(--em);font-size:.68rem;width:11px;}

/* Incomplete banner */
.inc-banner{background:linear-gradient(135deg,#7f1d1d,#b91c1c);border-radius:var(--r);padding:1.1rem 1.4rem;margin-bottom:1.4rem;color:#fff;display:flex;align-items:center;gap:1rem;animation:slideUp .4s ease both;}
.inc-banner i{font-size:1.6rem;opacity:.8;}
.inc-banner h6{margin:0 0 .2rem;font-size:.95rem;font-weight:700;}
.inc-banner p{margin:0;font-size:.78rem;opacity:.8;}
.inc-progress{flex:1;margin-left:auto;}
.inc-bar-track{height:8px;background:rgba(255,255,255,.2);border-radius:4px;overflow:hidden;}
.inc-bar-fill{height:8px;background:#fff;border-radius:4px;transition:width .8s ease;}
.inc-label{font-size:.68rem;opacity:.7;margin-top:.25rem;text-align:right;}

/* Filter bar */
.filter-bar{background:#fff;border-radius:var(--r);box-shadow:var(--sh-sm);padding:1.1rem 1.4rem;margin-bottom:1.4rem;animation:slideUp .44s ease .06s both;}
.fl{font-size:.66rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--sl-500);margin-bottom:.28rem;display:block;}
.fsel{border:1.5px solid var(--sl-200);border-radius:var(--r-sm);font-size:.83rem;height:40px;padding:0 2rem 0 .8rem;width:100%;background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%2394a3b8' d='M5 7L0 2h10z'/%3E%3C/svg%3E") no-repeat right .7rem center;-webkit-appearance:none;appearance:none;color:var(--sl-900);transition:border-color .18s,box-shadow .18s;}
.fsel:focus{border-color:var(--em);box-shadow:0 0 0 3px rgba(5,150,105,.1);outline:none;}
.btn-f{height:40px;padding:0 1.3rem;background:linear-gradient(135deg,var(--em),var(--em-dk));border:none;border-radius:var(--r-sm);color:#fff;font-weight:600;font-size:.83rem;display:inline-flex;align-items:center;justify-content:center;gap:.4rem;width:100%;box-shadow:0 4px 12px rgba(5,150,105,.33);cursor:pointer;transition:transform .14s,box-shadow .14s;}
.btn-f:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(5,150,105,.42);}

/* Quick-stat strip */
.qs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(128px,1fr));gap:.9rem;margin-bottom:1.4rem;}
.qs{background:#fff;border-radius:var(--r);box-shadow:var(--sh-sm);padding:.95rem 1rem;position:relative;overflow:hidden;animation:slideUp .46s ease both;}
.qs:nth-child(1){animation-delay:.08s}.qs:nth-child(2){animation-delay:.11s}.qs:nth-child(3){animation-delay:.14s}.qs:nth-child(4){animation-delay:.17s}.qs:nth-child(5){animation-delay:.20s}.qs:nth-child(6){animation-delay:.23s}.qs:nth-child(7){animation-delay:.26s}
.qs-dot{width:7px;height:7px;border-radius:50%;margin-bottom:.55rem;}
.qs-lbl{font-size:.63rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--sl-400);margin-bottom:.15rem;}
.qs-val{font-family:'DM Serif Display',serif;font-size:1.6rem;color:var(--sl-900);line-height:1;}
.qs-sub{font-size:.67rem;color:var(--sl-400);margin-top:.12rem;}
.qs-ico{position:absolute;right:.7rem;top:50%;transform:translateY(-50%);font-size:2rem;opacity:.07;}
.c-em .qs-dot{background:var(--em)}.c-sky .qs-dot{background:var(--sky)}.c-ro .qs-dot{background:var(--rose)}.c-go .qs-dot{background:var(--gold)}.c-vi .qs-dot{background:var(--vio)}.c-or .qs-dot{background:var(--or)}.c-tl .qs-dot{background:#0891b2}

/* Big stat cards */
.big-stat{border-radius:var(--r);padding:1.5rem 1.4rem;position:relative;overflow:hidden;box-shadow:var(--sh-md);color:#fff;animation:slideUp .48s ease both;transition:transform .2s,box-shadow .2s;}
.big-stat:hover{transform:translateY(-3px);box-shadow:var(--sh-lg);}
.bs-lbl{font-size:.66rem;letter-spacing:.12em;text-transform:uppercase;font-weight:600;margin-bottom:.4rem;opacity:.72;}
.bs-val{font-family:'DM Serif Display',serif;font-size:2.5rem;line-height:1;margin-bottom:.28rem;animation:countUp .6s ease both;}
.bs-note{font-size:.72rem;opacity:.58;}
.bs-ico{position:absolute;right:1.1rem;bottom:.9rem;font-size:3.3rem;opacity:.1;}
.bg-gpa{background:linear-gradient(135deg,#064e3b,#059669 80%);}
.bg-div{background:linear-gradient(135deg,#1e3a8a,#0284c7 80%);}
.bg-rnk{background:linear-gradient(135deg,#7f1d1d,#e11d48 80%);}
.bg-pts{background:linear-gradient(135deg,#713f12,#f59e0b 80%);}

/* Results table */
.tcard{background:#fff;border-radius:var(--r);box-shadow:var(--sh-md);overflow:hidden;margin-bottom:1.4rem;animation:slideUp .48s ease .12s both;}
.thead-bar{background:linear-gradient(135deg,var(--sl-900),#1e3a5f);padding:.9rem 1.3rem;display:flex;align-items:center;justify-content:space-between;}
.thead-bar h5{font-family:'DM Serif Display',serif;color:#fff;margin:0;font-size:1.03rem;}
.d-pill{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.17);border-radius:20px;padding:.2rem .75rem;font-size:.66rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--em-lt);}
.rtbl{width:100%;border-collapse:separate;border-spacing:0;}
.rtbl thead th{background:var(--sl-50);color:var(--sl-500);font-size:.66rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:.78rem .95rem;border-bottom:2px solid var(--sl-200);text-align:center;}
.rtbl tbody td{padding:.82rem .95rem;border-bottom:1px solid var(--sl-100);text-align:center;font-size:.845rem;color:var(--sl-700);vertical-align:middle;}
.rtbl tbody tr{transition:background .14s;}
.rtbl tbody tr:hover{background:#f0fdf9;}
.rtbl tbody tr:last-child td{border-bottom:none;}
.rtbl tfoot td{background:var(--em-lt);padding:.78rem .95rem;font-weight:700;font-size:.845rem;color:var(--sl-900);}
.td-sub{font-weight:500;color:var(--sl-900);text-align:left!important;}
.td-mark{font-weight:700;font-size:.93rem;color:var(--sl-900);}
.mark-track{width:76px;height:5px;background:var(--sl-100);border-radius:3px;margin:.28rem auto 0;}
.mark-fill{height:5px;border-radius:3px;}

/* Grade badges */
.gb{display:inline-flex;align-items:center;justify-content:center;width:31px;height:31px;border-radius:var(--r-sm);font-weight:800;font-size:.78rem;}
.gA{background:#dcfce7;color:#15803d}.gB{background:#dbeafe;color:#1d4ed8}.gC{background:#fef9c3;color:#854d0e}.gD{background:#ffedd5;color:#c2410c}.gE,.gF{background:#fee2e2;color:#b91c1c}.gX{background:var(--sl-100);color:var(--sl-500)}

/* Remark + position pills */
.rp{display:inline-block;padding:.17rem .58rem;border-radius:20px;font-size:.66rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;}
.pb{display:inline-flex;align-items:center;justify-content:center;min-width:27px;height:21px;border-radius:6px;font-size:.7rem;font-weight:700;background:var(--sl-100);color:var(--sl-700);}
.p1{background:#fef9c3;color:#854d0e}.p2{background:#f1f5f9;color:#1e293b}.p3{background:#ffedd5;color:#c2410c}

/* Section label */
.sec-lbl{font-size:.66rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--sl-400);margin-bottom:.7rem;display:flex;align-items:center;gap:.45rem;}
.sec-lbl::before{content:'';display:block;width:14px;height:2px;background:var(--em);border-radius:2px;}

/* Insight cards */
.ins-card{background:#fff;border-radius:var(--r);box-shadow:var(--sh-sm);overflow:hidden;height:100%;animation:slideUp .5s ease both;}
.ins-head{padding:.75rem 1.15rem;display:flex;align-items:center;gap:.55rem;border-bottom:1px solid var(--sl-100);}
.ins-ico{width:29px;height:29px;border-radius:var(--r-sm);display:flex;align-items:center;justify-content:center;font-size:.78rem;}
.ins-ttl{font-family:'DM Serif Display',serif;font-size:.93rem;color:var(--sl-900);margin:0;}
.ins-body{padding:1rem 1.15rem;}
.prow{display:flex;align-items:center;gap:.7rem;padding:.45rem 0;border-bottom:1px solid var(--sl-100);}
.prow:last-child{border-bottom:none;}
.prow-sub{flex:1;font-size:.8rem;font-weight:500;color:var(--sl-900);}
.prow-bar{width:96px;height:6px;background:var(--sl-100);border-radius:3px;flex-shrink:0;}
.prow-fill{height:6px;border-radius:3px;}
.prow-mk{font-size:.8rem;font-weight:700;color:var(--sl-700);min-width:36px;text-align:right;}

/* Perf meter */
.band-track{height:9px;border-radius:5px;overflow:hidden;background:linear-gradient(90deg,#fee2e2,#fef9c3,#dcfce7);position:relative;}
.band-thumb{position:absolute;top:50%;transform:translate(-50%,-50%);width:13px;height:13px;border-radius:50%;background:#fff;border:3px solid var(--sl-900);box-shadow:0 1px 4px rgba(0,0,0,.28);transition:left .9s cubic-bezier(.34,1.56,.64,1);}
.band-lbls{display:flex;justify-content:space-between;font-size:.62rem;color:var(--sl-400);margin-top:.28rem;}

/* Highlight boxes */
.hi-box{background:var(--sl-50);border-radius:var(--r-sm);padding:.8rem .5rem;text-align:center;}
.hi-cap{font-size:.62rem;text-transform:uppercase;letter-spacing:.07em;color:var(--sl-400);font-weight:700;margin-bottom:.28rem;}
.hi-sub{font-size:.8rem;font-weight:700;margin-bottom:.15rem;}
.hi-val{font-size:1.2rem;font-weight:800;}

/* Grade legend */
.gl{display:flex;flex-direction:column;gap:.45rem;}
.gl-r{display:flex;align-items:center;gap:.55rem;font-size:.78rem;}
.gl-d{width:9px;height:9px;border-radius:2px;flex-shrink:0;}
.gl-l{flex:1;color:var(--sl-700);}
.gl-c{font-weight:700;color:var(--sl-900);}
.gl-p{color:var(--sl-400);font-size:.7rem;}

/* Chart card */
.ch-card{background:#fff;border-radius:var(--r);box-shadow:var(--sh-sm);overflow:hidden;animation:slideUp .5s ease .3s both;}
.ch-head{padding:.78rem 1.15rem;display:flex;align-items:center;gap:.55rem;border-bottom:1px solid var(--sl-100);}
.ch-ico{width:29px;height:29px;border-radius:var(--r-sm);display:flex;align-items:center;justify-content:center;font-size:.78rem;}
.ch-ttl{font-family:'DM Serif Display',serif;font-size:.93rem;color:var(--sl-900);margin:0;}
.ch-body{padding:1.05rem;}

/* Subject comparison table */
.cmp-tbl{width:100%;border-collapse:collapse;font-size:.8rem;}
.cmp-tbl th{background:var(--sl-50);color:var(--sl-500);font-size:.63rem;letter-spacing:.07em;text-transform:uppercase;padding:.55rem .8rem;border-bottom:2px solid var(--sl-200);text-align:left;}
.cmp-tbl td{padding:.55rem .8rem;border-bottom:1px solid var(--sl-100);vertical-align:middle;}
.cmp-tbl tr:last-child td{border-bottom:none;}
.cmp-tbl tr:hover td{background:#f0fdf9;}
.trend-chip{display:inline-flex;align-items:center;gap:.25rem;font-size:.68rem;font-weight:700;padding:.15rem .5rem;border-radius:20px;}
.trend-up{background:#dcfce7;color:#15803d;}
.trend-dn{background:#fee2e2;color:#b91c1c;}
.trend-eq{background:#f1f5f9;color:#64748b;}

@media(max-width:768px){.stu-card{flex-direction:column;text-align:center}.hero h1{font-size:1.45rem}.qs-grid{grid-template-columns:repeat(2,1fr)}}
</style>
@endpush

@section('content_header')@stop

@section('content')
<div class="container-fluid px-3 py-2">

    @if(session('warning'))
        <div class="alert mb-3" style="border-radius:var(--r);border:none;background:#fef9c3;color:#854d0e;display:flex;align-items:center;gap:.6rem;">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        </div>
    @elseif(session('error'))
        <div class="alert mb-3" style="border-radius:var(--r);border:none;background:#fee2e2;color:#b91c1c;display:flex;align-items:center;gap:.6rem;">
            <i class="fas fa-times-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Back to Guardian Dashboard button --}}
    <a href="{{ route('guardian.dashboard') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Children
    </a>

    {{-- Hero --}}
    <div class="hero">
        <div class="hero-eye"><i class="fas fa-graduation-cap"></i> Academic Results</div>
        <h1>Student Result Summary</h1>
        <p class="hero-sub">
            {{ $exam->name ?? 'Select an exam to view results' }}
            @if(!empty($selected_session_id))
                · {{ $sessions->firstWhere('id', $selected_session_id)->name ?? '' }}
            @endif
        </p>
    </div>

    {{-- Student Profile --}}
    <div class="stu-card">
        <img class="stu-avatar"
             src="{{ $student->photo ? asset('storage/'.$student->photo) : asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}"
             alt="{{ $student->name }}">
        <div class="flex-grow-1">
            <h2 class="stu-name">{{ $student->name }}</h2>
            <div class="stu-meta">
                <span><i class="fas fa-id-badge"></i><strong>Adm:</strong>&nbsp;{{ $student->admission_no ?? 'N/A' }}</span>
                <span><i class="fas fa-chalkboard"></i><strong>Class:</strong>&nbsp;{{ $student->class->name ?? 'N/A' }}</span>
                <span><i class="fas fa-file-alt"></i><strong>Exam:</strong>&nbsp;{{ $exam->name ?? 'All' }}</span>
                <span><i class="fas fa-building"></i><strong>Dept:</strong>&nbsp;{{ $departments->firstWhere('id',$selected_department_id)->name ?? 'All' }}</span>
                @if(!empty($selected_session_id))
                    <span><i class="fas fa-calendar-alt"></i><strong>Session:</strong>&nbsp;{{ $sessions->firstWhere('id', $selected_session_id)->name ?? '' }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Filters (mirrors staff results.show) --}}
    <div class="filter-bar">
        <form action="{{ route('guardian.result.show', $student->id) }}" method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="fl"><i class="fas fa-calendar-alt me-1"></i> Academic Session</label>
                    <select name="academic_session_id" id="sessionSel" class="fsel" onchange="cascadeExams()">
                        <option value="">— All Sessions —</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}"
                                {{ ($selected_session_id ?? '') == $session->id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="fl"><i class="fas fa-file-alt me-1"></i> Exam</label>
                    <select name="exam_id" id="examSel" class="fsel">
                        <option value="">— Select Exam —</option>
                        @foreach($exams as $ex)
                            <option value="{{ $ex->id }}"
                                data-session="{{ $ex->academic_session_id }}"
                                {{ $selected_exam_id == $ex->id ? 'selected' : '' }}>
                                {{ $ex->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="fl"><i class="fas fa-building me-1"></i> Department</label>
                    <select name="department_id" class="fsel">
                        <option value="">— All Departments —</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $selected_department_id == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="fl" style="visibility:hidden;">x</label>
                    <button type="submit" class="btn-f"><i class="fas fa-search"></i> View Results</button>
                </div>
            </div>
        </form>
    </div>

    @php
        $subjects   = collect($subjectsData);
        $totalSubs  = $subjects->count();
        $passCount  = $subjects->filter(fn($s)=>!in_array($s['grade']??'',['E','F']))->count();
        $failCount  = $totalSubs - $passCount;
        $avgMark    = $totalSubs ? round($subjects->pluck('mark')->avg(), 1) : 0;
        $highest    = $subjects->sortByDesc('mark')->first();
        $lowest     = $subjects->sortBy('mark')->first();
        $passRate   = $totalSubs ? round($passCount/$totalSubs*100) : 0;
        $gradeDist  = $subjects->groupBy('grade')->map->count();
        $gradeClrs  = ['A'=>'#059669','B'=>'#0284c7','C'=>'#f59e0b','D'=>'#ea580c','E'=>'#e11d48','F'=>'#7f1d1d'];

        $topPerformingSubjects      = $subjects->filter(fn($s)=>in_array($s['grade']??'',['A','B']))->sortByDesc('mark')->values();
        $subjectsNeedingImprovement = $subjects->filter(fn($s)=>!in_array($s['grade']??'',['A','B']))->sortBy('mark')->values();

        // Display values — dashes when incomplete
        $displayPoints   = $isIncomplete ? '-' : ($totalPoints ?? '-');
        $displayGpa      = ($isIncomplete || $result['gpa'] === null) ? '-' : number_format($result['gpa'], 2);
        $displayDivision = $result['division'] ?? '-';
        $displayRank     = ($isIncomplete) ? '-' : $rank;

        // Subject progress bar for incomplete
        $incPct = $requiredCount > 0 ? min(100, round($attemptedCount / $requiredCount * 100)) : 0;

        // Per-subject trend for comparison table (first vs last exam)
        $subjectFirstLast = [];
        foreach($subjectTrend as $subName => $trendData) {
            $td = collect($trendData);
            if($td->count() >= 2) {
                $first = $td->first()['mark'] ?? null;
                $last  = $td->last()['mark']  ?? null;
                if($first !== null && $last !== null) {
                    $subjectFirstLast[$subName] = ['first'=>$first,'last'=>$last,'diff'=>round($last-$first,1)];
                }
            }
        }
    @endphp

    @if($exam && $totalSubs > 0)

    {{-- Incomplete warning banner --}}
    @if($isIncomplete)
    <div class="inc-banner">
        <i class="fas fa-triangle-exclamation"></i>
        <div class="flex-grow-1">
            <h6>Incomplete Subject Set</h6>
            <p>This student attempted {{ $attemptedCount }} of {{ $requiredCount }} required subjects. Division and rank are withheld until all subjects are completed.</p>
        </div>
        <div class="inc-progress" style="min-width:120px;">
            <div class="inc-bar-track">
                <div class="inc-bar-fill" style="width:{{ $incPct }}%;"></div>
            </div>
            <div class="inc-label">{{ $attemptedCount }}/{{ $requiredCount }} subjects ({{ $incPct }}%)</div>
        </div>
    </div>
    @endif

    {{-- Quick-stat strip --}}
    <div class="qs-grid">
        <div class="qs c-em">
            <div class="qs-dot"></div><div class="qs-lbl">Subjects</div>
            <div class="qs-val">{{ $totalSubs }}</div><div class="qs-sub">Attempted this exam</div>
            <i class="fas fa-book-open qs-ico"></i>
        </div>
        <div class="qs c-sky">
            <div class="qs-dot"></div><div class="qs-lbl">Passed</div>
            <div class="qs-val">{{ $passCount }}</div><div class="qs-sub">{{ $passRate }}% pass rate</div>
            <i class="fas fa-check-circle qs-ico"></i>
        </div>
        <div class="qs c-ro">
            <div class="qs-dot"></div><div class="qs-lbl">Failed</div>
            <div class="qs-val">{{ $failCount }}</div><div class="qs-sub">Below pass mark</div>
            <i class="fas fa-times-circle qs-ico"></i>
        </div>
        <div class="qs c-go">
            <div class="qs-dot"></div><div class="qs-lbl">Avg Mark</div>
            <div class="qs-val">{{ $avgMark }}</div><div class="qs-sub">Best {{ $attemptedCount }} subjects</div>
            <i class="fas fa-percentage qs-ico"></i>
        </div>
        <div class="qs c-vi">
            <div class="qs-dot"></div><div class="qs-lbl">Total Pts</div>
            <div class="qs-val">{{ $displayPoints }}</div>
            <div class="qs-sub">{{ $isIncomplete ? 'Incomplete' : 'Accumulated' }}</div>
            <i class="fas fa-star qs-ico"></i>
        </div>
        <div class="qs c-or">
            <div class="qs-dot"></div><div class="qs-lbl">Class Rank</div>
            <div class="qs-val">{{ $displayRank }}</div>
            <div class="qs-sub">{{ $isIncomplete ? 'Not ranked' : 'Among eligible' }}</div>
            <i class="fas fa-trophy qs-ico"></i>
        </div>
        <div class="qs c-tl">
            <div class="qs-dot"></div><div class="qs-lbl">Subjects Req.</div>
            <div class="qs-val" style="font-size:1.2rem;">{{ $attemptedCount }}/{{ $requiredCount }}</div>
            <div class="qs-sub">{{ $isIncomplete ? 'Needs more' : 'Requirement met' }}</div>
            <i class="fas fa-list-check qs-ico"></i>
        </div>
    </div>

    {{-- Big Stat Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="big-stat bg-gpa">
                <div class="bs-lbl"><i class="fas fa-chart-bar me-1"></i> GPA</div>
                <div class="bs-val">{{ $displayGpa }}</div>
                <div class="bs-note">{{ $isIncomplete ? 'Withheld – incomplete' : 'Grade Point Average' }}</div>
                <i class="fas fa-chart-bar bs-ico"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="big-stat bg-div">
                <div class="bs-lbl"><i class="fas fa-award me-1"></i> Division</div>
                <div class="bs-val">{{ $displayDivision }}</div>
                <div class="bs-note">{{ $isIncomplete ? 'Withheld – incomplete' : 'Academic classification' }}</div>
                <i class="fas fa-award bs-ico"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="big-stat bg-rnk">
                <div class="bs-lbl"><i class="fas fa-trophy me-1"></i> Class Position</div>
                <div class="bs-val">{{ $displayRank }}</div>
                <div class="bs-note">{{ $isIncomplete ? 'Withheld – incomplete' : 'Ranked among eligible' }}</div>
                <i class="fas fa-trophy bs-ico"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="big-stat bg-pts">
                <div class="bs-lbl"><i class="fas fa-star me-1"></i> Total Points</div>
                <div class="bs-val">{{ $displayPoints }}</div>
                <div class="bs-note">{{ $isIncomplete ? 'Withheld – incomplete' : 'Sum of best 7 points' }}</div>
                <i class="fas fa-star bs-ico"></i>
            </div>
        </div>
    </div>

    {{-- Subject Results Table --}}
    <div class="tcard">
        <div class="thead-bar">
            <h5><i class="fas fa-list-ul me-2"></i> Subject Results — {{ $exam->name }}</h5>
            @if($selected_department_id)
                <span class="d-pill">{{ $departments->firstWhere('id',$selected_department_id)->name ?? '' }}</span>
            @endif
        </div>
        <div style="overflow-x:auto;">
            <table class="rtbl">
                <thead>
                    <tr>
                        <th style="width:34px;">#</th>
                        <th style="text-align:left;">Subject</th>
                        <th>Mark</th>
                        <th>Grade</th>
                        <th>Point</th>
                        <th>Remark</th>
                        <th>Position</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjectsData as $subject)
                        @php
                            $g   = $subject['grade'] ?? '-';
                            $gcl = match($g){'A'=>'gA','B'=>'gB','C'=>'gC','D'=>'gD','E','F'=>'gE',default=>'gX'};
                            $rm  = $subject['remark'] ?? '-';
                            $rc  = ['Excellent'=>['#dcfce7','#15803d'],'Very Good'=>['#dbeafe','#1d4ed8'],'Good'=>['#ede9fe','#6d28d9'],'Average'=>['#fef9c3','#854d0e'],'Below Average'=>['#ffedd5','#c2410c'],'Fail'=>['#fee2e2','#b91c1c']][$rm] ?? ['#f1f5f9','#334155'];
                            $pos = $subject['subject_position'] ?? '-';
                            $pcl = match((string)$pos){'1'=>'p1','2'=>'p2','3'=>'p3',default=>''};
                            $mp  = min(100,$subject['mark']);
                            $bc  = $mp>=75?'#059669':($mp>=50?'#f59e0b':'#e11d48');
                        @endphp
                        <tr>
                            <td style="color:var(--sl-400);font-size:.73rem;">{{ $loop->iteration }}</td>
                            <td class="td-sub">{{ $subject['subject'] }}</td>
                            <td class="td-mark">
                                {{ number_format($subject['mark'],2) }}
                                <div class="mark-track"><div class="mark-fill" style="width:{{ $mp }}%;background:{{ $bc }};"></div></div>
                            </td>
                            <td><span class="gb {{ $gcl }}">{{ $g }}</span></td>
                            <td><strong>{{ $subject['point'] }}</strong></td>
                            <td><span class="rp" style="background:{{ $rc[0] }};color:{{ $rc[1] }};">{{ $rm }}</span></td>
                            <td><span class="pb {{ $pcl }}">{{ $pos }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5" style="color:var(--sl-400);">
                            <i class="fas fa-inbox fa-2x d-block mb-2 opacity-50"></i>No results found.
                        </td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right;padding-right:1rem;font-size:.7rem;letter-spacing:.06em;text-transform:uppercase;color:var(--sl-700);">Total Points</td>
                        <td colspan="3" style="font-size:1.02rem;">{{ $displayPoints }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Performance Analysis --}}
    <div class="sec-lbl mt-1 mb-3">Performance Analysis</div>

    <div class="row g-3 mb-3">
        {{-- Top Performing Subjects --}}
        <div class="col-md-6">
            <div class="ins-card">
                <div class="ins-head">
                    <div class="ins-ico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-arrow-trend-up"></i></div>
                    <h6 class="ins-ttl">Strong Subjects (Grade A or B)</h6>
                </div>
                <div class="ins-body">
                    @forelse($topPerformingSubjects as $s)
                        @php $mp=min(100,$s['mark']); $bc=$mp>=75?'#059669':($mp>=50?'#f59e0b':'#e11d48'); @endphp
                        <div class="prow">
                            <span class="prow-sub">{{ $s['subject'] }}</span>
                            <div class="prow-bar"><div class="prow-fill" style="width:{{ $mp }}%;background:{{ $bc }};"></div></div>
                            <span class="prow-mk">{{ number_format($s['mark'],1) }}</span>
                        </div>
                    @empty
                        <p class="text-center text-muted py-3">No subject scored A or B.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Subjects Needing Improvement --}}
        <div class="col-md-6">
            <div class="ins-card">
                <div class="ins-head">
                    <div class="ins-ico" style="background:#fee2e2;color:#b91c1c;"><i class="fas fa-arrow-trend-down"></i></div>
                    <h6 class="ins-ttl">Needs Improvement (Below Grade B)</h6>
                </div>
                <div class="ins-body">
                    @forelse($subjectsNeedingImprovement as $s)
                        @php $mp=min(100,$s['mark']); $bc=$mp>=75?'#059669':($mp>=50?'#f59e0b':'#e11d48'); @endphp
                        <div class="prow">
                            <span class="prow-sub">{{ $s['subject'] }}</span>
                            <div class="prow-bar"><div class="prow-fill" style="width:{{ $mp }}%;background:{{ $bc }};"></div></div>
                            <span class="prow-mk">{{ number_format($s['mark'],1) }}</span>
                        </div>
                    @empty
                        <p class="text-center text-muted py-3">Excellent! All subjects grade B or above.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Grade Distribution + Performance Meter --}}
    <div class="row g-3 mb-3">
        <div class="col-md-5">
            <div class="ch-card h-100">
                <div class="ch-head">
                    <div class="ch-ico" style="background:#ede9fe;color:var(--vio);"><i class="fas fa-chart-pie"></i></div>
                    <h6 class="ch-ttl">Grade Distribution</h6>
                </div>
                <div class="ch-body">
                    <div class="row align-items-center g-1">
                        <div class="col-7"><canvas id="gradeDonut" height="195"></canvas></div>
                        <div class="col-5">
                            <div class="gl">
                                @foreach(['A','B','C','D','E','F'] as $gr)
                                    @if(($gradeDist[$gr]??0)>0)
                                    <div class="gl-r">
                                        <div class="gl-d" style="background:{{ $gradeClrs[$gr]??'#94a3b8' }};"></div>
                                        <span class="gl-l">Grade {{ $gr }}</span>
                                        <span class="gl-c">{{ $gradeDist[$gr] }}</span>
                                        <span class="gl-p">({{ $totalSubs?round($gradeDist[$gr]/$totalSubs*100):0 }}%)</span>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="ch-card h-100">
                <div class="ch-head">
                    <div class="ch-ico" style="background:#fef9c3;color:#854d0e;"><i class="fas fa-gauge-high"></i></div>
                    <h6 class="ch-ttl">Performance Overview</h6>
                </div>
                <div class="ch-body">
                    <p style="font-size:.68rem;color:var(--sl-400);text-transform:uppercase;letter-spacing:.07em;font-weight:600;margin-bottom:.4rem;">Average Mark Position</p>
                    <div class="band-track">
                        <div class="band-thumb" style="left:{{ $avgMark }}%;"></div>
                    </div>
                    <div class="band-lbls"><span>0</span><span>Weak</span><span>Average</span><span>Good</span><span>100</span></div>
                    <hr style="border:none;border-top:1px solid var(--sl-100);margin:.9rem 0;">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="hi-box">
                                <div class="hi-cap">Best Subject</div>
                                <div class="hi-sub" style="color:#15803d;">{{ $highest['subject']??'—' }}</div>
                                <div class="hi-val" style="color:#059669;">{{ $highest?number_format($highest['mark'],1):'—' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hi-box">
                                <div class="hi-cap">Weakest Subject</div>
                                <div class="hi-sub" style="color:#b91c1c;">{{ $lowest['subject']??'—' }}</div>
                                <div class="hi-val" style="color:#e11d48;">{{ $lowest?number_format($lowest['mark'],1):'—' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hi-box">
                                <div class="hi-cap">Pass Rate</div>
                                <div class="hi-val" style="color:{{ $passRate>=60?'#059669':($passRate>=40?'#f59e0b':'#e11d48') }};">{{ $passRate }}%</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hi-box">
                                <div class="hi-cap">Average Mark</div>
                                <div class="hi-val" style="color:var(--sl-900);">{{ $avgMark }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif {{-- end @if($exam && $totalSubs > 0) --}}

    {{-- Historical Trends (session-scoped) --}}
    @if($gpaTrend->isNotEmpty() || !empty($subjectTrend) || !empty($bestSubjectsOverall))
    <div class="sec-lbl mt-1 mb-3">
        Session Trends
        @if(!empty($selected_session_id))
            <span style="font-weight:400;color:var(--sl-400);text-transform:none;letter-spacing:0;font-size:.75rem;">
                — {{ $sessions->firstWhere('id',$selected_session_id)->name ?? '' }}
            </span>
        @endif
    </div>

    <div class="row g-3 mb-3">
        @if($gpaTrend->isNotEmpty())
        <div class="col-md-6">
            <div class="ch-card">
                <div class="ch-head">
                    <div class="ch-ico" style="background:#dcfce7;color:var(--em);"><i class="fas fa-chart-area"></i></div>
                    <h6 class="ch-ttl">GPA Across Exams This Session</h6>
                </div>
                <div class="ch-body"><canvas id="gpaTrendChart" height="220"></canvas></div>
            </div>
        </div>
        @endif

        @if(!empty($subjectTrend))
        <div class="col-md-6">
            <div class="ch-card">
                <div class="ch-head">
                    <div class="ch-ico" style="background:#e0f2fe;color:var(--sky);"><i class="fas fa-chart-line"></i></div>
                    <h6 class="ch-ttl">Subject Scores Across Exams</h6>
                </div>
                <div class="ch-body"><canvas id="subjectTrendChart" height="220"></canvas></div>
            </div>
        </div>
        @endif
    </div>

    {{-- Subject progress comparison (first vs latest exam) --}}
    @if(!empty($subjectFirstLast))
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="ch-card">
                <div class="ch-head">
                    <div class="ch-ico" style="background:#fef9c3;color:var(--gold);"><i class="fas fa-arrows-left-right"></i></div>
                    <h6 class="ch-ttl">Subject Progress: First vs Latest Exam This Session</h6>
                </div>
                <div class="ch-body" style="padding:0;">
                    <table class="cmp-tbl">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>First Exam</th>
                                <th>Latest Exam</th>
                                <th>Change</th>
                                <th>Trend</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjectFirstLast as $subName => $fl)
                                @php
                                    $diff    = $fl['diff'];
                                    $chipCls = $diff > 0 ? 'trend-up' : ($diff < 0 ? 'trend-dn' : 'trend-eq');
                                    $chipIco = $diff > 0 ? 'fa-arrow-up' : ($diff < 0 ? 'fa-arrow-down' : 'fa-minus');
                                    $chipTxt = $diff > 0 ? '+' . $diff : (string)$diff;
                                @endphp
                                <tr>
                                    <td style="font-weight:600;color:var(--sl-900);">{{ $subName }}</td>
                                    <td>{{ number_format($fl['first'], 1) }}</td>
                                    <td>{{ number_format($fl['last'],  1) }}</td>
                                    <td style="font-weight:700;color:{{ $diff>0?'#15803d':($diff<0?'#b91c1c':'#64748b') }};">{{ $chipTxt }}</td>
                                    <td><span class="trend-chip {{ $chipCls }}"><i class="fas {{ $chipIco }}"></i> {{ $chipTxt }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Best subjects overall --}}
    @if(!empty($bestSubjectsOverall))
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="ch-card">
                <div class="ch-head">
                    <div class="ch-ico" style="background:#ede9fe;color:var(--vio);"><i class="fas fa-star"></i></div>
                    <h6 class="ch-ttl">Subject Averages This Session (Highest to Lowest)</h6>
                </div>
                <div class="ch-body"><canvas id="bestSubjectsChart" height="160"></canvas></div>
            </div>
        </div>
    </div>
    @endif

    @endif {{-- end trends block --}}

</div>
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* ═══ Session → Exam cascading (mirrors staff page) ═══ */
function cascadeExams() {
    const sessionId = $('#sessionSel').val();
    const examSel   = $('#examSel');

    if (!sessionId) {
        // All Sessions: show all original options
        examSel.find('option').show();
        examSel.find('option[disabled]').remove();
        return;
    }

    // Show "Loading exams…"
    examSel.html('<option value="">Loading exams…</option>');

    fetch("{{ route('marks.exams.by.session') }}?session_id=" + sessionId)
        .then(response => response.json())
        .then(exams => {
            let options = '<option value="">— Select Exam —</option>';
            if (exams.length > 0) {
                exams.forEach(exam => {
                    const sel = (exam.id == "{{ $selected_exam_id }}") ? ' selected' : '';
                    options += `<option value="${exam.id}" data-session="${sessionId}" ${sel}>${exam.name}</option>`;
                });
            } else {
                options = '<option value="" disabled>No exams for this session</option>';
            }
            examSel.html(options);
        })
        .catch(() => {
            examSel.html('<option value="">Error loading exams</option>');
        });
}

// Run on page load to respect any pre‑selected session
$(document).ready(function() {
    const sessionSel = $('#sessionSel');
    if (sessionSel.val()) {
        cascadeExams();   // Replace dropdown with exams for the selected session
    }
});

/* ═══ Charts (unchanged) ═══════════════════════════════ */
const P = ['#059669','#0284c7','#f59e0b','#e11d48','#7c3aed','#0891b2','#d97706','#be185d'];

/* Grade donut */
@if(isset($totalSubs) && $totalSubs > 0)
(function(){
    const labels = {!! json_encode($gradeDist->keys()->values()->toArray()) !!};
    const counts = {!! json_encode($gradeDist->values()->toArray()) !!};
    const clrs   = {!! json_encode($gradeDist->keys()->map(fn($g)=>$gradeClrs[$g]??'#94a3b8')->values()->toArray()) !!};
    new Chart(document.getElementById('gradeDonut'), {
        type:'doughnut',
        data:{ labels, datasets:[{ data:counts, backgroundColor:clrs, borderWidth:2, borderColor:'#fff', hoverOffset:5 }] },
        options:{ cutout:'66%', plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label: c=>' Grade '+c.label+': '+c.raw+' subject(s)' } } } }
    });
})();
@endif

/* GPA trend */
@if($gpaTrend->isNotEmpty())
new Chart(document.getElementById('gpaTrendChart'), {
    type:'line',
    data:{
        labels:{!! json_encode($gpaTrend->pluck('exam')->toArray()) !!},
        datasets:[{
            label:'GPA',
            data:{!! json_encode($gpaTrend->pluck('gpa')->toArray()) !!},
            borderColor:'#059669', backgroundColor:'rgba(5,150,105,.08)',
            borderWidth:2.5, pointRadius:5, pointHoverRadius:7,
            pointBackgroundColor:'#059669', pointBorderColor:'#fff', pointBorderWidth:2,
            fill:true, tension:.35
        }]
    },
    options:{
        responsive:true,
        plugins:{ legend:{ display:false }, tooltip:{ mode:'index', intersect:false } },
        scales:{
            x:{ grid:{ color:'#f1f5f9' }, ticks:{ font:{ size:11 } } },
            y:{ beginAtZero:true, max:5, grid:{ color:'#f1f5f9' }, ticks:{ font:{ size:11 } } }
        }
    }
});
@endif

/* Subject trend */
@if(!empty($subjectTrend))
new Chart(document.getElementById('subjectTrendChart'), {
    type:'line',
    data:{
        labels:{!! json_encode($exams->pluck('name')) !!},
        datasets:[
            @foreach($subjectTrend as $sn => $ms)
            { label:"{{ addslashes($sn) }}", data:{!! json_encode(collect($ms)->pluck('mark')->toArray()) !!}, borderWidth:2.5, pointRadius:4, pointHoverRadius:6, fill:false, tension:.35 },
            @endforeach
        ].map((d,i)=>({...d, borderColor:P[i%P.length], pointBackgroundColor:P[i%P.length], pointBorderColor:'#fff', pointBorderWidth:2}))
    },
    options:{
        responsive:true,
        plugins:{ legend:{ position:'bottom', labels:{ boxWidth:9, font:{ size:11 } } }, tooltip:{ mode:'index', intersect:false } },
        scales:{ x:{ grid:{ color:'#f1f5f9' }, ticks:{ font:{ size:11 } } }, y:{ beginAtZero:true, max:100, grid:{ color:'#f1f5f9' }, ticks:{ font:{ size:11 } } } }
    }
});
@endif

/* Best subjects bar */
@if(!empty($bestSubjectsOverall))
new Chart(document.getElementById('bestSubjectsChart'), {
    type:'bar',
    data:{
        labels:{!! json_encode(collect($bestSubjectsOverall)->pluck('subject')->toArray()) !!},
        datasets:[{
            label:'Avg Mark',
            data:{!! json_encode(collect($bestSubjectsOverall)->pluck('average')->toArray()) !!},
            backgroundColor:{!! json_encode(collect($bestSubjectsOverall)->keys()->map(fn($i)=>['rgba(5,150,105,.78)','rgba(2,132,199,.78)','rgba(245,158,11,.78)','rgba(225,29,72,.78)','rgba(124,58,237,.78)'][$i%5])->toArray()) !!},
            borderRadius:7, borderSkipped:false
        }]
    },
    options:{
        responsive:true,
        plugins:{ legend:{ display:false }, tooltip:{ mode:'index', intersect:false } },
        scales:{ x:{ grid:{ display:false }, ticks:{ font:{ size:11 } } }, y:{ beginAtZero:true, max:100, grid:{ color:'#f1f5f9' }, ticks:{ font:{ size:11 } } } }
    }
});
@endif
</script>
@endpush