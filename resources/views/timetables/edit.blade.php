@extends('adminlte::page')

@section('title', 'Edit — ' . $timetable->title)

@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('timetables.show', $timetable) }}" class="btn btn-secondary btn-sm mr-3">
        <i class="fas fa-arrow-left mr-1"></i>Back
    </a>
    <div>
        <h1 class="mb-0"><i class="fas fa-edit text-warning mr-2"></i>Edit Timetable</h1>
        <small class="text-muted">{{ $timetable->title }}</small>
    </div>
</div>
@stop

@section('content')

@if($timetable->type === 'class')
    @php $s = $timetable->settings ?? []; @endphp
@endif

<form id="timetableForm" method="POST" action="{{ route('timetables.update', $timetable) }}">
@csrf
@method('PUT')

<div class="row">
    {{-- Left column --}}
    <div class="col-lg-5">
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Basic Info</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $timetable->title) }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Type</label>
                    <div>
                        @if($timetable->type === 'class')
                            <span class="badge badge-info px-3 py-2"><i class="fas fa-chalkboard mr-1"></i>Class Routine</span>
                        @else
                            <span class="badge badge-warning px-3 py-2"><i class="fas fa-file-alt mr-1"></i>Exam Timetable</span>
                        @endif
                        <small class="text-muted d-block mt-1">Type cannot be changed after creation.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $timetable->notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Class selection --}}
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chalkboard mr-1"></i>Classes</h3></div>
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
                                   {{ in_array($c->id, old('class_ids', $timetable->class_ids)) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="cls_{{ $c->id }}">{{ $c->name }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
                @error('class_ids')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div class="col-lg-7">

        @if($timetable->type === 'class')
        {{-- CLASS ROUTINE SETTINGS --}}
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
                                   {{ in_array($d, old('days', $s['days'] ?? [1,2,3,4,5])) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="day_{{ $d }}">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label>Default Periods Per Week (per subject)</label>
                    <input type="number" name="default_periods_per_week" class="form-control"
                           value="{{ old('default_periods_per_week', $s['default_periods_per_week'] ?? 5) }}" min="1" max="10">
                    <small class="text-muted">Applies to all subjects unless overridden below.</small>
                </div>

                {{-- Timing Mode --}}
                @php $existingTimingMode = old('timing_mode', $s['timing_mode'] ?? 'auto'); @endphp
                <div class="form-group mb-2">
                    <label class="font-weight-bold">Period Timing Mode</label>
                    <div class="d-flex flex-wrap" style="gap:16px">
                        <div class="custom-control custom-radio">
                            <input type="radio" id="timing_auto" name="timing_mode" value="auto"
                                   class="custom-control-input" {{ $existingTimingMode === 'auto' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="timing_auto">
                                <i class="fas fa-magic mr-1 text-primary"></i><strong>Automatic</strong>
                                <small class="text-muted d-block">Compute from start time + duration</small>
                            </label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="timing_manual" name="timing_mode" value="manual"
                                   class="custom-control-input" {{ $existingTimingMode === 'manual' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="timing_manual">
                                <i class="fas fa-pencil-alt mr-1 text-warning"></i><strong>Manual</strong>
                                <small class="text-muted d-block">Set exact start/end per period</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="autoTimingFields">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>School Start Time</label>
                                <input type="time" name="school_start_time" class="form-control"
                                       value="{{ old('school_start_time', $s['school_start_time'] ?? '07:30') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Single Session Duration</label>
                                <select name="session_duration" class="form-control">
                                    @foreach([35 => '35 min', 40 => '40 min', 45 => '45 min', 50 => '50 min', 60 => '60 min'] as $min => $lbl)
                                    <option value="{{ $min }}" {{ old('session_duration', $s['session_duration'] ?? 40) == $min ? 'selected' : '' }}>
                                        {{ $lbl }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Manual period time table --}}
                <div id="manualTimingSection" style="{{ $existingTimingMode === 'manual' ? '' : 'display:none' }}">
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
                                @php
                                    $manualStart = old('period_times.'.$p->id.'.start',
                                        $s['period_times'][$p->id]['start'] ?? ($p->start_time ? substr($p->start_time,0,5) : ''));
                                    $manualEnd   = old('period_times.'.$p->id.'.end',
                                        $s['period_times'][$p->id]['end']   ?? ($p->end_time ? substr($p->end_time,0,5) : ''));
                                @endphp
                                <tr class="{{ $p->is_break ? 'table-warning' : '' }}">
                                    <td class="align-middle small">
                                        @if($p->is_break)<i class="fas fa-coffee mr-1 text-warning"></i>@endif
                                        {{ $p->name ?? ($p->is_break ? 'Break' : 'Period '.$p->order_no) }}
                                    </td>
                                    <td>
                                        <input type="time" name="period_times[{{ $p->id }}][start]"
                                               class="form-control form-control-sm" value="{{ $manualStart }}">
                                    </td>
                                    <td>
                                        <input type="time" name="period_times[{{ $p->id }}][end]"
                                               class="form-control form-control-sm" value="{{ $manualEnd }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>These override auto-computed times for this timetable only.</small>
                </div>
            </div>
        </div>

        {{-- SPECIAL SESSIONS --}}
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-star-and-crescent mr-1"></i>Special Sessions</h3>
            </div>
            <div class="card-body pb-1">
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
                <div id="specialSessionsList"></div>
                <button type="button" class="btn btn-sm btn-outline-info mb-2" id="addCustomSession">
                    <i class="fas fa-plus mr-1"></i>Add Custom Session
                </button>
            </div>
        </div>

        {{-- Subject override --}}
        <div class="card card-outline card-secondary" id="subjectConfigCard">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-book mr-1"></i>Periods Per Week Override</h3>
                <div class="card-tools">
                    <small class="text-muted">blank = default, 0 = exclude</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="subjectConfigBody">
                    <div class="text-center text-muted p-3">
                        <i class="fas fa-spinner fa-spin mr-1"></i>Loading subjects…
                    </div>
                </div>
            </div>
        </div>

        {{-- COMBINATION GROUPS --}}
        <div class="card card-outline card-warning" id="combinationCard">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-code-branch mr-1"></i>Optional / Combination Groups</h3>
                <div class="card-tools">
                    <small class="text-muted">Students choose one subject — they run at the same time</small>
                </div>
            </div>
            <div class="card-body">
                <div id="comboGroupsList"></div>
                <button type="button" class="btn btn-sm btn-outline-warning" id="addComboGroup">
                    <i class="fas fa-plus mr-1"></i>Add Combination Group
                </button>
            </div>
        </div>

        @else
        {{-- EXAM SETTINGS --}}
        <div class="card card-outline card-warning">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-file-alt mr-1"></i>Exam Settings</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Exam Dates</label>
                    <textarea name="exam_dates" class="form-control" rows="5">{{ old('exam_dates', implode("\n", $timetable->settings['exam_dates'] ?? [])) }}</textarea>
                    <small class="text-muted">One date per line — YYYY-MM-DD</small>
                </div>
                <div class="form-group">
                    <label>Exam Duration (minutes)</label>
                    <input type="number" name="exam_duration" class="form-control"
                           value="{{ old('exam_duration', $timetable->settings['exam_duration'] ?? 150) }}" min="30" max="240">
                </div>
                <div class="form-group">
                    <label>Invigilators Per Session</label>
                    <input type="number" name="invigilators_per_slot" class="form-control"
                           value="{{ old('invigilators_per_slot', $timetable->settings['invigilators_per_slot'] ?? 2) }}" min="1" max="5">
                </div>
                <div class="form-group">
                    <label>Exam Slots Per Day</label>
                    <div id="examSlotsContainer">
                        @foreach($timetable->settings['exam_slots'] ?? [] as $i => $slot)
                        <div class="exam-slot row no-gutters mb-2">
                            <div class="col mr-1"><input type="text" name="exam_slots[{{ $i }}][label]" class="form-control form-control-sm" value="{{ $slot['label'] ?? '' }}" placeholder="Label"></div>
                            <div class="col mr-1"><input type="time" name="exam_slots[{{ $i }}][start]" class="form-control form-control-sm" value="{{ $slot['start'] ?? '' }}"></div>
                            <div class="col mr-1"><input type="time" name="exam_slots[{{ $i }}][end]" class="form-control form-control-sm" value="{{ $slot['end'] ?? '' }}"></div>
                            <div class="col-auto"><button type="button" class="btn btn-sm btn-danger remove-slot"><i class="fas fa-times"></i></button></div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="addSlot">
                        <i class="fas fa-plus mr-1"></i>Add Slot
                    </button>
                </div>
            </div>
        </div>
        @endif

        <div class="text-right">
            <a href="{{ route('timetables.show', $timetable) }}" class="btn btn-secondary mr-2">Cancel</a>
            <button type="submit" class="btn btn-warning" id="saveBtn">
                <i class="fas fa-save mr-1"></i>Save & Regenerate
            </button>
        </div>
    </div>
</div>

</form>

@stop

@section('css')
<style>.gap-2{gap:0.5rem}</style>
@stop

@section('js')
<script>
const SUBJECTS_URL      = '{{ route("timetables.subjects-by-classes") }}';
const EXISTING_PPW      = @json($timetable->settings['periods_per_week'] ?? []);
const EXISTING_DOUBLE   = @json($timetable->settings['double_periods'] ?? []);
const EXISTING_SS       = @json($timetable->settings['special_sessions'] ?? []);
const EXISTING_COMBOS   = @json($timetable->settings['combinations'] ?? []);
const EXISTING_TIMING   = '{{ $timetable->settings["timing_mode"] ?? "auto" }}';

// ── Classes ───────────────────────────────────────────────────────────────
$('#selectAllClasses').on('click', () => $('.class-check').prop('checked', true).trigger('change'));
$('#clearAllClasses').on('click',  () => $('.class-check').prop('checked', false).trigger('change'));

// ── Subject periods AJAX ──────────────────────────────────────────────────
let loadedSubjectOpts = '';
let loadTimeout = null;

$('#classCheckboxes').on('change', '.class-check', function () {
    clearTimeout(loadTimeout);
    loadTimeout = setTimeout(loadSubjects, 300);
});

function loadSubjects() {
    const ids = $('.class-check:checked').map((_, el) => el.value).get();
    if (!ids.length) { $('#subjectConfigBody').html('<div class="text-center text-muted p-3">Select classes above.</div>'); return; }

    $.ajax({
        url: SUBJECTS_URL,
        data: { class_ids: ids },
        success(rows) {
            if (!rows.length) return;
            const byClass = {};
            rows.forEach(r => {
                if (!byClass[r.class_id]) byClass[r.class_id] = { name: r.class_name, subjects: [] };
                byClass[r.class_id].subjects.push(r);
            });

            let html = '<table class="table table-sm table-bordered mb-0"><thead class="thead-light"><tr>' +
                '<th>Class</th><th>Subject</th>' +
                '<th>Periods/Week <small class="text-muted font-weight-normal">(blank=default, 0=exclude)</small></th>' +
                '<th class="text-center" style="width:90px">Double <small class="text-muted d-block" style="font-size:.65rem">back-to-back</small></th>' +
                '</tr></thead><tbody>';
            Object.entries(byClass).forEach(([cid, cls]) => {
                cls.subjects.forEach((s, i) => {
                    const key = `${s.class_id}_${s.subject_id}`;
                    const existing  = EXISTING_PPW[key] !== undefined ? EXISTING_PPW[key] : '';
                    const isDbl     = EXISTING_DOUBLE.includes(key);
                    html += `<tr>`;
                    if (i === 0) html += `<td rowspan="${cls.subjects.length}" class="align-middle font-weight-bold">${cls.name}</td>`;
                    html += `<td>${s.subject_name}</td>`;
                    html += `<td style="width:110px"><input type="number" name="periods_per_week[${key}]"
                               class="form-control form-control-sm" min="0" max="10"
                               placeholder="default" value="${existing}"></td>`;
                    html += `<td class="text-center align-middle">
                        <input type="checkbox" name="double_periods[${key}]" value="1"
                               ${isDbl ? 'checked' : ''}
                               title="All periods must be placed as consecutive back-to-back pairs">
                    </td></tr>`;
                });
            });
            html += '</tbody></table>';
            $('#subjectConfigBody').html(html);

            // Populate combo group selects
            const seen = {};
            loadedSubjectOpts = rows.reduce((acc, r) => {
                if (!seen[r.subject_id]) {
                    seen[r.subject_id] = true;
                    acc += `<option value="${r.subject_id}">${r.subject_name}</option>`;
                }
                return acc;
            }, '');

            // Re-render existing combos with fresh subject options
            if ($('#comboGroupsList').is(':empty') && EXISTING_COMBOS.length) {
                EXISTING_COMBOS.forEach((combo, idx) => {
                    const localIdx = comboIdx++;
                    const row = buildComboGroupRow(localIdx);
                    $('#comboGroupsList').append(row);
                    const $group = $('#comboGroupsList .combo-group').last();
                    // Restore subject selection
                    $group.find(`select[name="combo_subjects[${localIdx}][]"]`)
                          .val((combo.subjects || []).map(String));
                    // Restore shared count
                    $group.find(`input[name="combo_shared[${localIdx}]"]`).val(combo.shared ?? 2);
                    // Restore class restriction
                    if (combo.class_ids && combo.class_ids.length) {
                        $group.find(`select[name="combo_class_ids[${localIdx}][]"]`)
                              .val(combo.class_ids.map(String));
                    }
                });
            } else {
                // Refresh subject options in existing groups
                $('#comboGroupsList .combo-group').each(function() {
                    const $subjectSel = $(this).find('select[name*="combo_subjects"]');
                    const selected = $subjectSel.val() || [];
                    $subjectSel.html(loadedSubjectOpts).val(selected);
                });
            }
        }
    });
}

// Load on page ready for pre-checked classes
$(function () {
    loadSubjects();
    // Render existing special sessions
    EXISTING_SS.forEach(ss => {
        $('#specialSessionsList').append(buildSessionRow(ssIndex++, ss));
        // Mark matching preset button as active
        $('.add-preset-btn').filter((_, el) => $(el).data('preset')?.name === ss.name)
            .addClass('btn-secondary').removeClass('btn-outline-secondary').prop('disabled', true);
    });
});

// ── Combination Groups ────────────────────────────────────────────────────
let comboIdx = 0;
function buildComboGroupRow(idx) {
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
    if (!loadedSubjectOpts) { alert('Select classes to load subjects first.'); return; }
    $('#comboGroupsList').append(buildComboGroupRow(comboIdx++));
});
$(document).on('click', '.remove-combo', function () { $(this).closest('.combo-group').remove(); });

// Timing mode toggle
function toggleTimingMode() {
    const isManual = $('input[name="timing_mode"]:checked').val() === 'manual';
    $('#autoTimingFields').toggle(!isManual);
    $('#manualTimingSection').toggle(isManual);
}
$('input[name="timing_mode"]').on('change', toggleTimingMode);
toggleTimingMode();

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
    const days  = data.days || [1,2,3,4,5];
    const color = data.color || TYPE_COLORS[data.type] || 'secondary';
    const icon  = TYPE_ICONS[data.type || 'free'] || 'fas fa-circle';
    const dayCheckboxes = [1,2,3,4,5].map(d =>
        `<label class="mr-2 small"><input type="checkbox" name="special_sessions[${idx}][days][]" value="${d}"
            ${days.includes(d) ? 'checked' : ''}> ${DAYS_MAP[d]}</label>`
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
                    <input type="text" name="special_sessions[${idx}][name]" class="form-control form-control-sm font-weight-bold"
                           value="${data.name || ''}" placeholder="Session name" required>
                </div>
                <div class="col-auto mr-2">
                    <input type="time" name="special_sessions[${idx}][start_time]" class="form-control form-control-sm"
                           value="${data.start || data.start_time || ''}">
                </div>
                <div class="col-auto mr-1"><span class="text-muted">–</span></div>
                <div class="col-auto mr-2">
                    <input type="time" name="special_sessions[${idx}][end_time]" class="form-control form-control-sm"
                           value="${data.end || data.end_time || ''}">
                </div>
                <div class="col-auto mr-2">
                    <button type="button" class="btn btn-xs btn-danger remove-session"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="mt-1 ml-4 pl-1 small text-muted">Days: ${dayCheckboxes}</div>
        </div>
    </div>`;
}

$('#addCustomSession').on('click', () => $('#specialSessionsList').append(buildSessionRow(ssIndex++, {})));
$(document).on('click', '.add-preset-btn', function () {
    const preset = $(this).data('preset');
    const names  = $('.special-session-item input[name$="[name]"]').map((_, el) => el.value).get();
    if (names.includes(preset.name)) {
        $(this).addClass('btn-secondary').removeClass('btn-outline-secondary').prop('disabled', true);
        return;
    }
    $('#specialSessionsList').append(buildSessionRow(ssIndex++, preset));
    $(this).addClass('btn-secondary').removeClass('btn-outline-secondary').prop('disabled', true);
});
$(document).on('click', '.remove-session', function () {
    const name = $(this).closest('.special-session-item').find('input[name$="[name]"]').val();
    $('.add-preset-btn').filter((_, el) => $(el).data('preset')?.name === name)
        .removeClass('btn-secondary').addClass('btn-outline-secondary').prop('disabled', false);
    $(this).closest('.special-session-item').remove();
});

// ── Exam slots ────────────────────────────────────────────────────────────
let slotIdx = {{ count($timetable->settings['exam_slots'] ?? []) }};
$('#addSlot').on('click', function () {
    $('#examSlotsContainer').append(`<div class="exam-slot row no-gutters mb-2">
        <div class="col mr-1"><input type="text" name="exam_slots[${slotIdx}][label]" class="form-control form-control-sm" placeholder="Label"></div>
        <div class="col mr-1"><input type="time" name="exam_slots[${slotIdx}][start]" class="form-control form-control-sm"></div>
        <div class="col mr-1"><input type="time" name="exam_slots[${slotIdx}][end]" class="form-control form-control-sm"></div>
        <div class="col-auto"><button type="button" class="btn btn-sm btn-danger remove-slot"><i class="fas fa-times"></i></button></div>
    </div>`); slotIdx++;
});
$(document).on('click', '.remove-slot', function () { $(this).closest('.exam-slot').remove(); });

// ── Submit ────────────────────────────────────────────────────────────────
$('#timetableForm').on('submit', function () {
    const checked = $('.class-check:checked').length;
    if (!checked) { alert('Please select at least one class.'); return false; }
    $('#saveBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving…').prop('disabled', true);
});
</script>
@stop
