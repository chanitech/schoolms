@extends('adminlte::page')

@section('title', 'AI Insights Dashboard')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm mr-3">
                <i class="fas fa-arrow-left mr-1"></i>Back
            </a>
            <h1 class="mb-0">
                <i class="fas fa-robot text-primary"></i> AI Insights Dashboard
            </h1>
        </div>
        <div class="d-flex align-items-center mt-1 mt-md-0">
            <span class="badge badge-primary mr-2"><i class="fas fa-microchip mr-1"></i>Groq — llama-3.3-70b</span>
            <span class="badge badge-success"><i class="fas fa-bolt mr-1"></i>Free Tier · 30 req/min</span>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">

    {{-- Info Banner --}}
    <div class="alert alert-info alert-dismissible fade show mb-3 py-2">
        <i class="fas fa-info-circle"></i>
        Results are <strong>cached for 24 hours</strong> per student/class to save API calls.
        Use the <strong>Clear Cache</strong> button if you need a fresh analysis after entering new marks.
        <button type="button" class="close py-1" data-dismiss="alert">&times;</button>
    </div>

    <div class="row">

        {{-- ── Student Analysis ─────────────────────────────────────────── --}}
        <div class="col-lg-6 mb-4">
            <div class="card card-outline card-primary h-100 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-graduate mr-2 text-primary"></i>Student Performance Analysis
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" title="Collapse" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Select Student</label>
                        <select id="student-select" class="form-control select2" style="width:100%">
                            <option value="">— Choose a student —</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->first_name }} {{ $s->last_name }}
                                    @if($s->class) · {{ $s->class->name }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button id="analyze-student-btn" class="btn btn-primary btn-sm mr-2">
                            <i class="fas fa-magic mr-1"></i>Analyze
                        </button>
                        <button class="btn btn-outline-secondary btn-sm clear-cache-btn" data-type="student">
                            <i class="fas fa-sync-alt mr-1"></i>Clear Cache
                        </button>
                    </div>
                    <div id="student-result" class="result-box mt-3" style="display:none"></div>
                </div>
            </div>
        </div>

        {{-- ── Class Analysis ──────────────────────────────────────────── --}}
        <div class="col-lg-6 mb-4">
            <div class="card card-outline card-success h-100 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2 text-success"></i>Class Performance Analysis
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Select Class</label>
                        <select id="class-select" class="form-control select2" style="width:100%">
                            <option value="">— Choose a class —</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button id="analyze-class-btn" class="btn btn-success btn-sm mr-2">
                            <i class="fas fa-chart-line mr-1"></i>Analyze Class
                        </button>
                        <button id="interventions-btn" class="btn btn-warning btn-sm mr-2">
                            <i class="fas fa-lightbulb mr-1"></i>Interventions
                        </button>
                        <button class="btn btn-outline-secondary btn-sm clear-cache-btn" data-type="class">
                            <i class="fas fa-sync-alt mr-1"></i>Clear Cache
                        </button>
                    </div>
                    <div id="class-result" class="result-box mt-3" style="display:none"></div>
                </div>
            </div>
        </div>

        {{-- ── Finance Insights ─────────────────────────────────────────── --}}
        <div class="col-lg-6 mb-4">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-coins mr-2 text-info"></i>Financial Insights
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Analyzes all student pocket money deposits and withdrawals to surface trends and recommendations.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <button id="finance-btn" class="btn btn-info btn-sm mr-2">
                            <i class="fas fa-chart-pie mr-1"></i>Get Finance Insights
                        </button>
                        <button class="btn btn-outline-secondary btn-sm clear-cache-btn" data-type="finance">
                            <i class="fas fa-sync-alt mr-1"></i>Clear Cache
                        </button>
                    </div>
                    <div id="finance-result" class="result-box mt-3" style="display:none"></div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <style>
        /* ── Result box ── */
        .result-box {
            background: #f8fafc;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 16px;
            position: relative;
            font-size: 14px;
            line-height: 1.7;
            min-height: 80px;
        }

        /* ── Markdown-rendered content ── */
        .ai-content h1, .ai-content h2 { font-size: 18px; font-weight: 700; margin: 14px 0 6px; border-bottom: 2px solid #e9ecef; padding-bottom: 4px; }
        .ai-content h3 { font-size: 15px; font-weight: 700; margin: 12px 0 4px; color: #343a40; }
        .ai-content h4 { font-size: 13px; font-weight: 700; margin: 8px 0 4px; }
        .ai-content ul, .ai-content ol { padding-left: 20px; margin-bottom: 8px; }
        .ai-content li { margin-bottom: 3px; }
        .ai-content p { margin-bottom: 8px; }
        .ai-content strong { font-weight: 700; }
        .ai-content em { font-style: italic; }
        .ai-content hr { border-top: 1px solid #dee2e6; margin: 12px 0; }
        .ai-content code { background: #f1f3f5; border-radius: 3px; padding: 1px 5px; font-size: 12px; color: #e83e8c; }
        .ai-content pre { background: #f1f3f5; border-radius: 6px; padding: 10px; overflow-x: auto; }
        .ai-content table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 13px; }
        .ai-content th { background: #e9ecef; font-weight: 700; padding: 6px 10px; border: 1px solid #dee2e6; text-align: left; }
        .ai-content td { padding: 5px 10px; border: 1px solid #dee2e6; }
        .ai-content tr:nth-child(even) td { background: #f8f9fa; }
        .ai-content blockquote { border-left: 3px solid #007bff; padding-left: 12px; color: #6c757d; margin: 8px 0; }

        /* ── Loading skeleton ── */
        .ai-loading {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; padding: 30px; gap: 10px; color: #6c757d;
        }
        .ai-loading .spinner { font-size: 28px; color: #007bff; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .ai-loading .loading-text { font-size: 13px; font-weight: 500; }
        .ai-loading .loading-sub { font-size: 11px; color: #adb5bd; }

        /* ── Copy button ── */
        .copy-btn {
            position: absolute; top: 10px; right: 10px;
            background: rgba(255,255,255,0.9); border: 1px solid #dee2e6;
            border-radius: 5px; padding: 3px 8px; font-size: 11px;
            cursor: pointer; color: #6c757d; display: none;
        }
        .result-box:hover .copy-btn { display: block; }
        .copy-btn:hover { background: #007bff; color: #fff; border-color: #007bff; }

        /* ── Response meta ── */
        .result-meta {
            display: flex; justify-content: space-between; align-items: center;
            font-size: 11px; color: #adb5bd;
            border-top: 1px solid #f0f0f0; margin-top: 12px; padding-top: 8px;
        }
        .result-meta .badge { font-size: 10px; }

        .gap-2 { gap: 0.5rem; }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
    $(document).ready(function () {
        // ── Initialise Select2 ──
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

        // ── Configure marked.js ──
        marked.setOptions({ breaks: true, gfm: true });

        // ── Loading state ──
        function showLoading(boxId, label) {
            $('#' + boxId).show().html(`
                <div class="ai-loading">
                    <i class="fas fa-cog spinner"></i>
                    <div class="loading-text">AI is ${label}…</div>
                    <div class="loading-sub">This may take 10–30 seconds</div>
                </div>`);
        }

        // ── Render result ──
        function showResult(boxId, text, meta) {
            const html = marked.parse(text || '*No response generated.*');
            const elapsed = meta?.elapsed ? `<span>${meta.elapsed}s</span>` : '';
            const cached  = meta?.cached
                ? `<span class="badge badge-secondary"><i class="fas fa-database mr-1"></i>Cached</span>`
                : `<span class="badge badge-primary"><i class="fas fa-robot mr-1"></i>Live</span>`;

            $('#' + boxId).html(`
                <button class="copy-btn" onclick="copyText(this)" title="Copy"><i class="fas fa-copy mr-1"></i>Copy</button>
                <div class="ai-content">${html}</div>
                <div class="result-meta">
                    <span>${cached}</span>
                    <span>${elapsed}</span>
                </div>`);
        }

        function showError(boxId, msg) {
            $('#' + boxId).html(`
                <div class="alert alert-warning mb-0 py-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i> ${escHtml(msg)}
                </div>`);
        }

        function doPost(url, data, boxId, label) {
            showLoading(boxId, label);
            const start = Date.now();
            $.post(url, { _token: '{{ csrf_token() }}', ...data })
                .done(r => showResult(boxId, r.analysis, { elapsed: ((Date.now()-start)/1000).toFixed(1), cached: r.cached }))
                .fail(x => showError(boxId, x.responseJSON?.message || x.responseJSON?.error || 'Request failed. Please try again.'));
        }

        // ── Analyze Student ──
        $('#analyze-student-btn').on('click', function () {
            const id = $('#student-select').val();
            if (!id) return toastrWarn('Please select a student.');
            doPost('{{ route("ai.analyze.student") }}', { student_id: id }, 'student-result', 'analyzing student performance');
        });

        // ── Analyze Class ──
        $('#analyze-class-btn').on('click', function () {
            const id = $('#class-select').val();
            if (!id) return toastrWarn('Please select a class.');
            doPost('{{ route("ai.analyze.class") }}', { class_id: id }, 'class-result', 'analyzing class performance');
        });

        // ── Suggest Interventions ──
        $('#interventions-btn').on('click', function () {
            const id = $('#class-select').val();
            if (!id) return toastrWarn('Please select a class.');
            doPost('{{ route("ai.suggest.interventions") }}', { class_id: id }, 'class-result', 'generating intervention plans');
        });

        // ── Finance Insights ──
        $('#finance-btn').on('click', function () {
            doPost('{{ route("ai.finance.insights") }}', {}, 'finance-result', 'analyzing financial data');
        });

        // ── Clear Cache ──
        $('.clear-cache-btn').on('click', function () {
            const type = $(this).data('type');
            const map  = { student: '#student-result', class: '#class-result', finance: '#finance-result' };
            $(map[type]).hide().empty();
            if (typeof toastr !== 'undefined') {
                toastr.info('Cache cleared — next analysis will fetch fresh data.', '', { timeOut: 2500 });
            }
            $.post('{{ url("/ai/clear-cache") }}', { _token: '{{ csrf_token() }}', type: type })
                .fail(() => {}); // silent fail — cache cleared on front-end already
        });
    });

    // ── Copy text ──
    function copyText(btn) {
        const box = $(btn).siblings('.ai-content');
        const text = box.text();
        navigator.clipboard.writeText(text).then(() => {
            $(btn).html('<i class="fas fa-check mr-1"></i>Copied!').css('color','#28a745');
            setTimeout(() => $(btn).html('<i class="fas fa-copy mr-1"></i>Copy').css('color',''), 2000);
        });
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function toastrWarn(msg) {
        if (typeof toastr !== 'undefined') { toastr.warning(msg); }
        else { alert(msg); }
    }
    </script>
@endpush
