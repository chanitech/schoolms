@extends('adminlte::page')
@section('title', ($existing ? 'Edit' : 'Write') . ' Daily Report')

@push('css')
<style>
.form-card { border: none; border-radius: 14px; box-shadow: 0 2px 16px rgba(0,0,0,.08); overflow: hidden; }
.section-header {
    display: flex; align-items: center; gap: .6rem;
    font-weight: 700; font-size: .82rem; text-transform: uppercase;
    letter-spacing: .08em; color: #4e73df; margin-bottom: .75rem;
}
.section-header::after { content: ''; flex: 1; height: 1px; background: #e3e6f0; }
.session-pill {
    display: flex; align-items: flex-start; gap: .75rem;
    padding: .75rem 1rem; border-bottom: 1px solid #f0f0f5;
    transition: background .1s;
}
.session-pill:hover { background: #f8f9fc; }
.session-pill:last-child { border-bottom: none; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
.textarea-styled {
    border: 1.5px solid #e3e6f0; border-radius: 10px; padding: .85rem 1rem;
    font-size: .9rem; line-height: 1.65; resize: vertical; width: 100%;
    transition: border-color .2s, box-shadow .2s;
}
.textarea-styled:focus {
    outline: none; border-color: #4e73df;
    box-shadow: 0 0 0 3px rgba(78,115,223,.12);
}
.field-label {
    display: flex; align-items: center; gap: .5rem;
    font-weight: 600; font-size: .82rem; color: #555; margin-bottom: .4rem;
}
.field-label .icon { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .65rem; flex-shrink: 0; }
.activity-row { background: #f8f9fc; border-radius: 10px; padding: .75rem; margin-bottom: .5rem; border: 1.5px solid #e3e6f0; }
.btn-action { border-radius: 10px; font-weight: 600; padding: .55rem 1.4rem; }
.date-nav { background: #f8f9fc; border-radius: 10px; border: 1.5px solid #e3e6f0; padding: .4rem .8rem; font-size: .85rem; font-weight: 600; }
</style>
@endpush

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
    <div>
        <h1 class="m-0 font-weight-bold" style="font-size:1.35rem">
            <i class="fas fa-edit mr-2" style="color:#4e73df"></i>
            {{ $existing ? 'Edit' : 'Write' }} Daily Report
        </h1>
        <p class="text-muted mb-0" style="font-size:.82rem">
            <i class="fas fa-calendar-day mr-1"></i>{{ $date->format('l, d F Y') }}
        </p>
    </div>
    <div class="d-flex align-items-center" style="gap:.5rem">
        <div class="d-flex align-items-center" style="gap:.3rem">
            <a href="{{ route('daily-reports.create', ['date' => $date->copy()->subDay()->toDateString()]) }}"
                class="btn btn-sm btn-light" style="border-radius:8px" title="Previous day">
                <i class="fas fa-chevron-left"></i>
            </a>
            <input type="date" id="datePicker" class="date-nav form-control form-control-sm"
                value="{{ $date->toDateString() }}" style="width:145px">
            <a href="{{ route('daily-reports.create', ['date' => $date->copy()->addDay()->toDateString()]) }}"
                class="btn btn-sm btn-light" style="border-radius:8px" title="Next day">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <a href="{{ route('daily-reports.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px">
            <i class="fas fa-list mr-1"></i>My Reports
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

@if($errors->any())
<div class="alert alert-danger border-0 mb-3" style="border-radius:10px;box-shadow:0 2px 8px rgba(220,53,69,.2)">
    <i class="fas fa-exclamation-circle mr-2"></i>
    <strong>Please fix the following:</strong>
    <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ route('daily-reports.store') }}" method="POST" id="reportForm">
@csrf
<input type="hidden" name="report_date" value="{{ $date->toDateString() }}">

<div class="row">

{{-- ─── LEFT COLUMN ──────────────────────────────────────────────── --}}
<div class="col-lg-4 mb-3">

    {{-- Sessions Panel --}}
    <div class="form-card mb-3">
        <div class="card-body p-0">
            <div class="px-3 pt-3 pb-2">
                <div class="section-header">
                    <i class="fas fa-chalkboard-teacher" style="color:#36b9cc"></i>
                    Sessions Today
                    <span class="badge badge-info ml-auto" style="font-size:.68rem;border-radius:20px;text-transform:none;letter-spacing:0">{{ $sessions->count() }}</span>
                </div>
            </div>
            @forelse($sessions as $sl)
            @php
                $dotColor = ['attended'=>'#1cc88a','late'=>'#f6c23e','absent'=>'#e74a3b'][$sl->status] ?? '#6c757d';
                $textColor = ['attended'=>'text-success','late'=>'text-warning','absent'=>'text-danger'][$sl->status] ?? 'text-muted';
            @endphp
            <div class="session-pill">
                <div class="status-dot" style="background:{{ $dotColor }}"></div>
                <div class="flex-grow-1" style="font-size:.82rem">
                    <strong>{{ $sl->subject->name ?? '—' }}</strong>
                    <span class="text-muted"> · {{ $sl->schoolClass->name ?? '—' }}</span>
                    @if($sl->period)
                    <span class="text-muted" style="font-size:.72rem">
                        · {{ \Carbon\Carbon::parse($sl->period->start_time)->format('H:i') }}
                    </span>
                    @endif
                    @if($sl->topic)
                    <div class="text-muted mt-1" style="font-size:.72rem">
                        <i class="fas fa-bookmark mr-1 text-primary"></i>{{ Str::limit($sl->topic->title, 40) }}
                    </div>
                    @endif
                    <span class="{{ $textColor }}" style="font-size:.7rem;text-transform:capitalize">{{ $sl->status }}</span>
                </div>
            </div>
            @empty
            <div class="text-center py-4" style="color:#b0bec5">
                <i class="fas fa-calendar-times fa-2x d-block mb-2" style="opacity:.4"></i>
                <span style="font-size:.82rem">No sessions for this date.</span>
                <div class="mt-2">
                    <a href="{{ route('timetables.my-sessions') }}" class="btn btn-xs btn-outline-info" style="border-radius:6px">
                        Log Sessions →
                    </a>
                </div>
            </div>
            @endforelse

            @if($sessions->count())
            <div class="px-3 pb-3 pt-2">
                <a href="{{ route('timetables.my-sessions') }}" class="btn btn-xs btn-outline-secondary w-100" style="border-radius:8px;font-size:.77rem">
                    <i class="fas fa-plus mr-1"></i>Update Session Logs
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Extra Activities Panel --}}
    <div class="form-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="section-header mb-0" style="flex:1">
                    <i class="fas fa-tasks" style="color:#858796"></i>
                    Extra Activities
                </div>
                <button type="button" id="addActivity"
                    class="btn btn-xs btn-primary ml-2" style="border-radius:8px;flex-shrink:0">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div id="activitiesContainer">
                @if($existing && $existing->activities->count())
                @foreach($existing->activities as $i => $act)
                <div class="activity-row">
                    <div class="row no-gutters" style="gap:.3rem 0">
                        <div class="col-7 pr-1">
                            <select name="activities[{{ $i }}][type]" class="form-control form-control-sm" style="border-radius:7px">
                                @foreach(['meeting'=>'Meeting','duty'=>'Duty','exam_invigilation'=>'Exam Invigilation','training'=>'Training','other'=>'Other'] as $val => $lbl)
                                <option value="{{ $val }}" {{ $act->type===$val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-5 pl-1 text-right">
                            <button type="button" class="btn btn-xs btn-outline-danger remove-activity" style="border-radius:7px">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="col-12 mt-1">
                            <input type="text" name="activities[{{ $i }}][title]" class="form-control form-control-sm"
                                placeholder="Activity title" value="{{ $act->title }}" style="border-radius:7px">
                        </div>
                        <div class="col-6 pr-1 mt-1">
                            <input type="time" name="activities[{{ $i }}][time_from]" class="form-control form-control-sm"
                                value="{{ $act->time_from }}" style="border-radius:7px" placeholder="From">
                        </div>
                        <div class="col-6 pl-1 mt-1">
                            <input type="time" name="activities[{{ $i }}][time_to]" class="form-control form-control-sm"
                                value="{{ $act->time_to }}" style="border-radius:7px" placeholder="To">
                        </div>
                        <div class="col-12 mt-1">
                            <input type="text" name="activities[{{ $i }}][description]" class="form-control form-control-sm"
                                placeholder="Brief description (optional)" value="{{ $act->description }}" style="border-radius:7px">
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <div id="noActivity" class="text-center py-3" style="color:#b0bec5;font-size:.82rem">
                    <i class="fas fa-plus-circle d-block mb-1" style="font-size:1.5rem;opacity:.3"></i>
                    No extra activities. Click + to add.
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ─── RIGHT COLUMN ─────────────────────────────────────────────── --}}
<div class="col-lg-8 mb-3">
    <div class="form-card">
        <div style="background:linear-gradient(135deg,#4e73df,#224abe);padding:1.25rem 1.5rem;color:#fff">
            <h5 class="mb-1 font-weight-bold"><i class="fas fa-file-alt mr-2"></i>Report Narrative</h5>
            <p class="mb-0" style="font-size:.82rem;opacity:.85">Complete all sections for a comprehensive report</p>
        </div>
        <div class="card-body" style="padding:1.5rem">

            {{-- Completion progress --}}
            @php
                $filled = ($existing ? (
                    ($existing->summary ? 1 : 0) +
                    ($existing->challenges ? 1 : 0) +
                    ($existing->next_day_plan ? 1 : 0)
                ) : 0);
                $pct = round($filled / 3 * 100);
            @endphp
            <div class="mb-4">
                <div class="d-flex justify-content-between" style="font-size:.78rem;color:#777;margin-bottom:.3rem">
                    <span>Report completion</span>
                    <span>{{ $pct }}%</span>
                </div>
                <div class="progress" style="height:5px;border-radius:3px">
                    <div class="progress-bar" style="width:{{ $pct }}%;background:#4e73df;border-radius:3px"></div>
                </div>
            </div>

            {{-- Summary --}}
            <div class="mb-4">
                <div class="field-label">
                    <span class="icon" style="background:#e8f4fd;color:#4e73df"><i class="fas fa-align-left"></i></span>
                    Summary of the Day
                    <span class="text-danger ml-1">*</span>
                </div>
                <textarea name="summary" class="textarea-styled @error('summary') border-danger @enderror"
                    rows="5" id="summaryField"
                    placeholder="Describe what happened today — lessons taught, topics covered, student engagement, key moments, attendance observations...">{{ old('summary', $existing->summary ?? '') }}</textarea>
                <div class="d-flex justify-content-between mt-1">
                    @error('summary')<span class="text-danger" style="font-size:.78rem">{{ $message }}</span>@else<span></span>@enderror
                    <span id="summaryCount" style="font-size:.72rem;color:#aaa">0 words</span>
                </div>
            </div>

            {{-- Challenges --}}
            <div class="mb-4">
                <div class="field-label">
                    <span class="icon" style="background:#fff8e1;color:#f6c23e"><i class="fas fa-exclamation-triangle"></i></span>
                    Challenges / Issues Faced
                </div>
                <textarea name="challenges" class="textarea-styled" rows="3"
                    placeholder="Any difficulties — teaching challenges, student behavior, missing resources, absenteeism, infrastructure issues...">{{ old('challenges', $existing->challenges ?? '') }}</textarea>
            </div>

            {{-- Next Day Plan --}}
            <div class="mb-4">
                <div class="field-label">
                    <span class="icon" style="background:#e8f8ef;color:#1cc88a"><i class="fas fa-forward"></i></span>
                    Plan for Tomorrow
                </div>
                <textarea name="next_day_plan" class="textarea-styled" rows="3"
                    placeholder="What do you plan for tomorrow? Topics to cover, assessments to give, activities planned...">{{ old('next_day_plan', $existing->next_day_plan ?? '') }}</textarea>
            </div>

            {{-- Additional Notes --}}
            <div class="mb-0">
                <div class="field-label">
                    <span class="icon" style="background:#f0f0f0;color:#858796"><i class="fas fa-sticky-note"></i></span>
                    Additional Notes
                    <span style="font-size:.72rem;color:#aaa;font-weight:400">(optional)</span>
                </div>
                <textarea name="additional_notes" class="textarea-styled" rows="2"
                    placeholder="Any other information for HOD or management...">{{ old('additional_notes', $existing->additional_notes ?? '') }}</textarea>
            </div>

        </div>
        <div class="card-footer border-0 d-flex justify-content-between align-items-center"
            style="background:#f8f9fc;padding:1rem 1.5rem;border-top:1.5px solid #e3e6f0">
            <div class="d-flex" style="gap:.5rem">
                <button type="submit" name="draft" class="btn btn-action btn-secondary" style="font-size:.85rem">
                    <i class="fas fa-save mr-1"></i>Save Draft
                </button>
                <a href="{{ route('daily-reports.index') }}" class="btn btn-action btn-light" style="font-size:.85rem">Cancel</a>
            </div>
            <button type="submit" name="submit" value="1"
                class="btn btn-action btn-success" style="font-size:.88rem"
                onclick="return confirm('Submit this report to your HOD?\n\nYou will not be able to edit it after submission.')">
                <i class="fas fa-paper-plane mr-1"></i>Submit to HOD
            </button>
        </div>
    </div>
</div>

</div>
</form>

</div>
@endsection

@push('js')
<script>
// Date picker navigation
document.getElementById('datePicker').addEventListener('change', function () {
    window.location = '{{ route("daily-reports.create") }}?date=' + this.value;
});

// Word count for summary
const summaryField = document.getElementById('summaryField');
const summaryCount = document.getElementById('summaryCount');
function updateWordCount() {
    const words = summaryField.value.trim().split(/\s+/).filter(w => w.length > 0);
    summaryCount.textContent = words.length + ' words';
}
summaryField.addEventListener('input', updateWordCount);
updateWordCount();

// Completion bar updater
const textareas = document.querySelectorAll('[name="summary"], [name="challenges"], [name="next_day_plan"]');
const progressBar = document.querySelector('.progress-bar');
function updateProgress() {
    let filled = 0;
    textareas.forEach(t => { if (t.value.trim().length > 0) filled++; });
    const pct = Math.round(filled / 3 * 100);
    progressBar.style.width = pct + '%';
}
textareas.forEach(t => t.addEventListener('input', updateProgress));

// Activity rows
let actIdx = {{ ($existing && $existing->activities) ? $existing->activities->count() : 0 }};
const typeOptions = [
    ['meeting','Meeting'],['duty','Duty'],
    ['exam_invigilation','Exam Invigilation'],
    ['training','Training'],['other','Other']
].map(([v,l]) => `<option value="${v}">${l}</option>`).join('');

function makeRow(i) {
    return `<div class="activity-row">
        <div class="row no-gutters" style="gap:.3rem 0">
            <div class="col-7 pr-1">
                <select name="activities[${i}][type]" class="form-control form-control-sm" style="border-radius:7px">${typeOptions}</select>
            </div>
            <div class="col-5 pl-1 text-right">
                <button type="button" class="btn btn-xs btn-outline-danger remove-activity" style="border-radius:7px"><i class="fas fa-times"></i></button>
            </div>
            <div class="col-12 mt-1">
                <input type="text" name="activities[${i}][title]" class="form-control form-control-sm" placeholder="Activity title" required style="border-radius:7px">
            </div>
            <div class="col-6 pr-1 mt-1">
                <input type="time" name="activities[${i}][time_from]" class="form-control form-control-sm" style="border-radius:7px">
            </div>
            <div class="col-6 pl-1 mt-1">
                <input type="time" name="activities[${i}][time_to]" class="form-control form-control-sm" style="border-radius:7px">
            </div>
            <div class="col-12 mt-1">
                <input type="text" name="activities[${i}][description]" class="form-control form-control-sm" placeholder="Brief description" style="border-radius:7px">
            </div>
        </div>
    </div>`;
}

document.getElementById('addActivity').addEventListener('click', function () {
    const noAct = document.getElementById('noActivity');
    if (noAct) noAct.remove();
    document.getElementById('activitiesContainer').insertAdjacentHTML('beforeend', makeRow(actIdx++));
});

document.getElementById('activitiesContainer').addEventListener('click', function (e) {
    const btn = e.target.closest('.remove-activity');
    if (btn) btn.closest('.activity-row').remove();
});
</script>
@endpush
