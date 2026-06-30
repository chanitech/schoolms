@extends('adminlte::page')

@section('title', 'New Timetable')

@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('timetables.index') }}" class="btn btn-secondary btn-sm mr-3">
        <i class="fas fa-arrow-left mr-1"></i>Back
    </a>
    <h1 class="mb-0"><i class="fas fa-calendar-plus text-primary mr-2"></i>New Timetable</h1>
</div>
@stop

@section('content')

<form id="timetableForm" method="POST" action="{{ route('timetables.store') }}">
@csrf

<div class="row">
    {{-- Left column: basic info --}}
    <div class="col-lg-5">
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Basic Info</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}" placeholder="e.g. Form 1–4 Weekly Routine 2024" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Timetable Type <span class="text-danger">*</span></label>
                    <select name="type" id="typeSelect" class="form-control" required>
                        <option value="class" {{ old('type') === 'class' ? 'selected' : '' }}>
                            Class Routine (Daily Schedule)
                        </option>
                        <option value="exam" {{ old('type') === 'exam' ? 'selected' : '' }}>
                            Exam Timetable
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Academic Session <span class="text-danger">*</span></label>
                    <select name="academic_session_id" class="form-control @error('academic_session_id') is-invalid @enderror" required>
                        <option value="">— Select Session —</option>
                        @foreach($sessions as $s)
                        <option value="{{ $s->id }}" {{ old('academic_session_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('academic_session_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Class selection --}}
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chalkboard mr-1"></i>Select Classes</h3></div>
            <div class="card-body">
                <div class="form-group mb-1">
                    <button type="button" class="btn btn-xs btn-secondary mr-1" id="selectAllClasses">Select All</button>
                    <button type="button" class="btn btn-xs btn-secondary" id="clearAllClasses">Clear</button>
                </div>
                <div class="row" id="classCheckboxes">
                    @foreach($classes as $c)
                    <div class="col-6 col-md-4">
                        <div class="custom-control custom-checkbox mb-1">
                            <input type="checkbox" class="custom-control-input class-check"
                                   id="cls_{{ $c->id }}" name="class_ids[]" value="{{ $c->id }}"
                                   {{ in_array($c->id, old('class_ids', [])) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="cls_{{ $c->id }}">{{ $c->name }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
                @error('class_ids')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    {{-- Right column: type-specific settings --}}
    <div class="col-lg-7">

        {{-- CLASS ROUTINE SETTINGS --}}
        <div id="classSettings">
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-cog mr-1"></i>Class Routine Settings</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>School Days</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri'] as $d => $label)
                            <div class="custom-control custom-checkbox mr-3">
                                <input type="checkbox" class="custom-control-input" id="day_{{ $d }}"
                                       name="days[]" value="{{ $d }}"
                                       {{ in_array($d, old('days', [1,2,3,4,5])) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="day_{{ $d }}">{{ $label }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Default Periods Per Week (per subject)</label>
                        <input type="number" name="default_periods_per_week" class="form-control"
                               value="{{ old('default_periods_per_week', 5) }}" min="1" max="10">
                        <small class="text-muted">Applies to all subjects unless overridden below.</small>
                    </div>

                    {{-- Timing Mode --}}
                    <div class="form-group mb-2">
                        <label class="font-weight-bold">Period Timing Mode</label>
                        <div class="d-flex flex-wrap" style="gap:16px">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="timing_auto" name="timing_mode" value="auto"
                                       class="custom-control-input" {{ old('timing_mode','auto') === 'auto' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="timing_auto">
                                    <i class="fas fa-magic mr-1 text-primary"></i><strong>Automatic</strong>
                                    <small class="text-muted d-block">Compute from start time + duration</small>
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="timing_manual" name="timing_mode" value="manual"
                                       class="custom-control-input" {{ old('timing_mode') === 'manual' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="timing_manual">
                                    <i class="fas fa-pencil-alt mr-1 text-warning"></i><strong>Manual</strong>
                                    <small class="text-muted d-block">Set exact start/end per period</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Auto timing fields --}}
                    <div id="autoTimingFields">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>School Start Time</label>
                                    <input type="time" name="school_start_time" class="form-control"
                                           value="{{ old('school_start_time', '07:30') }}">
                                    <small class="text-muted">Time first teaching period begins.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Single Session Duration</label>
                                    <select name="session_duration" class="form-control">
                                        @foreach([35 => '35 min', 40 => '40 min', 45 => '45 min', 50 => '50 min', 60 => '60 min'] as $min => $label)
                                        <option value="{{ $min }}" {{ old('session_duration', 40) == $min ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Duration of one teaching period. Doubles = 2×.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Manual timing table --}}
                    <div id="manualTimingSection" style="display:none">
                        <small class="font-weight-bold text-warning d-block mb-1">
                            <i class="fas fa-pencil-alt mr-1"></i>Set exact start &amp; end time for each period:
                        </small>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-1">
                                <thead class="thead-light">
                                    <tr><th>Period / Break</th><th>Start</th><th>End</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($periods as $p)
                                    <tr class="{{ $p->is_break ? 'table-warning' : '' }}">
                                        <td class="align-middle small">
                                            @if($p->is_break)<i class="fas fa-coffee mr-1 text-warning"></i>@endif
                                            {{ $p->name ?? ($p->is_break ? 'Break' : 'Period '.$p->order_no) }}
                                        </td>
                                        <td>
                                            <input type="time" name="period_times[{{ $p->id }}][start]"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('period_times.'.$p->id.'.start', $p->start_time ? substr($p->start_time,0,5) : '') }}">
                                        </td>
                                        <td>
                                            <input type="time" name="period_times[{{ $p->id }}][end]"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('period_times.'.$p->id.'.end', $p->end_time ? substr($p->end_time,0,5) : '') }}">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>These override the auto-computed times for this timetable only.</small>
                    </div>

                    <div class="alert alert-info py-2 mb-0 mt-2" style="font-size:0.82rem">
                        <i class="fas fa-info-circle mr-1"></i>
                        Period 1 is filled across all days first, then Period 2, etc. Check <strong>Double</strong> on a subject to force back-to-back placement.
                    </div>
                </div>
            </div>

            {{-- SPECIAL SESSIONS --}}
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-star-and-crescent mr-1"></i>Special Sessions</h3>
                    <div class="card-tools">
                        <small class="text-muted">Select sessions to include in the timetable grid</small>
                    </div>
                </div>
                <div class="card-body pb-1">

                    {{-- Preset quick-add buttons --}}
                    <div class="mb-3">
                        <p class="text-muted small mb-1"><i class="fas fa-bolt mr-1"></i>Quick presets:</p>
                        <div class="d-flex flex-wrap" style="gap:4px">
                            @php
                            $presets = [
                                ['name'=>'Assembly',       'type'=>'assembly',   'days'=>[1,2,3,4,5], 'start'=>'07:05','end'=>'07:25','color'=>'secondary'],
                                ['name'=>'Prayer Time',    'type'=>'prayer',     'days'=>[1,2,3,4,5], 'start'=>'13:10','end'=>'13:45','color'=>'info'],
                                ['name'=>'Friday Prayer',  'type'=>'prayer',     'days'=>[5],         'start'=>'12:30','end'=>'14:00','color'=>'info'],
                                ['name'=>'Short Break',    'type'=>'break',      'days'=>[1,2,3,4,5], 'start'=>'09:55','end'=>'10:30','color'=>'warning'],
                                ['name'=>'Long Break',     'type'=>'break',      'days'=>[1,2,3,4,5], 'start'=>'12:00','end'=>'12:35','color'=>'warning'],
                                ['name'=>'Lunch Break',    'type'=>'break',      'days'=>[1,2,3,4,5], 'start'=>'13:10','end'=>'13:45','color'=>'warning'],
                                ['name'=>'Resting Time',   'type'=>'free',       'days'=>[1,2,3,4,5], 'start'=>'13:45','end'=>'14:20','color'=>'secondary'],
                                ['name'=>'SDP + Qur\'an', 'type'=>'sdp',        'days'=>[1,2,3,4,5], 'start'=>'14:20','end'=>'15:00','color'=>'purple'],
                                ['name'=>'Self Study',     'type'=>'self_study', 'days'=>[1,2,3,4,5], 'start'=>'15:00','end'=>'16:00','color'=>'success'],
                                ['name'=>'Sports & Games', 'type'=>'sports',     'days'=>[5],         'start'=>'14:50','end'=>'16:00','color'=>'danger'],
                                ['name'=>'Debate',         'type'=>'debate',     'days'=>[5],         'start'=>'16:00','end'=>'17:00','color'=>'purple'],
                            ];
                            @endphp
                            @foreach($presets as $p)
                            <button type="button" class="btn btn-xs btn-outline-secondary add-preset-btn"
                                    data-preset='@json($p)'>
                                + {{ $p['name'] }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Special sessions list (built by JS) --}}
                    <div id="specialSessionsList"></div>

                    <button type="button" class="btn btn-sm btn-outline-info mb-2" id="addCustomSession">
                        <i class="fas fa-plus mr-1"></i>Add Custom Session
                    </button>

                    <p class="text-muted small mb-0">
                        <i class="fas fa-info-circle mr-1"></i>
                        Special sessions appear as shaded rows in the timetable grid and on the dashboard schedule.
                    </p>
                </div>
            </div>

            {{-- Subject/period-per-week config (loaded via AJAX after class selection) --}}
            <div class="card card-outline card-secondary" id="subjectConfigCard" style="display:none">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-book mr-1"></i>Periods Per Week Override</h3>
                    <div class="card-tools">
                        <small class="text-muted">Optional — override default for specific subjects</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="subjectConfigBody">
                        <div class="text-center text-muted p-3">
                            <i class="fas fa-info-circle mr-1"></i>Select classes above to configure per-subject periods.
                        </div>
                    </div>
                </div>
            </div>
        </div>

            {{-- COMBINATION / OPTIONAL SUBJECTS --}}
            <div class="card card-outline card-warning" id="combinationCard" style="display:none">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-code-branch mr-1"></i>Optional / Combination Groups</h3>
                    <div class="card-tools">
                        <small class="text-muted">Students choose one subject from each group — they run at the same time</small>
                    </div>
                </div>
                <div class="card-body">
                    <div id="comboGroupsList"></div>
                    <button type="button" class="btn btn-sm btn-outline-warning" id="addComboGroup">
                        <i class="fas fa-plus mr-1"></i>Add Combination Group
                    </button>
                    <div class="mt-2 text-muted small">
                        <i class="fas fa-info-circle mr-1"></i>
                        E.g. Biology &amp; Mathematics share 2 periods/week (students pick one). Each also keeps their solo periods if periods/week &gt; shared count.
                    </div>
                </div>
            </div>

        {{-- EXAM TIMETABLE SETTINGS --}}
        <div id="examSettings" style="display:none">
            <div class="card card-outline card-warning">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-file-alt mr-1"></i>Exam Settings</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Exam Dates <span class="text-danger">*</span></label>
                        <textarea name="exam_dates" class="form-control" rows="5"
                                  placeholder="One date per line:&#10;2024-11-04&#10;2024-11-05&#10;2024-11-06">{{ old('exam_dates') }}</textarea>
                        <small class="text-muted">Enter one date per line in YYYY-MM-DD format.</small>
                    </div>

                    <div class="form-group">
                        <label>Exam Duration (minutes)</label>
                        <input type="number" name="exam_duration" class="form-control"
                               value="{{ old('exam_duration', 150) }}" min="30" max="240">
                    </div>

                    <div class="form-group">
                        <label>Invigilators Per Session</label>
                        <input type="number" name="invigilators_per_slot" class="form-control"
                               value="{{ old('invigilators_per_slot', 2) }}" min="1" max="5">
                        <small class="text-muted">System will auto-assign from staff, avoiding conflicts. Subject teachers are excluded from invigilating their own exam.</small>
                    </div>

                    <div class="form-group">
                        <label>Exam Slots Per Day</label>
                        <div id="examSlotsContainer">
                            <div class="exam-slot row no-gutters mb-2">
                                <div class="col mr-1">
                                    <input type="text" name="exam_slots[0][label]" class="form-control form-control-sm"
                                           placeholder="Label" value="Morning">
                                </div>
                                <div class="col mr-1">
                                    <input type="time" name="exam_slots[0][start]" class="form-control form-control-sm" value="08:00">
                                </div>
                                <div class="col mr-1">
                                    <input type="time" name="exam_slots[0][end]" class="form-control form-control-sm" value="10:30">
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-sm btn-danger remove-slot"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <div class="exam-slot row no-gutters mb-2">
                                <div class="col mr-1">
                                    <input type="text" name="exam_slots[1][label]" class="form-control form-control-sm"
                                           placeholder="Label" value="Afternoon">
                                </div>
                                <div class="col mr-1">
                                    <input type="time" name="exam_slots[1][start]" class="form-control form-control-sm" value="14:00">
                                </div>
                                <div class="col mr-1">
                                    <input type="time" name="exam_slots[1][end]" class="form-control form-control-sm" value="16:30">
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-sm btn-danger remove-slot"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="addSlot">
                            <i class="fas fa-plus mr-1"></i>Add Slot
                        </button>
                        <small class="d-block text-muted mt-1">Label · Start · End</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="text-right">
            <a href="{{ route('timetables.index') }}" class="btn btn-secondary mr-2">Cancel</a>
            <button type="submit" class="btn btn-primary" id="generateBtn">
                <i class="fas fa-magic mr-1"></i>Generate Timetable
            </button>
        </div>
    </div>
</div>

</form>

@stop

@section('css')
<style>
.gap-2 { gap: 0.5rem; }
.exam-slot .form-control-sm { font-size: 0.82rem; }
</style>
@stop

@section('js')
<script>
const SUBJECTS_URL = '{{ route("timetables.subjects-by-classes") }}';
const CSRF = '{{ csrf_token() }}';

// Toggle class/exam settings panes
function toggleType() {
    const type = $('#typeSelect').val();
    $('#classSettings').toggle(type === 'class');
    $('#examSettings').toggle(type  === 'exam');
}
$('#typeSelect').on('change', toggleType);
toggleType();

// Toggle auto vs manual period timing
function toggleTimingMode() {
    const isManual = $('input[name="timing_mode"]:checked').val() === 'manual';
    $('#autoTimingFields').toggle(!isManual);
    $('#manualTimingSection').toggle(isManual);
}
$('input[name="timing_mode"]').on('change', toggleTimingMode);
toggleTimingMode();

// Select/clear all class checkboxes
$('#selectAllClasses').on('click', () => $('.class-check').prop('checked', true).trigger('change'));
$('#clearAllClasses').on('click',  () => $('.class-check').prop('checked', false).trigger('change'));

// Load subjects for selected classes (AJAX)
let loadTimeout = null;
$('#classCheckboxes').on('change', '.class-check', function () {
    if ($('#typeSelect').val() !== 'class') return;
    clearTimeout(loadTimeout);
    loadTimeout = setTimeout(loadSubjects, 300);
});

function loadSubjects() {
    const ids = $('.class-check:checked').map((_, el) => el.value).get();
    if (!ids.length) {
        $('#subjectConfigCard').hide();
        return;
    }

    $.ajax({
        url: SUBJECTS_URL,
        data: { class_ids: ids },
        success(rows) {
            if (!rows.length) { $('#subjectConfigCard').hide(); return; }

            // Group by class
            const byClass = {};
            rows.forEach(r => {
                if (!byClass[r.class_id]) byClass[r.class_id] = { name: r.class_name, subjects: [] };
                byClass[r.class_id].subjects.push(r);
            });

            let html = '<table class="table table-sm table-bordered mb-0"><thead class="thead-light"><tr>' +
                '<th>Class</th><th>Subject</th>' +
                '<th>Periods/Week <small class="text-muted font-weight-normal">(blank = default, 0 = exclude)</small></th>' +
                '<th class="text-center" style="width:90px">Double <small class="text-muted d-block" style="font-size:.65rem">back-to-back</small></th>' +
                '</tr></thead><tbody>';

            Object.entries(byClass).forEach(([cid, cls]) => {
                cls.subjects.forEach((s, i) => {
                    const key = `${s.class_id}_${s.subject_id}`;
                    html += `<tr>`;
                    if (i === 0) html += `<td rowspan="${cls.subjects.length}" class="align-middle font-weight-bold">${cls.name}</td>`;
                    html += `<td>${s.subject_name}</td>`;
                    html += `<td style="width:110px">
                        <input type="number" name="periods_per_week[${key}]"
                               class="form-control form-control-sm" min="0" max="10" placeholder="default">
                    </td>`;
                    html += `<td class="text-center align-middle">
                        <input type="checkbox" name="double_periods[${key}]" value="1"
                               title="All periods must be placed as consecutive back-to-back pairs">
                    </td></tr>`;
                });
            });

            html += '</tbody></table>';
            $('#subjectConfigBody').html(html);
            $('#subjectConfigCard').show();

            // Build unique subject options for combo selects (unique by subject_id)
            const seen = {};
            loadedSubjectOpts = rows.reduce((acc, r) => {
                if (!seen[r.subject_id]) {
                    seen[r.subject_id] = true;
                    acc += `<option value="${r.subject_id}">${r.subject_name}</option>`;
                }
                return acc;
            }, '');
            $('#combinationCard').show();

            // Refresh existing combo group selects with updated options
            $('#comboGroupsList .combo-group select').each(function() {
                const selected = $(this).val() || [];
                $(this).html(loadedSubjectOpts);
                $(this).val(selected);
            });
        }
    });
}

// Exam slots: add/remove
let slotIdx = 2;
$('#addSlot').on('click', function () {
    const html = `<div class="exam-slot row no-gutters mb-2">
        <div class="col mr-1"><input type="text" name="exam_slots[${slotIdx}][label]" class="form-control form-control-sm" placeholder="Label"></div>
        <div class="col mr-1"><input type="time" name="exam_slots[${slotIdx}][start]" class="form-control form-control-sm"></div>
        <div class="col mr-1"><input type="time" name="exam_slots[${slotIdx}][end]" class="form-control form-control-sm"></div>
        <div class="col-auto"><button type="button" class="btn btn-sm btn-danger remove-slot"><i class="fas fa-times"></i></button></div>
    </div>`;
    $('#examSlotsContainer').append(html);
    slotIdx++;
});
$(document).on('click', '.remove-slot', function () {
    $(this).closest('.exam-slot').remove();
});

// Generate button: show loading state
$('#timetableForm').on('submit', function () {
    const checked = $('.class-check:checked').length;
    if (!checked) {
        alert('Please select at least one class.');
        return false;
    }
    $('#generateBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i>Generating…').prop('disabled', true);
});

// ── Combination Groups ────────────────────────────────────────────────────
let comboIdx = 0;
let loadedSubjectOpts = ''; // populated after loadSubjects() AJAX

function buildComboGroupRow(idx) {
    // Build class options from currently-checked class checkboxes
    const classOpts = $('.class-check:checked').map((_, el) => {
        const label = $(`label[for="${el.id}"]`).text().trim();
        return `<option value="${el.value}">${label}</option>`;
    }).get().join('');

    return `<div class="combo-group border border-warning rounded p-2 mb-2" data-cidx="${idx}">
        <div class="d-flex align-items-start flex-wrap" style="gap:10px">
            <div>
                <label class="small font-weight-bold mb-1">Applies to Classes</label>
                <select name="combo_class_ids[${idx}][]" class="form-control form-control-sm" multiple size="3" style="min-width:130px">
                    ${classOpts}
                </select>
                <small class="text-muted d-block">Blank = all selected classes</small>
            </div>
            <div>
                <label class="small font-weight-bold mb-1">Subjects sharing this slot</label>
                <select name="combo_subjects[${idx}][]" class="form-control form-control-sm" multiple size="4" style="min-width:180px">
                    ${loadedSubjectOpts}
                </select>
                <small class="text-muted d-block">Hold Ctrl / ⌘ to select multiple</small>
            </div>
            <div>
                <label class="small font-weight-bold mb-1">Shared periods/week</label>
                <input type="number" name="combo_shared[${idx}]" class="form-control form-control-sm"
                       min="1" max="10" value="2" style="width:70px">
                <small class="text-muted d-block">Slots they run together</small>
            </div>
            <div class="ml-auto">
                <button type="button" class="btn btn-xs btn-danger remove-combo mt-3">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>`;
}

$('#addComboGroup').on('click', function () {
    if (!loadedSubjectOpts) { alert('Select classes first to load subjects.'); return; }
    $('#comboGroupsList').append(buildComboGroupRow(comboIdx++));
});

$(document).on('click', '.remove-combo', function () {
    $(this).closest('.combo-group').remove();
});

// ── Special Sessions ──────────────────────────────────────────────────────
let ssIndex = 0;

const DAYS_MAP = {1:'Mon',2:'Tue',3:'Wed',4:'Thu',5:'Fri'};
const TYPE_ICONS = {
    assembly:'fas fa-flag', prayer:'fas fa-star-and-crescent', break:'fas fa-coffee',
    self_study:'fas fa-book-open', sports:'fas fa-running', sdp:'fas fa-quran',
    debate:'fas fa-comments', free:'fas fa-moon'
};
const TYPE_COLORS = {
    assembly:'secondary', prayer:'info', break:'warning',
    self_study:'success', sports:'danger', sdp:'purple', debate:'primary', free:'secondary'
};

function buildSessionRow(idx, data) {
    const days     = data.days || [1,2,3,4,5];
    const color    = data.color || TYPE_COLORS[data.type] || 'secondary';
    const icon     = TYPE_ICONS[data.type || 'free'] || 'fas fa-circle';
    const dayCheckboxes = [1,2,3,4,5].map(d =>
        `<label class="mr-2 small">
            <input type="checkbox" name="special_sessions[${idx}][days][]" value="${d}"
                   ${days.includes(d) ? 'checked' : ''}> ${DAYS_MAP[d]}
         </label>`
    ).join('');

    return `
    <div class="card card-sm mb-2 border-left border-${color} special-session-item" data-idx="${idx}">
        <div class="card-body p-2">
            <div class="row align-items-center no-gutters">
                <div class="col-auto mr-2">
                    <i class="${icon} text-${color}"></i>
                    <input type="hidden" name="special_sessions[${idx}][type]"  value="${data.type  || 'free'}">
                    <input type="hidden" name="special_sessions[${idx}][color]" value="${color}">
                </div>
                <div class="col mr-2">
                    <input type="text" name="special_sessions[${idx}][name]"
                           class="form-control form-control-sm font-weight-bold"
                           value="${data.name || ''}" placeholder="Session name" required>
                </div>
                <div class="col-auto mr-2">
                    <input type="time" name="special_sessions[${idx}][start_time]"
                           class="form-control form-control-sm" value="${data.start || data.start_time || ''}">
                </div>
                <div class="col-auto mr-1">
                    <span class="text-muted">–</span>
                </div>
                <div class="col-auto mr-2">
                    <input type="time" name="special_sessions[${idx}][end_time]"
                           class="form-control form-control-sm" value="${data.end || data.end_time || ''}">
                </div>
                <div class="col-auto mr-2">
                    <button type="button" class="btn btn-xs btn-danger remove-session">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="mt-1 ml-4 pl-1 small text-muted">
                Days: ${dayCheckboxes}
            </div>
        </div>
    </div>`;
}

$('#addCustomSession').on('click', function () {
    $('#specialSessionsList').append(buildSessionRow(ssIndex++, {}));
});

$(document).on('click', '.add-preset-btn', function () {
    const preset = $(this).data('preset');
    // Check if this preset name already added
    const existingNames = $('.special-session-item input[name$="[name]"]').map((_, el) => el.value).get();
    if (existingNames.includes(preset.name)) {
        $(this).addClass('btn-secondary').removeClass('btn-outline-secondary').prop('disabled', true);
        return;
    }
    $('#specialSessionsList').append(buildSessionRow(ssIndex++, preset));
    $(this).addClass('btn-secondary').removeClass('btn-outline-secondary').prop('disabled', true);
});

$(document).on('click', '.remove-session', function () {
    const name = $(this).closest('.special-session-item').find('input[name$="[name]"]').val();
    // Re-enable the preset button if it exists
    $('.add-preset-btn').filter((_, el) => $(el).data('preset')?.name === name)
        .removeClass('btn-secondary').addClass('btn-outline-secondary').prop('disabled', false);
    $(this).closest('.special-session-item').remove();
});
</script>
@stop
