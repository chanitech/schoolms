@extends('adminlte::page')
@section('title', 'My Daily Reports')

@push('css')
<style>
.report-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    transition: transform .15s, box-shadow .15s;
    overflow: hidden;
}
.report-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.12); }
.status-bar { height: 4px; width: 100%; }
.day-badge { font-size: .7rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 600; }
.kpi-mini { background: #f8f9fc; border-radius: 8px; padding: .6rem 1rem; text-align: center; }
.kpi-mini .num { font-size: 1.5rem; font-weight: 700; line-height: 1.1; }
.empty-state { padding: 4rem 0; text-align: center; color: #9ca3af; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: .4; }
</style>
@endpush

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
    <div>
        <h1 class="m-0 font-weight-bold" style="font-size:1.4rem">
            <i class="fas fa-clipboard-list mr-2" style="color:#4e73df"></i>My Daily Reports
        </h1>
        <p class="text-muted mb-0" style="font-size:.82rem">Track and submit your teaching activity each day</p>
    </div>
    <a href="{{ route('daily-reports.create') }}" class="btn btn-primary"
        style="border-radius:8px;font-weight:600;padding:.45rem 1.2rem">
        <i class="fas fa-plus mr-1"></i>Write Today's Report
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid">

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm" style="border-radius:10px">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

{{-- KPI strip --}}
@php
    $totalReports     = $reports->total();
    $submittedCount   = \App\Models\DailyReport::where('teacher_id', auth()->id())->where('status','submitted')->count();
    $draftCount       = \App\Models\DailyReport::where('teacher_id', auth()->id())->where('status','draft')->count();
    $todayReport      = \App\Models\DailyReport::where('teacher_id', auth()->id())->whereDate('report_date', today())->first();
@endphp
<div class="row mb-4" style="gap:0">
    <div class="col-6 col-md-3 mb-3">
        <div class="kpi-mini">
            <div class="num text-primary">{{ $totalReports }}</div>
            <div class="text-muted" style="font-size:.78rem">Total Reports</div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="kpi-mini">
            <div class="num text-success">{{ $submittedCount }}</div>
            <div class="text-muted" style="font-size:.78rem">Submitted</div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="kpi-mini">
            <div class="num text-warning">{{ $draftCount }}</div>
            <div class="text-muted" style="font-size:.78rem">Drafts</div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="kpi-mini">
            @if($todayReport && $todayReport->isSubmitted())
                <div class="num text-success"><i class="fas fa-check-circle" style="font-size:1.4rem"></i></div>
                <div class="text-muted" style="font-size:.78rem">Today Done</div>
            @else
                <div class="num text-danger"><i class="fas fa-times-circle" style="font-size:1.4rem"></i></div>
                <div class="text-muted" style="font-size:.78rem">Today Pending</div>
            @endif
        </div>
    </div>
</div>

{{-- Today alert --}}
@if(!$todayReport)
<div class="d-flex align-items-center justify-content-between p-3 mb-4"
    style="background:linear-gradient(135deg,#4e73df,#224abe);border-radius:12px;color:#fff">
    <div>
        <strong><i class="fas fa-bell mr-2"></i>No report for today — {{ now()->format('l, d F Y') }}</strong>
        <div style="font-size:.82rem;opacity:.85;margin-top:2px">Keep management informed by submitting your daily report.</div>
    </div>
    <a href="{{ route('daily-reports.create') }}" class="btn btn-light btn-sm font-weight-bold ml-3" style="border-radius:8px;white-space:nowrap">
        Write Now
    </a>
</div>
@elseif($todayReport->status === 'draft')
<div class="d-flex align-items-center justify-content-between p-3 mb-4"
    style="background:linear-gradient(135deg,#f6c23e,#dda520);border-radius:12px;color:#fff">
    <div>
        <strong><i class="fas fa-pencil-alt mr-2"></i>Today's report is saved as draft</strong>
        <div style="font-size:.82rem;opacity:.9;margin-top:2px">Submit it to your HOD before the end of the day.</div>
    </div>
    <a href="{{ route('daily-reports.edit', $todayReport) }}" class="btn btn-light btn-sm font-weight-bold ml-3" style="border-radius:8px">
        Complete & Submit
    </a>
</div>
@endif

{{-- Reports grid --}}
@if($reports->isEmpty())
<div class="empty-state">
    <i class="fas fa-clipboard-list d-block"></i>
    <h5 class="text-muted">No reports yet</h5>
    <p class="text-muted mb-3">Start by writing your first daily report.</p>
    <a href="{{ route('daily-reports.create') }}" class="btn btn-primary" style="border-radius:8px">Write First Report</a>
</div>
@else
<div class="row">
    @foreach($reports as $rpt)
    @php
        $isSubmitted = $rpt->isSubmitted();
        $barColor    = $isSubmitted ? '#1cc88a' : '#f6c23e';
        $sessCount   = $sessionCounts[$rpt->report_date->toDateString()] ?? 0;
        $isToday     = $rpt->report_date->isToday();
    @endphp
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card report-card h-100">
            <div class="status-bar" style="background:{{ $barColor }}"></div>
            <div class="card-body pb-2">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="font-weight-bold mb-0" style="font-size:.95rem">
                            {{ $rpt->report_date->format('d F Y') }}
                            @if($isToday)<span class="badge badge-primary ml-1" style="font-size:.6rem">Today</span>@endif
                        </h6>
                        <span class="day-badge text-muted">{{ $rpt->report_date->format('l') }}</span>
                    </div>
                    @if($isSubmitted)
                    <span class="badge badge-success" style="border-radius:20px;padding:.3rem .75rem">
                        <i class="fas fa-check mr-1"></i>Submitted
                    </span>
                    @else
                    <span class="badge badge-warning" style="border-radius:20px;padding:.3rem .75rem">
                        <i class="fas fa-pencil-alt mr-1"></i>Draft
                    </span>
                    @endif
                </div>

                {{-- Summary snippet --}}
                @if($rpt->summary)
                <p class="text-muted mb-2" style="font-size:.8rem;line-height:1.5">
                    {{ Str::limit($rpt->summary, 90) }}
                </p>
                @endif

                {{-- Chips --}}
                <div class="d-flex flex-wrap mb-2" style="gap:.3rem">
                    <span class="badge badge-light border" style="font-size:.72rem">
                        <i class="fas fa-chalkboard mr-1 text-info"></i>{{ $sessCount }} sessions
                    </span>
                    @if($rpt->challenges)
                    <span class="badge badge-light border" style="font-size:.72rem">
                        <i class="fas fa-exclamation-circle mr-1 text-warning"></i>Challenges noted
                    </span>
                    @endif
                    @if($rpt->next_day_plan)
                    <span class="badge badge-light border" style="font-size:.72rem">
                        <i class="fas fa-forward mr-1 text-success"></i>Plan included
                    </span>
                    @endif
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    @if($isSubmitted && $rpt->submitted_at)
                        <i class="fas fa-paper-plane mr-1"></i>{{ $rpt->submitted_at->format('H:i') }}
                    @else
                        <i class="fas fa-clock mr-1"></i>Last saved {{ $rpt->updated_at->diffForHumans() }}
                    @endif
                </small>
                <div style="gap:.3rem;display:flex">
                    <a href="{{ route('daily-reports.show', $rpt) }}"
                        class="btn btn-sm btn-outline-info" style="border-radius:8px;font-size:.78rem">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                    @unless($isSubmitted)
                    <a href="{{ route('daily-reports.edit', $rpt) }}"
                        class="btn btn-sm btn-outline-warning" style="border-radius:8px;font-size:.78rem">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                    @endunless
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($reports->hasPages())
<div class="mt-2">{{ $reports->links() }}</div>
@endif
@endif

</div>
@endsection
