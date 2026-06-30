@extends('adminlte::page')

@section('title', 'Manage Exam Questions')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <a href="{{ route('marks.index') }}" class="btn btn-secondary btn-sm mr-3">
                <i class="fas fa-arrow-left mr-1"></i>Back to Marks
            </a>
            <h1 class="mb-0"><i class="fas fa-list-ol text-primary mr-2"></i>Manage Exam Questions</h1>
        </div>
        <a href="{{ route('marks.question.evaluation') }}" class="btn btn-outline-info btn-sm">
            <i class="fas fa-chart-bar mr-1"></i>Evaluation Report
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-4">

        {{-- Step 1: Filter --}}
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Step 1 — Select Filters</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="font-weight-bold">Academic Year</label>
                    <select id="sel-session" class="form-control select2">
                        <option value="">— All Years —</option>
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Class <small class="text-muted font-weight-normal">(optional)</small></label>
                    <select id="sel-class" class="form-control select2">
                        <option value="">— All Classes —</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Department <small class="text-muted font-weight-normal">(optional)</small></label>
                    <select id="sel-department" class="form-control select2">
                        <option value="">— All Departments —</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Exam</label>
                    <select id="sel-exam" class="form-control select2">
                        <option value="">— Select Year First —</option>
                        @foreach($exams as $e)
                            <option value="{{ $e->id }}">{{ $e->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold">Subject</label>
                    <select id="sel-subject" class="form-control select2">
                        <option value="">— Select Subject —</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button id="btn-load" class="btn btn-primary btn-block" disabled>
                    <i class="fas fa-arrow-right mr-1"></i>Load Questions
                </button>
            </div>
        </div>

        {{-- Info card --}}
        <div class="card bg-light shadow-sm">
            <div class="card-body py-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle text-info mr-1"></i>
                    Questions defined here determine the columns shown when teachers enter marks in <strong>By Questions</strong> mode.<br><br>
                    Marks are auto-totalled and converted to a <strong>percentage (0–100)</strong> for grading — existing results are not affected.
                </small>
            </div>
        </div>

    </div>

    <div class="col-lg-8">
        <div class="card card-outline card-success shadow-sm" id="questions-card" style="display:none;">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-pencil-alt mr-2"></i>Step 2 — Define Questions</h3>
                <span id="exam-subject-label" class="badge badge-info ml-3"></span>
                <span class="ml-auto">
                    Total: <strong id="total-marks-display">0</strong> marks
                </span>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0" id="questions-table">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:60px" class="text-center">#</th>
                            <th>Description <small class="text-muted font-weight-normal">(optional)</small></th>
                            <th style="width:130px">Max Marks</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="questions-tbody">
                        {{-- rows injected by JS --}}
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <td colspan="2" class="text-right font-weight-bold">Total:</td>
                            <td class="font-weight-bold text-success" id="tfoot-total">0 marks</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-row">
                    <i class="fas fa-plus mr-1"></i>Add Question
                </button>
                <div>
                    <button type="button" class="btn btn-danger btn-sm mr-2" id="btn-clear-all">
                        <i class="fas fa-trash mr-1"></i>Clear All
                    </button>
                    <button type="button" class="btn btn-success" id="btn-save">
                        <i class="fas fa-save mr-1"></i>Save Questions
                    </button>
                </div>
            </div>
        </div>

        {{-- Empty state --}}
        <div id="empty-state" class="text-center text-muted py-5">
            <i class="fas fa-list-ol fa-4x mb-3 text-light"></i>
            <p class="mb-0">Select an exam and subject on the left, then click <strong>Load Questions</strong>.</p>
        </div>
    </div>
</div>
@stop

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css" rel="stylesheet"/>
<style>
    #questions-tbody tr td { vertical-align: middle; }
    .q-no-badge { display:inline-block; width:28px; height:28px; border-radius:50%; background:#007bff; color:#fff; text-align:center; line-height:28px; font-size:12px; font-weight:700; }
    .drag-handle { cursor:grab; color:#adb5bd; }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
<script>
toastr.options = { positionClass: 'toast-top-right', timeOut: 4000, progressBar: true };
</script>
<script>
$(function () {
    // ── Select2 ──
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    // ── Academic Year → load Exams (AJAX) ──────────────────────────────────
    $('#sel-session').on('change', function () {
        const sessionId = $(this).val();
        const examSel   = $('#sel-exam');
        checkLoadBtn();

        if (!sessionId) {
            examSel.html('<option value="">— Select Year First —</option>@foreach($exams as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach').trigger('change');
            return;
        }
        examSel.html('<option value="">Loading…</option>');
        $.get('{{ route("marks.exams.by.session") }}', { session_id: sessionId })
            .done(function (exams) {
                let opts = '<option value="">— Select Exam —</option>';
                exams.forEach(e => opts += `<option value="${e.id}">${e.name}</option>`);
                examSel.html(opts).trigger('change');
            })
            .fail(() => examSel.html('<option value="">Error loading exams</option>'));
    });

    // ── Department → load Subjects (AJAX) ──────────────────────────────────
    $('#sel-department').on('change', function () {
        const deptId = $(this).val();
        const subSel = $('#sel-subject');
        checkLoadBtn();

        if (!deptId) {
            subSel.html('<option value="">— Select Subject —</option>@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach').trigger('change');
            return;
        }
        subSel.html('<option value="">Loading…</option>');
        $.get('{{ route("marks.subjects.by.department") }}', { department_id: deptId })
            .done(function (subjects) {
                let opts = '<option value="">— Select Subject —</option>';
                subjects.forEach(s => opts += `<option value="${s.id}">${s.name}</option>`);
                subSel.html(opts).trigger('change');
            })
            .fail(() => subSel.html('<option value="">Error loading subjects</option>'));
    });

    // ── Enable Load button when Exam + Subject are both selected ───────────
    function checkLoadBtn() {
        $('#btn-load').prop('disabled', !($('#sel-exam').val() && $('#sel-subject').val()));
    }
    $('#sel-exam, #sel-subject').on('change', checkLoadBtn);

    // ── Load existing questions ──
    $('#btn-load').on('click', function () {
        const examId    = $('#sel-exam').val();
        const subjectId = $('#sel-subject').val();
        const examName  = $('#sel-exam option:selected').text();
        const subName   = $('#sel-subject option:selected').text();

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading…');

        $.get('{{ route("exam.questions.get") }}', { exam_id: examId, subject_id: subjectId })
            .done(function (questions) {
                $('#exam-subject-label').text(examName + ' · ' + subName);
                $('#questions-tbody').empty();

                if (questions.length > 0) {
                    questions.forEach(q => addRow(q.question_no, q.description, q.max_marks));
                } else {
                    addRow(1, '', '');
                }

                updateNumbers();
                updateTotal();
                $('#empty-state').hide();
                $('#questions-card').show();
            })
            .fail(function () {
                toastr.error('Failed to load questions. Please try again.');
            })
            .always(function () {
                $('#btn-load').prop('disabled', false).html('<i class="fas fa-arrow-right mr-1"></i>Load Questions');
            });
    });

    // ── Add a blank row ──
    $('#btn-add-row').on('click', function () {
        const nextNo = $('#questions-tbody tr').length + 1;
        addRow(nextNo, '', '');
        updateNumbers();
    });

    // ── Delete row ──
    $(document).on('click', '.btn-delete-row', function () {
        if ($('#questions-tbody tr').length <= 1) {
            toastr.warning('At least one question is required.');
            return;
        }
        $(this).closest('tr').remove();
        updateNumbers();
        updateTotal();
    });

    // ── Live total ──
    $(document).on('input', '.q-max-marks', updateTotal);

    // ── Clear all ──
    $('#btn-clear-all').on('click', function () {
        if (!confirm('Clear all questions? This cannot be undone here.')) return;
        $('#questions-tbody').empty();
        addRow(1, '', '');
        updateNumbers();
        updateTotal();
    });

    // ── Save ──
    $('#btn-save').on('click', function () {
        const examId    = $('#sel-exam').val();
        const subjectId = $('#sel-subject').val();

        const questions = [];
        let valid = true;
        $('#questions-tbody tr').each(function () {
            const maxMarks = parseFloat($(this).find('.q-max-marks').val());
            if (!maxMarks || maxMarks <= 0) { valid = false; return false; }
            questions.push({
                description: $(this).find('.q-desc').val().trim() || null,
                max_marks:   maxMarks,
            });
        });

        if (!valid) {
            toastr.error('All questions must have a valid max marks value greater than 0.');
            return;
        }

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving…');

        $.post('{{ route("exam.questions.save") }}', {
            _token:     '{{ csrf_token() }}',
            exam_id:    examId,
            subject_id: subjectId,
            questions:  questions,
        })
        .done(function (res) {
            toastr.success(res.message || 'Questions saved!');
        })
        .fail(function (xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                const msg = Object.values(errors).flat().join('<br>');
                toastr.error(msg, 'Validation Error', { timeOut: 6000 });
            } else {
                toastr.error('Failed to save questions.');
            }
        })
        .always(function () {
            $('#btn-save').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Save Questions');
        });
    });

    // ── Helpers ──────────────────────────────────────────────────────────
    function addRow(no, desc, maxMarks) {
        const row = `
            <tr>
                <td class="text-center"><span class="q-no-badge">${no}</span></td>
                <td><input type="text" class="form-control form-control-sm q-desc" placeholder="e.g. Reading comprehension" value="${escHtml(desc || '')}"></td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control q-max-marks" min="0.5" max="999" step="0.5" value="${maxMarks || ''}" placeholder="e.g. 10">
                        <div class="input-group-append"><span class="input-group-text">pts</span></div>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-xs btn-outline-danger btn-delete-row" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>`;
        $('#questions-tbody').append(row);
    }

    function updateNumbers() {
        $('#questions-tbody tr').each(function (i) {
            $(this).find('.q-no-badge').text(i + 1);
        });
    }

    function updateTotal() {
        let total = 0;
        $('.q-max-marks').each(function () {
            const v = parseFloat($(this).val());
            if (!isNaN(v)) total += v;
        });
        $('#total-marks-display').text(total % 1 === 0 ? total : total.toFixed(1));
        $('#tfoot-total').text((total % 1 === 0 ? total : total.toFixed(1)) + ' marks');
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
});
</script>
@endpush
