@extends('adminlte::page')
@section('title', 'Staff Daily Reports')

@push('css')
<style>
.kpi-box {
    border-radius: 14px; border: none; overflow: hidden;
    box-shadow: 0 2px 14px rgba(0,0,0,.07);
}
.kpi-box .inner { padding: 1.1rem 1.3rem; }
.kpi-box .num { font-size: 2rem; font-weight: 800; line-height: 1.1; }
.kpi-box .lbl { font-size: .75rem; text-transform: uppercase; letter-spacing: .07em; opacity: .8; margin-top: 2px; }
.kpi-box .foot { background: rgba(0,0,0,.08); padding: .4rem 1.3rem; font-size: .75rem; }

.report-table { border-collapse: collapse; width: 100%; }
.report-table thead th {
    background: #f0f4ff; color: #1a3c6e; font-weight: 700;
    font-size: .72rem; text-transform: uppercase; letter-spacing: .06em;
    padding: .65rem 1rem; border-bottom: 2px solid #d0d9f0;
}
.report-table tbody td { padding: .6rem 1rem; border-bottom: 1px solid #f0f0f8; font-size: .84rem; vertical-align: middle; }
.report-table tbody tr:hover { background: #f8f9fc; }
.report-table tbody tr:last-child td { border-bottom: none; }

.missing-pill { display: inline-flex; align-items: center; gap: .3rem; background: #fff3cd; border: 1px solid #ffc107; color: #856404; border-radius: 20px; padding: .2rem .65rem; font-size: .75rem; font-weight: 600; }
.filter-card { border-radius: 12px; border: 1.5px solid #e3e6f0; box-shadow: none; margin-bottom: 1.2rem; }
.filter-card .card-body { padding: .85rem 1.2rem; }
.table-card { border-radius: 12px; border: 1.5px solid #e3e6f0; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
.table-card .card-header { background: #fff; border-bottom: 2px solid #e3e6f0; padding: .85rem 1.2rem; font-weight: 700; font-size: .9rem; color: #1a3c6e; }
.flag-icon { font-size: .85rem; }
.btn-view { border-radius: 8px; font-size: .78rem; font-weight: 600; }
</style>
@endpush

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
    <div>
        <h1 class="m-0 font-weight-bold" style="font-size:1.4rem">
            <i class="fas fa-clipboard-check mr-2" style="color:#4e73df"></i>Staff Daily Reports
        </h1>
        <p class="text-muted mb-0" style="font-size:.82rem">
            Monitor your department's daily reporting compliance
        </p>
    </div>
    <a href="{{ route('daily-reports.create') }}" class="btn btn-primary"
        style="border-radius:10px;font-weight:600;padding:.45rem 1.2rem">
        <i class="fas fa-edit mr-1"></i>Write My Report
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid">

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:10px">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

{{-- KPI Row ──────────────────────────────────────────────────────────────── --}}
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="kpi-box" style="background:linear-gradient(135deg,#1cc88a,#169a6a);color:#fff">
            <div class="inner">
                <div class="num">{{ $todaySubmitted }}</div>
                <div class="lbl">Submitted Today</div>
                <div class="mt-2" style="font-size:.78rem;opacity:.85">
                    of {{ $teachers->count() }} staff members
                </div>
            </div>
            <div class="foot text-white">
                <i class="fas fa-check-circle mr-1"></i>{{ now()->format('d F Y') }}
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="kpi-box" style="background:linear-gradient(135deg,#f6c23e,#c69500);color:#fff">
            <div class="inner">
                <div class="num">{{ $pendingDrafts }}</div>
                <div class="lbl">Saved as Draft</div>
                <div class="mt-2" style="font-size:.78rem;opacity:.85">
                    not yet submitted to HOD
                </div>
            </div>
            <div class="foot text-white">
                <i class="fas fa-pencil-alt mr-1"></i>Pending submission
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        @php $notReported = max(0, $teachers->count() - $todaySubmitted); @endphp
        <div class="kpi-box" style="background:linear-gradient(135deg,#e74a3b,#b52b1e);color:#fff">
            <div class="inner">
                <div class="num">{{ $notReported }}</div>
                <div class="lbl">Not Reported Today</div>
                <div class="mt-2" style="font-size:.78rem;opacity:.85">
                    no report submitted or saved
                </div>
            </div>
            <div class="foot text-white">
                <i class="fas fa-times-circle mr-1"></i>Action may be needed
            </div>
        </div>
    </div>
</div>

{{-- Missing staff alert ──────────────────────────────────────────────────── --}}
@if($missingTeachers->count())
<div class="p-3 mb-4" style="background:#fff8e1;border:1.5px solid #ffc107;border-radius:12px">
    <div class="d-flex align-items-center mb-2" style="gap:.5rem">
        <i class="fas fa-exclamation-triangle text-warning"></i>
        <strong style="font-size:.85rem;color:#856404">Staff who have not submitted today's report:</strong>
    </div>
    <div class="d-flex flex-wrap" style="gap:.4rem">
        @foreach($missingTeachers as $t)
        <span class="missing-pill"><i class="fas fa-user"></i>{{ $t->name }}</span>
        @endforeach
    </div>
</div>
@else
<div class="p-3 mb-4" style="background:#d4edda;border:1.5px solid #28a745;border-radius:12px;display:flex;align-items:center;gap:.6rem">
    <i class="fas fa-check-circle text-success fa-lg"></i>
    <strong style="color:#155724">All staff have submitted today's report.</strong>
</div>
@endif

{{-- Filters ─────────────────────────────────────────────────────────────── --}}
<div class="filter-card card">
    <div class="card-body">
        <form method="GET" class="form-inline flex-wrap align-items-center" style="gap:.5rem">
            <div class="input-group input-group-sm" style="width:145px">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="border-radius:8px 0 0 8px"><i class="fas fa-calendar"></i></span>
                </div>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}" style="border-radius:0 8px 8px 0">
            </div>
            <select name="teacher" class="form-control form-control-sm" style="border-radius:8px;min-width:180px">
                <option value="">All Teachers</option>
                @foreach($teachers as $t)
                <option value="{{ $t->id }}" {{ request('teacher')==$t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control form-control-sm" style="border-radius:8px;width:130px">
                <option value="">All Status</option>
                <option value="submitted" {{ request('status')=='submitted' ? 'selected' : '' }}>✓ Submitted</option>
                <option value="draft"     {{ request('status')=='draft' ? 'selected' : '' }}>✎ Draft</option>
            </select>
            <button class="btn btn-primary btn-sm" style="border-radius:8px">
                <i class="fas fa-filter mr-1"></i>Filter
            </button>
            <a href="{{ route('daily-reports.hod') }}" class="btn btn-light btn-sm" style="border-radius:8px">
                <i class="fas fa-times mr-1"></i>Clear
            </a>
        </form>
    </div>
</div>

{{-- Reports Table ─────────────────────────────────────────────────────────── --}}
<div class="table-card card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list mr-2"></i>All Reports</span>
        <span class="text-muted" style="font-size:.8rem">{{ $reports->total() }} total</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Teacher</th>
                        <th>Status</th>
                        <th>Sessions</th>
                        <th>Summary</th>
                        <th style="text-align:center">Challenges</th>
                        <th style="text-align:center">Has Plan</th>
                        <th>Submitted</th>
                        <th width="60"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $rpt)
                    @php
                        $sessCount = $sessionCounts[$rpt->teacher_id . '_' . $rpt->report_date->toDateString()]->cnt ?? 0;
                    @endphp
                    <tr>
                        <td>
                            <strong style="color:#1a3c6e">{{ $rpt->report_date->format('d M Y') }}</strong>
                            <div style="font-size:.72rem;color:#aaa">{{ $rpt->report_date->format('l') }}</div>
                        </td>
                        <td>
                            <div style="font-weight:600;font-size:.85rem">{{ $rpt->teacher->name ?? '—' }}</div>
                        </td>
                        <td>
                            @if($rpt->status === 'submitted')
                            <span style="display:inline-flex;align-items:center;gap:.3rem;background:#d4edda;color:#155724;padding:.25rem .65rem;border-radius:20px;font-size:.72rem;font-weight:700">
                                <i class="fas fa-check-circle"></i>Submitted
                            </span>
                            @else
                            <span style="display:inline-flex;align-items:center;gap:.3rem;background:#fff3cd;color:#856404;padding:.25rem .65rem;border-radius:20px;font-size:.72rem;font-weight:700">
                                <i class="fas fa-pencil-alt"></i>Draft
                            </span>
                            @endif
                        </td>
                        <td>
                            <span style="background:#e8f4fd;color:#1a7ab5;padding:.2rem .6rem;border-radius:20px;font-size:.75rem;font-weight:600">
                                {{ $sessCount }}
                            </span>
                        </td>
                        <td style="max-width:200px">
                            @if($rpt->summary)
                            <span style="font-size:.78rem;color:#555;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px" title="{{ $rpt->summary }}">
                                {{ Str::limit($rpt->summary, 55) }}
                            </span>
                            @else
                            <span style="font-size:.75rem;color:#ccc;font-style:italic">—</span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            @if($rpt->challenges)
                            <i class="fas fa-exclamation-circle flag-icon text-warning" title="{{ Str::limit($rpt->challenges, 80) }}"></i>
                            @else
                            <span style="color:#ddd">—</span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            @if($rpt->next_day_plan)
                            <i class="fas fa-check flag-icon text-success" title="{{ Str::limit($rpt->next_day_plan, 80) }}"></i>
                            @else
                            <span style="color:#ddd">—</span>
                            @endif
                        </td>
                        <td style="font-size:.78rem;color:#777">
                            {{ $rpt->submitted_at ? $rpt->submitted_at->format('H:i') : '—' }}
                        </td>
                        <td>
                            <a href="{{ route('daily-reports.show', $rpt) }}" class="btn btn-sm btn-outline-primary btn-view">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5" style="color:#b0bec5">
                            <i class="fas fa-clipboard fa-2x d-block mb-2" style="opacity:.3"></i>
                            No reports found for the selected filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($reports->hasPages())
    <div class="card-footer bg-white border-top" style="border-top:1.5px solid #e3e6f0">
        {{ $reports->links() }}
    </div>
    @endif
</div>

</div>
@endsection
