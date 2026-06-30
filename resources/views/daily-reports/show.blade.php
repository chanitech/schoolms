@extends('adminlte::page')
@section('title', 'Daily Report – ' . $dailyReport->report_date->format('d M Y'))

@php $isOwner = auth()->id() === $dailyReport->teacher_id; @endphp

@push('css')
<style>
/* ── Screen styles ──────────────────────────────────── */
.doc-wrapper {
    max-width: 820px; margin: 0 auto;
    background: #fff; border-radius: 14px;
    box-shadow: 0 4px 30px rgba(0,0,0,.1);
    overflow: hidden;
}
.doc-header {
    background: linear-gradient(135deg, #1a3c6e 0%, #2b5ba8 100%);
    color: #fff; padding: 1.5rem 2rem 1rem;
    display: flex; align-items: center; gap: 1.25rem;
}
.school-logo { width: 64px; height: 64px; object-fit: contain; border-radius: 50%; background: #fff; padding: 4px; flex-shrink: 0; }
.school-logo-placeholder { width: 64px; height: 64px; border-radius: 50%; background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.doc-title-band {
    background: #f0f4ff; border-bottom: 2px solid #d0d9f0;
    text-align: center; padding: .6rem 2rem;
    font-weight: 700; font-size: .9rem; letter-spacing: .12em;
    text-transform: uppercase; color: #1a3c6e;
}
.meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
.meta-cell { padding: .7rem 1.5rem; border-right: 1px solid #e8ecf5; border-bottom: 1px solid #e8ecf5; }
.meta-cell:nth-child(2n) { border-right: none; }
.meta-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .07em; color: #7c8db5; font-weight: 600; }
.meta-value { font-size: .88rem; font-weight: 600; color: #1a2d5a; margin-top: 1px; }
.section-block { padding: 1.25rem 2rem; border-bottom: 1px solid #eef0f8; }
.section-block:last-child { border-bottom: none; }
.sec-title {
    display: flex; align-items: center; gap: .5rem;
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .1em; color: #4e73df; margin-bottom: .75rem;
}
.sec-title::after { content: ''; flex: 1; height: 1.5px; background: #e3e6f0; }
.sessions-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.sessions-table th { background: #f0f4ff; color: #1a3c6e; font-weight: 700; padding: .45rem .75rem; text-align: left; font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; }
.sessions-table td { padding: .5rem .75rem; border-bottom: 1px solid #f0f0f8; vertical-align: middle; }
.sessions-table tr:last-child td { border-bottom: none; }
.status-chip { display: inline-block; padding: .2rem .55rem; border-radius: 20px; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.narrative-text { font-size: .88rem; line-height: 1.75; color: #2d3748; white-space: pre-line; }
.empty-field { font-size: .82rem; color: #a0aec0; font-style: italic; }
.sig-block { display: flex; justify-content: space-between; padding: 1.5rem 2rem 1rem; gap: 2rem; }
.sig-line { flex: 1; border-top: 1.5px solid #1a3c6e; padding-top: .4rem; font-size: .78rem; color: #444; text-align: center; }
.stamp-area { width: 90px; height: 90px; border: 1.5px dashed #c0c9e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .65rem; color: #a0aec0; text-align: center; }
.doc-footer-bar { background: #f0f4ff; border-top: 2px solid #d0d9f0; padding: .5rem 2rem; text-align: center; font-size: .7rem; color: #7c8db5; }
.status-submitted-banner { background: #d4edda; border-left: 4px solid #28a745; padding: .6rem 1rem; font-size: .82rem; color: #155724; display: flex; align-items: center; gap: .5rem; }
.status-draft-banner { background: #fff3cd; border-left: 4px solid #ffc107; padding: .6rem 1rem; font-size: .82rem; color: #856404; display: flex; align-items: center; gap: .5rem; }
.activity-chip { display: inline-flex; align-items: center; gap: .35rem; background: #f0f4ff; border: 1px solid #d0d9f0; border-radius: 8px; padding: .3rem .65rem; font-size: .78rem; margin: .2rem; }

/* ── Screen action toolbar (hidden in print) ──────── */
.print-toolbar {
    max-width: 820px; margin: 0 auto 1rem;
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: .4rem;
}
.btn-toolbar-item { border-radius: 10px; font-size: .83rem; font-weight: 600; padding: .4rem 1rem; }

/* ── PRINT STYLES ───────────────────────────────────── */
@media print {
    body { background: #fff !important; }
    .main-header, .main-sidebar, .main-footer,
    .content-header, .print-toolbar,
    .breadcrumb-holder, .alert { display: none !important; }
    .content-wrapper { margin: 0 !important; background: #fff !important; padding: 0 !important; }
    .doc-wrapper {
        box-shadow: none !important; border-radius: 0 !important;
        max-width: 100% !important;
    }
    .doc-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .doc-title-band, .meta-cell, .section-block { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .sessions-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .status-chip { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .doc-footer-bar { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @page { margin: 1cm 1.2cm; size: A4 portrait; }
    .section-block { page-break-inside: avoid; }
}
</style>
@endpush

@section('content')
<div class="container-fluid py-2">

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:10px;max-width:820px;margin:0 auto .75rem">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

{{-- Action toolbar (screen only) --}}
<div class="print-toolbar">
    <div class="d-flex" style="gap:.4rem">
        <a href="{{ route('daily-reports.index') }}" class="btn btn-light btn-toolbar-item">
            <i class="fas fa-arrow-left mr-1"></i>Back
        </a>
        @if($isOwner && !$dailyReport->isSubmitted())
        <a href="{{ route('daily-reports.edit', $dailyReport) }}" class="btn btn-warning btn-toolbar-item">
            <i class="fas fa-edit mr-1"></i>Edit Draft
        </a>
        @endif
    </div>
    <div class="d-flex" style="gap:.4rem">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-toolbar-item">
            <i class="fas fa-print mr-1"></i>Print / Save PDF
        </button>
        @if($isOwner && !$dailyReport->isSubmitted())
        <form action="{{ route('daily-reports.store') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="report_date" value="{{ $dailyReport->report_date->toDateString() }}">
            <input type="hidden" name="summary" value="{{ $dailyReport->summary }}">
            <input type="hidden" name="challenges" value="{{ $dailyReport->challenges }}">
            <input type="hidden" name="next_day_plan" value="{{ $dailyReport->next_day_plan }}">
            <input type="hidden" name="additional_notes" value="{{ $dailyReport->additional_notes }}">
            <button type="submit" name="submit" value="1" class="btn btn-success btn-toolbar-item"
                onclick="return confirm('Submit this report to HOD? You will not be able to edit it after submission.')">
                <i class="fas fa-paper-plane mr-1"></i>Submit to HOD
            </button>
        </form>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     OFFICIAL DOCUMENT
═══════════════════════════════════════════════════ --}}
<div class="doc-wrapper">

    {{-- Status banner --}}
    @if($dailyReport->isSubmitted())
    <div class="status-submitted-banner">
        <i class="fas fa-check-circle"></i>
        <strong>Submitted</strong> — {{ $dailyReport->submitted_at->format('l, d F Y \a\t H:i') }}
    </div>
    @else
    <div class="status-draft-banner">
        <i class="fas fa-pencil-alt"></i>
        <strong>Draft</strong> — Not yet submitted to HOD
    </div>
    @endif

    {{-- Letterhead ──────────────────────────────────────────────── --}}
    <div class="doc-header">
        @if($school && $school->logo)
        <img src="{{ asset('storage/'.$school->logo) }}" alt="Logo" class="school-logo">
        @else
        <div class="school-logo-placeholder"><i class="fas fa-school fa-lg" style="opacity:.7"></i></div>
        @endif
        <div class="flex-grow-1">
            <div style="font-size:.7rem;opacity:.75;letter-spacing:.12em;text-transform:uppercase">Official Report</div>
            <h4 class="mb-0 font-weight-bold" style="font-size:1.15rem;line-height:1.2">
                {{ $school->name ?? 'School Name' }}
            </h4>
            @if($school && $school->motto)
            <div style="font-size:.75rem;opacity:.8;font-style:italic;margin-top:1px">{{ $school->motto }}</div>
            @endif
            @if($school)
            <div style="font-size:.7rem;opacity:.7;margin-top:.3rem">
                @if($school->address)<i class="fas fa-map-marker-alt mr-1"></i>{{ $school->address }}&ensp;@endif
                @if($school->phone)<i class="fas fa-phone mr-1"></i>{{ $school->phone }}&ensp;@endif
                @if($school->email)<i class="fas fa-envelope mr-1"></i>{{ $school->email }}@endif
            </div>
            @endif
        </div>
        <div style="text-align:right;flex-shrink:0;opacity:.85">
            <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:.08em">Report No.</div>
            <div style="font-size:.9rem;font-weight:700">DR-{{ str_pad($dailyReport->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div style="font-size:.65rem;margin-top:.3rem;opacity:.8">{{ $dailyReport->created_at->format('d/m/Y') }}</div>
        </div>
    </div>

    {{-- Document Title ───────────────────────────────────────────── --}}
    <div class="doc-title-band">
        Teacher Daily Activity Report
    </div>

    {{-- Meta grid ───────────────────────────────────────────────── --}}
    <div class="meta-grid">
        <div class="meta-cell">
            <div class="meta-label">Teacher Name</div>
            <div class="meta-value">{{ $dailyReport->teacher->name }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Report Date</div>
            <div class="meta-value">{{ $dailyReport->report_date->format('l, d F Y') }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Department</div>
            <div class="meta-value">{{ $teacherStaff?->department?->name ?? '—' }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Position</div>
            <div class="meta-value">{{ $teacherStaff?->position ?? 'Teacher' }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Total Sessions</div>
            <div class="meta-value">{{ $sessions->count() }} session(s)</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Submission Status</div>
            <div class="meta-value">
                @if($dailyReport->isSubmitted())
                <span style="color:#28a745">&#10003; Submitted — {{ $dailyReport->submitted_at->format('H:i') }}</span>
                @else
                <span style="color:#e6a817">&#9998; Draft (pending submission)</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Sessions Conducted ───────────────────────────────────────── --}}
    <div class="section-block">
        <div class="sec-title"><i class="fas fa-chalkboard-teacher"></i>Sessions Conducted</div>
        @if($sessions->count())
        <table class="sessions-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Time</th>
                    <th>Subject</th>
                    <th>Class</th>
                    <th>Topic Covered</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sessions as $i => $sl)
                @php
                    $chipBg = ['attended'=>'#d4edda','late'=>'#fff3cd','absent'=>'#f8d7da'][$sl->status] ?? '#e2e8f0';
                    $chipColor = ['attended'=>'#155724','late'=>'#856404','absent'=>'#721c24'][$sl->status] ?? '#555';
                @endphp
                <tr>
                    <td style="color:#7c8db5;font-weight:600">{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        @if($sl->period)
                        {{ \Carbon\Carbon::parse($sl->period->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($sl->period->end_time)->format('H:i') }}
                        @else
                        —
                        @endif
                    </td>
                    <td><strong>{{ $sl->subject->name ?? '—' }}</strong></td>
                    <td>{{ $sl->schoolClass->name ?? '—' }}</td>
                    <td>
                        @if($sl->topic)
                        <span style="color:#1a3c6e">{{ $sl->topic->title }}</span>
                        @elseif($sl->notes)
                        <span style="color:#6c757d;font-size:.78rem">{{ Str::limit($sl->notes, 45) }}</span>
                        @else
                        <span style="color:#a0aec0;font-style:italic">Not logged</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-chip" style="background:{{ $chipBg }};color:{{ $chipColor }}">
                            {{ ucfirst($sl->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="empty-field">No sessions recorded for this date.</p>
        @endif
    </div>

    {{-- Extra Activities ─────────────────────────────────────────── --}}
    @if($dailyReport->activities->count())
    <div class="section-block">
        <div class="sec-title"><i class="fas fa-tasks"></i>Extra Activities</div>
        @php
            $actIcons = ['meeting'=>'fas fa-handshake','duty'=>'fas fa-shield-alt','exam_invigilation'=>'fas fa-clipboard-check','training'=>'fas fa-graduation-cap','other'=>'fas fa-ellipsis-h'];
        @endphp
        <table class="sessions-table">
            <thead>
                <tr><th>#</th><th>Type</th><th>Activity</th><th>Time</th><th>Description</th></tr>
            </thead>
            <tbody>
                @foreach($dailyReport->activities as $i => $act)
                <tr>
                    <td style="color:#7c8db5;font-weight:600">{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td style="text-transform:capitalize;font-size:.8rem">{{ str_replace('_',' ',$act->type) }}</td>
                    <td><strong>{{ $act->title }}</strong></td>
                    <td style="font-size:.8rem;white-space:nowrap">
                        @if($act->time_from || $act->time_to)
                        {{ $act->time_from ? \Carbon\Carbon::parse($act->time_from)->format('H:i') : '—' }}
                        @if($act->time_from && $act->time_to) – @endif
                        {{ $act->time_to ? \Carbon\Carbon::parse($act->time_to)->format('H:i') : '' }}
                        @else —
                        @endif
                    </td>
                    <td style="font-size:.8rem;color:#555">{{ $act->description ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Summary ─────────────────────────────────────────────────── --}}
    <div class="section-block">
        <div class="sec-title"><i class="fas fa-align-left"></i>Summary of the Day</div>
        @if($dailyReport->summary)
        <p class="narrative-text">{{ $dailyReport->summary }}</p>
        @else
        <p class="empty-field">No summary provided.</p>
        @endif
    </div>

    {{-- Challenges ───────────────────────────────────────────────── --}}
    <div class="section-block">
        <div class="sec-title"><i class="fas fa-exclamation-triangle"></i>Challenges / Issues Faced</div>
        @if($dailyReport->challenges)
        <p class="narrative-text">{{ $dailyReport->challenges }}</p>
        @else
        <p class="empty-field">No challenges reported.</p>
        @endif
    </div>

    {{-- Next Day Plan ────────────────────────────────────────────── --}}
    <div class="section-block">
        <div class="sec-title"><i class="fas fa-forward"></i>Plan for Tomorrow</div>
        @if($dailyReport->next_day_plan)
        <p class="narrative-text">{{ $dailyReport->next_day_plan }}</p>
        @else
        <p class="empty-field">No plan for tomorrow provided.</p>
        @endif
    </div>

    {{-- Additional Notes ─────────────────────────────────────────── --}}
    @if($dailyReport->additional_notes)
    <div class="section-block">
        <div class="sec-title"><i class="fas fa-sticky-note"></i>Additional Notes</div>
        <p class="narrative-text">{{ $dailyReport->additional_notes }}</p>
    </div>
    @endif

    {{-- Signature Block ──────────────────────────────────────────── --}}
    <div class="section-block" style="border-bottom:none;padding-top:.75rem">
        <div class="sec-title"><i class="fas fa-signature"></i>Signatures</div>
        <div class="sig-block">
            <div style="flex:1;text-align:center">
                <div style="height:48px"></div>
                <div class="sig-line">
                    <strong>{{ $dailyReport->teacher->name }}</strong><br>
                    <span style="color:#7c8db5">Teacher / {{ $teacherStaff?->position ?? 'Subject Teacher' }}</span>
                </div>
            </div>
            <div style="display:flex;align-items:flex-end;padding-bottom:.4rem">
                <div class="stamp-area">Official<br>Stamp</div>
            </div>
            <div style="flex:1;text-align:center">
                <div style="height:48px"></div>
                <div class="sig-line">
                    <strong>{{ $teacherStaff?->department?->head?->first_name ?? 'HOD Name' }} {{ $teacherStaff?->department?->head?->last_name ?? '' }}</strong><br>
                    <span style="color:#7c8db5">Head of Department — {{ $teacherStaff?->department?->name ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Document footer ──────────────────────────────────────────── --}}
    <div class="doc-footer-bar">
        <span>{{ $school->name ?? 'School' }}</span>
        &nbsp;·&nbsp;
        <span>Teacher Daily Report — {{ $dailyReport->report_date->format('d F Y') }}</span>
        &nbsp;·&nbsp;
        <span>Ref: DR-{{ str_pad($dailyReport->id, 5, '0', STR_PAD_LEFT) }}</span>
        &nbsp;·&nbsp;
        <span>Printed: {{ now()->format('d/m/Y H:i') }}</span>
    </div>

</div>{{-- end doc-wrapper --}}
</div>
@endsection
