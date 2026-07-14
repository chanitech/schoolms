@extends('adminlte::page')

@section('title', 'Add Marks')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center">
            <a href="{{ route('marks.index') }}" class="btn btn-secondary btn-sm mr-3">
                <i class="fas fa-arrow-left mr-1"></i>Back to Marks
            </a>
            <h1 class="mb-0">Add Marks</h1>
        </div>
        <a href="{{ route('marks.question.evaluation') }}" class="btn btn-outline-info btn-sm mt-1 mt-md-0">
            <i class="fas fa-chart-bar mr-1"></i>Evaluation Report
        </a>
    </div>
@stop

@section('content')
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            {{ session('warning') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-0">Manual Entry</h3>
        <div class="d-flex flex-wrap gap-1">
            {{-- General mode buttons (shown in general mode) --}}
            <div id="general-mode-buttons">
                <a href="#" id="downloadFilteredBtn" class="btn btn-sm btn-info mr-1" style="display: none;">
                    <i class="fas fa-users mr-1"></i>Download Template
                </a>
                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#importExcelModal">
                    <i class="fas fa-file-excel mr-1"></i>Import Marks
                </button>
            </div>

            {{-- Question mode buttons (shown in question mode) --}}
            <div id="question-mode-buttons" style="display:none;">
                <a href="#" id="downloadQuestionTemplateBtn" class="btn btn-sm btn-info mr-1" style="display: none;">
                    <i class="fas fa-download mr-1"></i>Download Template
                </a>
                <button type="button" class="btn btn-sm btn-success" id="btnOpenQuestionImport"
                        data-toggle="modal" data-target="#importQuestionMarksModal" style="display: none;">
                    <i class="fas fa-file-excel mr-1"></i>Import by Questions
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('marks.store') }}" method="POST" id="marksForm">
            @csrf

            <div class="row mb-3">
                {{-- Academic Year --}}
                <div class="col-lg-2 col-md-4 mb-2">
                    <label for="session">Academic Year</label>
                    <select name="academic_session_id" id="session" class="form-control" required>
                        <option value="">Select Year</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ old('academic_session_id', $selectedSession ?? '') == $session->id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Class --}}
                <div class="col-lg-2 col-md-4 mb-2">
                    <label for="class">Class</label>
                    <select name="class_id" id="class" class="form-control" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $selectedClass ?? '') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Department --}}
                <div class="col-lg-2 col-md-4 mb-2">
                    <label for="department">Department</label>
                    <select name="department_id" id="department" class="form-control">
                        <option value="">All Departments</option>
                        @foreach(\App\Models\Department::orderBy('name')->get() as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $selectedDepartment ?? '') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Exam --}}
                <div class="col-lg-3 col-md-6 mb-2">
                    <label for="exam">Exam</label>
                    <select name="exam_id" id="exam" class="form-control" required>
                        <option value="">— Select Year First —</option>
                    </select>
                </div>

                {{-- Subject --}}
                <div class="col-lg-3 col-md-6 mb-2">
                    <label for="subject">Subject</label>
                    <select name="subject_id" id="subject" class="form-control" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $selectedSubject ?? '') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @if(auth()->user()->hasRole('Teacher'))
                        <small class="text-muted">Only your assigned subjects are shown</small>
                    @endif
                </div>
            </div>

            {{-- Mode toggle (shown after students load) --}}
            <div id="mode-toggle-bar" class="row mb-3" style="display:none !important;">
                <div class="col-12 d-flex align-items-center">
                    <span class="mr-3 font-weight-bold text-muted small">ENTRY MODE:</span>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-primary active" id="btn-mode-general">
                            <i class="fas fa-hashtag mr-1"></i>General Mark
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="btn-mode-questions">
                            <i class="fas fa-list-ol mr-1"></i>By Questions
                        </button>
                    </div>
                    <span id="questions-defined-badge" class="ml-3" style="display:none;"></span>
                    <a href="{{ route('exam.questions.manage') }}" target="_blank"
                       class="btn btn-xs btn-outline-secondary ml-auto" id="manage-questions-link" style="display:none;">
                        <i class="fas fa-cog mr-1"></i>Define Questions
                    </a>
                </div>
            </div>

            {{-- Students Table (general mode) --}}
            <div id="general-mode-section">
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-sm" id="students-table">
                            <thead class="thead-light">
                                <tr>
                                    <th width="40">#</th>
                                    <th>Student</th>
                                    <th width="160">Mark <small class="text-muted">(0–100)</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">
                                        Select academic year, class, exam and subject to load students
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>Save Marks
                </button>
            </div>

            {{-- Question-mode section (separate form, different action) --}}
            <div id="question-mode-section" style="display:none;">
                <div id="question-no-questions" class="alert alert-warning" style="display:none;">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    No questions defined for this exam and subject yet.
                    <a href="{{ route('exam.questions.manage') }}" target="_blank" class="alert-link">
                        Define questions first <i class="fas fa-external-link-alt fa-xs"></i>
                    </a>, then come back and switch to <strong>By Questions</strong> mode.
                </div>
                <div id="question-table-wrap"></div>
            </div>
        </form>

        {{-- Separate form for question-mode submission --}}
        <form action="{{ route('marks.store.by.questions') }}" method="POST" id="question-marks-form" style="display:none;">
            @csrf
            <input type="hidden" name="academic_session_id" id="qf-session">
            <input type="hidden" name="class_id"            id="qf-class">
            <input type="hidden" name="subject_id"          id="qf-subject">
            <input type="hidden" name="exam_id"             id="qf-exam">
            <div id="qf-score-inputs"></div>
            <button type="submit" class="btn btn-primary mt-3">
                <i class="fas fa-save mr-1"></i>Save Question Marks
            </button>
            <a href="{{ route('marks.question.evaluation') }}" class="btn btn-outline-info mt-3 ml-2">
                <i class="fas fa-chart-bar mr-1"></i>View Evaluation Report
            </a>
        </form>
    </div>
</div>

{{-- Import Excel Modal --}}
<div class="modal fade" id="importExcelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('marks.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Marks from Excel</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Excel Format Required:</strong>
                        <ul class="mb-0">
                            <li>Columns: <code>student_id</code> or <code>admission_no</code>, and <code>mark</code></li>
                            <li>Make sure you have selected <strong>Class, Session, Subject & Exam</strong> before importing.</li>
                            <li>Marks must be between 0 and 100.</li>
                        </ul>
                    </div>
                    <div class="form-group">
                        <label for="excel_file">Choose Excel File (.xlsx, .xls)</label>
                        <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls" required>
                    </div>
                    <input type="hidden" name="class_id" id="import_class_id">
                    <input type="hidden" name="session_id" id="import_session_id">
                    <input type="hidden" name="subject_id" id="import_subject_id">
                    <input type="hidden" name="exam_id" id="import_exam_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import & Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import by Questions Modal --}}
<div class="modal fade" id="importQuestionMarksModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('marks.import.question.marks') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-excel mr-1 text-success"></i>Import Marks by Questions</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <strong>Instructions:</strong>
                        <ol class="mb-0 pl-3">
                            <li>Select Academic Year, Class, Exam and Subject first, then switch to <strong>By Questions</strong> mode.</li>
                            <li>Click <strong>Download Template</strong> to get a pre-filled Excel with your question columns.</li>
                            <li>Fill in the scores for each question (leave blank to skip a student).</li>
                            <li>Upload the file here — scores are validated against each question's max marks.</li>
                        </ol>
                    </div>
                    <div class="form-group">
                        <label>Excel File (.xlsx, .xls)</label>
                        <input type="file" name="question_excel_file" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                    <input type="hidden" name="class_id"   id="qimport_class_id">
                    <input type="hidden" name="session_id" id="qimport_session_id">
                    <input type="hidden" name="subject_id" id="qimport_subject_id">
                    <input type="hidden" name="exam_id"    id="qimport_exam_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload mr-1"></i>Import & Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function () {

    let currentMode       = 'general'; // 'general' | 'questions'
    let loadedQuestions   = [];        // questions for current exam+subject
    let loadedStudents    = [];        // general-mode students

    // ── Load exams from session ───────────────────────────────────────────
    function loadExams() {
        const session_id = $('#session').val();
        const examSelect = $('#exam');
        if (session_id) {
            examSelect.html('<option value="">Loading…</option>');
            $.get("{{ route('marks.exams.by.session') }}", { session_id, exclude_published: 1 })
                .done(function (exams) {
                    let opts = '<option value="">Select Exam</option>';
                    exams.forEach(e => {
                        const sel = ({{ $selectedExam ?? 'null' }} == e.id) ? 'selected' : '';
                        opts += `<option value="${e.id}" ${sel}>${e.name}</option>`;
                    });
                    examSelect.html(opts.length > 1 ? opts : '<option value="">No exams for this session</option>');
                    if ($('#exam').val()) loadStudents();
                })
                .fail(() => examSelect.html('<option value="">Error loading exams</option>'));
        } else {
            examSelect.html('<option value="">— Select Session First —</option>');
        }
    }

    // ── Load subjects from department ─────────────────────────────────────
    function loadSubjects() {
        const dept_id = $('#department').val();
        if (dept_id) {
            $.get("{{ route('marks.subjects.by.department') }}", { department_id: dept_id })
                .done(function (subjects) {
                    let opts = '<option value="">Select Subject</option>';
                    subjects.forEach(s => {
                        const sel = ({{ $selectedSubject ?? 'null' }} == s.id) ? 'selected' : '';
                        opts += `<option value="${s.id}" ${sel}>${s.name}</option>`;
                    });
                    $('#subject').html(opts);
                    if ($('#subject').val() && $('#exam').val()) loadStudents();
                })
                .fail(() => $('#subject').html('<option value="">Could not fetch subjects</option>'));
        } else {
            $('#subject').html('<option value="">Select Subject</option>');
        }
    }

    // ── Load students (general mode) ──────────────────────────────────────
    function loadStudents() {
        const class_id   = $('#class').val();
        const session_id = $('#session').val();
        const subject_id = $('#subject').val();
        const exam_id    = $('#exam').val();

        resetModeUI();

        if (!(class_id && session_id && subject_id && exam_id)) {
            $('#students-table tbody').html('<tr><td colspan="3" class="text-center text-muted">Select academic year, class, exam and subject to load students</td></tr>');
            return;
        }

        $('#students-table tbody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading…</td></tr>');

        $.get("{{ route('marks.students') }}", { class_id, session_id, subject_id, exam_id })
            .done(function (students) {
                loadedStudents = students;
                renderGeneralTable(students);
                showModeToggle();
                updateDownloadLink();
            })
            .fail(function (xhr) {
                const msg = xhr.responseJSON?.error || 'Failed to load students';
                $('#students-table tbody').html(`<tr><td colspan="3" class="text-center text-danger">${msg}</td></tr>`);
            });
    }

    // ── Render general mark table ─────────────────────────────────────────
    function renderGeneralTable(students) {
        let tbody = '';
        if (students.length > 0) {
            students.forEach((s, i) => {
                const mark = s.mark ?? '';
                tbody += `<tr>
                    <td class="text-center text-muted">${i + 1}</td>
                    <td>${escHtml(s.first_name + ' ' + s.last_name)}</td>
                    <td>
                        <input type="number" name="marks[${s.id}]"
                               class="form-control form-control-sm"
                               min="0" max="100" step="0.01" value="${mark}"
                               placeholder="0–100">
                    </td>
                </tr>`;
            });
        } else {
            tbody = '<tr><td colspan="3" class="text-center text-muted">No students found</td></tr>';
        }
        $('#students-table tbody').html(tbody);
    }

    // ── Mode toggle visibility ────────────────────────────────────────────
    function showModeToggle() {
        $('#mode-toggle-bar').css('display', 'flex');
        $('#manage-questions-link').show();
        switchMode(currentMode);   // re-render current mode
    }

    function resetModeUI() {
        $('#mode-toggle-bar').hide();
        $('#question-mode-section').hide();
        $('#general-mode-section').show();
        $('#question-marks-form').hide();
        $('#questions-defined-badge').hide();
        $('#question-mode-buttons').hide();
        $('#downloadQuestionTemplateBtn').hide();
        $('#btnOpenQuestionImport').hide();
        loadedQuestions = [];
    }

    // ── Switch between General / By Questions mode ────────────────────────
    function switchMode(mode) {
        currentMode = mode;
        if (mode === 'general') {
            $('#btn-mode-general').removeClass('btn-outline-primary').addClass('btn-primary active');
            $('#btn-mode-questions').removeClass('btn-primary active').addClass('btn-outline-primary');
            $('#general-mode-section').show();
            $('#question-mode-section').hide();
            $('#question-marks-form').hide();
            $('#general-mode-buttons').show();
            $('#question-mode-buttons').hide();
        } else {
            $('#btn-mode-questions').removeClass('btn-outline-primary').addClass('btn-primary active');
            $('#btn-mode-general').removeClass('btn-primary active').addClass('btn-outline-primary');
            $('#general-mode-section').hide();
            $('#question-mode-section').show();
            $('#general-mode-buttons').hide();
            $('#question-mode-buttons').show();
            updateQuestionModeButtons();
            loadQuestionMode();
        }
    }

    // ── Show/hide download+import buttons in question mode ────────────────
    function updateQuestionModeButtons() {
        const class_id   = $('#class').val();
        const session_id = $('#session').val();
        const subject_id = $('#subject').val();
        const exam_id    = $('#exam').val();
        const allSet     = class_id && session_id && subject_id && exam_id;

        if (allSet) {
            // Build download URL with current filter values
            const params = new URLSearchParams({
                class_id,
                academic_session_id: session_id,
                exam_id,
                subject_id,
            });
            $('#downloadQuestionTemplateBtn')
                .attr('href', '{{ route("marks.download.question.template") }}?' + params)
                .show();
            $('#btnOpenQuestionImport').show();

            // Sync hidden fields for import modal
            $('#qimport_class_id').val(class_id);
            $('#qimport_session_id').val(session_id);
            $('#qimport_subject_id').val(subject_id);
            $('#qimport_exam_id').val(exam_id);
        } else {
            $('#downloadQuestionTemplateBtn').hide();
            $('#btnOpenQuestionImport').hide();
        }
    }

    $('#btn-mode-general').on('click', () => switchMode('general'));
    $('#btn-mode-questions').on('click', () => switchMode('questions'));

    // ── Load students+questions for question mode ─────────────────────────
    function loadQuestionMode() {
        const class_id   = $('#class').val();
        const session_id = $('#session').val();
        const subject_id = $('#subject').val();
        const exam_id    = $('#exam').val();

        $('#question-table-wrap').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted">Loading question structure…</p></div>');
        $('#question-no-questions').hide();

        $.get("{{ route('marks.students.with.questions') }}", { class_id, session_id, subject_id, exam_id })
            .done(function (data) {
                if (!data.has_questions) {
                    $('#question-table-wrap').empty();
                    $('#question-no-questions').show();
                    $('#questions-defined-badge').hide();
                    $('#question-marks-form').hide();
                    return;
                }

                loadedQuestions = data.questions;
                const total = loadedQuestions.reduce((s, q) => s + q.max_marks, 0);

                $('#questions-defined-badge').html(
                    `<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>${loadedQuestions.length} questions · ${total} marks total</span>`
                ).show();

                renderQuestionTable(data.questions, data.students);
                buildHiddenForm(data.questions);
                $('#question-marks-form').show();
            })
            .fail(() => {
                $('#question-table-wrap').html('<div class="alert alert-danger">Failed to load question data.</div>');
            });
    }

    // ── Render the question-mode table ────────────────────────────────────
    function renderQuestionTable(questions, students) {
        const totalMax = questions.reduce((s, q) => s + q.max_marks, 0);

        // Build header
        let thead = '<tr><th width="30">#</th><th>Student</th>';
        questions.forEach(q => {
            const desc = q.description ? ` <small class="text-muted d-block" style="font-size:10px;">${escHtml(q.description)}</small>` : '';
            thead += `<th class="text-center" style="min-width:80px;">Q${q.question_no}${desc}<small class="text-primary">/${q.max_marks}</small></th>`;
        });
        thead += `<th class="text-center" style="min-width:90px;">Total<br><small class="text-primary">/${totalMax}</small></th>`;
        thead += `<th class="text-center" style="min-width:60px;">%</th></tr>`;

        // Build body
        let tbody = '';
        if (students.length === 0) {
            tbody = `<tr><td colspan="${questions.length + 4}" class="text-center text-muted">No students found</td></tr>`;
        } else {
            students.forEach((s, idx) => {
                const sid = s.id;
                tbody += `<tr data-student="${sid}">
                    <td class="text-center text-muted small">${idx + 1}</td>
                    <td class="font-weight-bold">${escHtml(s.first_name + ' ' + s.last_name)}</td>`;
                questions.forEach(q => {
                    const existing = s.scores[q.id] ?? '';
                    tbody += `<td>
                        <input type="number" class="form-control form-control-sm q-score-input text-center"
                               data-student="${sid}" data-question="${q.id}" data-max="${q.max_marks}"
                               min="0" max="${q.max_marks}" step="0.5"
                               value="${existing}" placeholder="0">
                    </td>`;
                });
                tbody += `<td class="text-center font-weight-bold" id="row-total-${sid}">—</td>`;
                tbody += `<td class="text-center" id="row-pct-${sid}">—</td></tr>`;
                // Compute existing total if any scores loaded
                setTimeout(() => recalcRow(sid, questions, totalMax), 0);
            });
        }

        // Class average footer
        let tfoot = `<tr class="table-light font-weight-bold">
            <td colspan="2" class="text-right">Class Average:</td>`;
        questions.forEach(q => {
            tfoot += `<td class="text-center text-info" id="col-avg-${q.id}">—</td>`;
        });
        tfoot += `<td class="text-center text-info" id="col-avg-total">—</td><td id="col-avg-pct">—</td></tr>`;

        const html = `<div class="table-responsive">
            <table class="table table-bordered table-sm" id="question-entry-table">
                <thead class="thead-light">${thead}</thead>
                <tbody>${tbody}</tbody>
                <tfoot>${tfoot}</tfoot>
            </table></div>`;

        $('#question-table-wrap').html(html);

        // Live recalc on input
        $(document).on('input', '.q-score-input', function () {
            const sid = $(this).data('student');
            const max = parseFloat($(this).data('max'));
            let val   = parseFloat($(this).val());
            // Cap at question max
            if (!isNaN(val) && val > max) { $(this).val(max); }
            recalcRow(sid, loadedQuestions, totalMax);
            recalcColumnAverages(loadedQuestions, totalMax);
        });

        // Initial column averages
        recalcColumnAverages(questions, totalMax);
    }

    function recalcRow(sid, questions, totalMax) {
        let total = 0; let hasAny = false;
        questions.forEach(q => {
            const val = parseFloat($(`.q-score-input[data-student="${sid}"][data-question="${q.id}"]`).val());
            if (!isNaN(val)) { total += val; hasAny = true; }
        });
        if (hasAny) {
            const pct = totalMax > 0 ? ((total / totalMax) * 100).toFixed(1) : 0;
            $(`#row-total-${sid}`).text(total % 1 === 0 ? total : total.toFixed(1));
            const pctNum = parseFloat(pct);
            const color  = pctNum >= 70 ? 'text-success' : pctNum >= 50 ? 'text-warning' : 'text-danger';
            $(`#row-pct-${sid}`).html(`<span class="${color} font-weight-bold">${pct}%</span>`);
        } else {
            $(`#row-total-${sid}`).text('—');
            $(`#row-pct-${sid}`).text('—');
        }
    }

    function recalcColumnAverages(questions, totalMax) {
        let totalAvgs = [];
        questions.forEach(q => {
            const vals = [];
            $(`.q-score-input[data-question="${q.id}"]`).each(function () {
                const v = parseFloat($(this).val());
                if (!isNaN(v)) vals.push(v);
            });
            const avg = vals.length ? (vals.reduce((a, b) => a + b, 0) / vals.length) : null;
            $(`#col-avg-${q.id}`).text(avg !== null ? avg.toFixed(1) : '—');
            totalAvgs.push(avg);
        });
        // Overall avg total
        const rowTotals = [];
        $('.q-score-input[data-student]').each(function () {
            // collect per-student totals
        });
        // Simpler: average of row totals
        const perStudent = {};
        questions.forEach(q => {
            $(`.q-score-input[data-question="${q.id}"]`).each(function () {
                const sid = $(this).data('student');
                const v   = parseFloat($(this).val());
                if (!isNaN(v)) { perStudent[sid] = (perStudent[sid] || 0) + v; }
            });
        });
        const totals = Object.values(perStudent);
        if (totals.length) {
            const avgTotal = totals.reduce((a, b) => a + b, 0) / totals.length;
            const avgPct   = totalMax > 0 ? ((avgTotal / totalMax) * 100).toFixed(1) : '—';
            $('#col-avg-total').text(avgTotal.toFixed(1));
            $('#col-avg-pct').text(avgPct + '%');
        }
    }

    // ── Build hidden inputs for question form submission ──────────────────
    function buildHiddenForm(questions) {
        $('#qf-session').val($('#session').val());
        $('#qf-class').val($('#class').val());
        $('#qf-subject').val($('#subject').val());
        $('#qf-exam').val($('#exam').val());
        // score inputs injected dynamically on submit
    }

    // On question-form submit: collect all score inputs and populate hidden fields
    $('#question-marks-form').on('submit', function () {
        $('#qf-score-inputs').empty();
        $(`.q-score-input`).each(function () {
            const sid = $(this).data('student');
            const qid = $(this).data('question');
            const val = $(this).val();
            $('<input>').attr({
                type:  'hidden',
                name:  `scores[${sid}][${qid}]`,
                value: val,
            }).appendTo('#qf-score-inputs');
        });
        // Sync hidden header fields
        $('#qf-session').val($('#session').val());
        $('#qf-class').val($('#class').val());
        $('#qf-subject').val($('#subject').val());
        $('#qf-exam').val($('#exam').val());
    });

    // ── Download link ─────────────────────────────────────────────────────
    function updateDownloadLink() {
        const class_id = $('#class').val(), session_id = $('#session').val(),
              exam_id  = $('#exam').val(), subject_id  = $('#subject').val(),
              dept_id  = $('#department').val();
        if (class_id && session_id && exam_id && subject_id) {
            const p = new URLSearchParams({ class_id, academic_session_id: session_id, exam_id, subject_id, department_id: dept_id });
            $('#downloadFilteredBtn').attr('href', "{{ route('marks.download-filtered-template') }}?" + p).show();
        } else {
            $('#downloadFilteredBtn').hide();
        }
    }

    // ── Event wiring ──────────────────────────────────────────────────────
    $('#department').on('change', loadSubjects);
    $('#session').on('change', function () {
        loadExams();
        resetModeUI();
        $('#students-table tbody').html('<tr><td colspan="3" class="text-center text-muted">Select academic year, class, exam and subject to load students</td></tr>');
        $('#downloadFilteredBtn').hide();
    });
    $('#class, #subject, #exam').on('change', function () {
        resetModeUI();
        loadStudents();
    });

    // Initial load
    updateDownloadLink();
    if ($('#session').val()) loadExams();

    // Import modal — sync hidden fields
    $('#importExcelModal').on('show.bs.modal', function () {
        $('#import_class_id').val($('#class').val());
        $('#import_session_id').val($('#session').val());
        $('#import_subject_id').val($('#subject').val());
        $('#import_exam_id').val($('#exam').val());
    });

    // Auto-dismiss alerts
    setTimeout(() => $('.alert').fadeOut('slow'), 5000);

    // ── Utility ───────────────────────────────────────────────────────────
    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
});
</script>
@endsection