@extends('adminlte::page')

@section('title', 'Topic Coverage — ' . $lessonPlan->subject->name)

@section('content_header')
<div class="d-flex align-items-center justify-content-between flex-wrap">
    <div class="d-flex align-items-center">
        <a href="{{ route('topic-coverage.index') }}" class="btn btn-secondary btn-sm mr-3">
            <i class="fas fa-arrow-left mr-1"></i>Back
        </a>
        <div>
            <h1 class="mb-0"><i class="fas fa-tasks text-primary mr-2"></i>{{ $lessonPlan->subject->name }}</h1>
            <small class="text-muted ml-1">
                {{ $lessonPlan->schoolClass->name }} &middot; {{ $lessonPlan->session->name }}
                &middot; {{ $lessonPlan->teacher->name ?? ($lessonPlan->teacher->first_name . ' ' . $lessonPlan->teacher->last_name) }}
            </small>
        </div>
    </div>
    <div class="d-flex align-items-center mt-1 mt-md-0">
        <span class="badge badge-pill badge-light border mr-2" id="stats-badge">
            <i class="fas fa-tasks mr-1"></i>
            <span id="covered-count">{{ $stats['covered'] }}</span>/<span id="total-count">{{ $stats['total'] }}</span> covered
        </span>
        <div class="progress mr-2" style="width:100px;height:10px;">
            <div class="progress-bar" id="main-progress" style="width:{{ $stats['pct'] }}%;background:#28a745;"></div>
        </div>
        <strong id="main-pct" class="{{ $stats['pct'] >= 80 ? 'text-success' : ($stats['pct'] >= 50 ? 'text-warning' : 'text-danger') }}">
            {{ $stats['pct'] }}%
        </strong>
    </div>
</div>
@stop

@section('content')

@foreach(['success','warning','error'] as $type)
@if(session($type))
<div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>{{ session($type) }}
</div>
@endif
@endforeach

<div class="row">
    {{-- Left: Topics & Subtopics --}}
    <div class="col-lg-8">

        <div id="topics-container">
        @forelse($lessonPlan->topics as $topic)
        @php $tStats = $topic->completionStats(); @endphp
        <div class="card shadow-sm mb-3 topic-card" data-topic-id="{{ $topic->id }}">
            <div class="card-header d-flex align-items-center py-2">
                <i class="fas fa-grip-vertical text-muted mr-2 drag-handle"></i>
                <span class="topic-title font-weight-bold flex-grow-1">{{ $topic->title }}</span>
                <span class="badge badge-{{ $tStats['pct'] >= 80 ? 'success' : ($tStats['pct'] >= 50 ? 'warning' : 'secondary') }} mr-2 topic-pct-badge">
                    {{ $tStats['pct'] }}%
                </span>
                @if($canEdit)
                <button class="btn btn-xs btn-outline-primary mr-1 btn-edit-topic" title="Rename topic">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <button class="btn btn-xs btn-outline-danger btn-delete-topic" title="Delete topic">
                    <i class="fas fa-trash"></i>
                </button>
                @endif
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush subtopics-list">
                    @foreach($topic->subtopics as $sub)
                    <li class="list-group-item px-3 py-2 subtopic-item d-flex align-items-center"
                        data-subtopic-id="{{ $sub->id }}" data-status="{{ $sub->status }}">
                        <i class="fas fa-{{ $sub->isCovered() ? 'check-circle text-success' : 'circle text-muted' }} mr-3 subtopic-status-icon"></i>
                        <div class="flex-grow-1">
                            <span class="subtopic-title {{ $sub->isCovered() ? 'text-muted text-decoration-line-through' : '' }}">
                                {{ $sub->title }}
                            </span>
                            @if($sub->isCovered())
                            <br>
                            <small class="text-success">
                                <i class="fas fa-calendar-check mr-1"></i>
                                Covered {{ $sub->date_covered?->format('d M Y') }}
                                @if($sub->coveredBy) by {{ $sub->coveredBy->name ?? $sub->coveredBy->first_name }}@endif
                            </small>
                            @endif
                            @if($sub->notes)
                            <br><small class="text-info"><i class="fas fa-sticky-note mr-1"></i>{{ $sub->notes }}</small>
                            @endif
                        </div>
                        <div class="ml-2 subtopic-actions">
                            @if($canEdit || $lessonPlan->teacher_id === auth()->id())
                                @if($sub->isCovered())
                                <button class="btn btn-xs btn-outline-warning btn-toggle-covered" title="Unmark">
                                    <i class="fas fa-undo mr-1"></i>Unmark
                                </button>
                                @else
                                <button class="btn btn-xs btn-success btn-toggle-covered" title="Mark as covered">
                                    <i class="fas fa-check mr-1"></i>Covered
                                </button>
                                @endif
                                <button class="btn btn-xs btn-outline-info btn-generate-plan"
                                        title="{{ $sub->lesson_plan_content ? 'View / Regenerate Lesson Plan' : 'Generate Lesson Plan' }}"
                                        data-subtopic-id="{{ $sub->id }}"
                                        data-subtopic-title="{{ $sub->title }}"
                                        data-has-plan="{{ $sub->lesson_plan_content ? '1' : '0' }}">
                                    <i class="fas fa-file-alt mr-1"></i>{{ $sub->lesson_plan_content ? 'Plan ✓' : 'Plan' }}
                                </button>
                            @endif
                            @if($canEdit)
                            <button class="btn btn-xs btn-outline-secondary btn-edit-subtopic" title="Edit">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-xs btn-outline-danger btn-delete-subtopic" title="Delete">
                                <i class="fas fa-times"></i>
                            </button>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @if($canEdit)
                <div class="px-3 py-2 border-top bg-light">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control input-new-subtopic" placeholder="Add subtopic…" maxlength="200">
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary btn-add-subtopic" type="button">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-4" id="empty-topics-msg">
            <i class="fas fa-list fa-3x mb-2 text-light"></i>
            <p>No topics yet. Add your first topic on the right.</p>
        </div>
        @endforelse
        </div>

    </div>

    {{-- Right: Add Topic + Record Info + Summary --}}
    <div class="col-lg-4">

        @if($canEdit)
        <div class="card card-outline card-primary shadow-sm mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i>Add Topic</h3></div>
            <div class="card-body">
                <div class="input-group">
                    <input type="text" id="new-topic-input" class="form-control" placeholder="Topic title…" maxlength="200">
                    <div class="input-group-append">
                        <button id="btn-add-topic" class="btn btn-primary" type="button">Add</button>
                    </div>
                </div>
                <small class="text-muted">Press Enter or click Add</small>
            </div>
        </div>
        @endif

        {{-- Record Info --}}
        <div class="card bg-light shadow-sm mb-3">
            <div class="card-body">
                <h6 class="font-weight-bold mb-2"><i class="fas fa-info-circle text-info mr-1"></i>Record Details</h6>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr><th class="pl-0">Subject</th><td>{{ $lessonPlan->subject->name }}</td></tr>
                    <tr><th class="pl-0">Class</th><td>{{ $lessonPlan->schoolClass->name }}</td></tr>
                    <tr><th class="pl-0">Year</th><td>{{ $lessonPlan->session->name }}</td></tr>
                    <tr><th class="pl-0">Teacher</th><td>{{ $lessonPlan->teacher->name ?? ($lessonPlan->teacher->first_name . ' ' . $lessonPlan->teacher->last_name) }}</td></tr>
                    @if($lessonPlan->subject->department)
                    <tr><th class="pl-0">Department</th><td>{{ $lessonPlan->subject->department->name }}</td></tr>
                    @endif
                </table>
                @if($lessonPlan->description)
                <hr class="my-2">
                <p class="small text-muted mb-0">{{ $lessonPlan->description }}</p>
                @endif
            </div>
        </div>

        {{-- Coverage Summary --}}
        <div class="card shadow-sm">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i>Coverage Summary</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="topic-summary-list">
                    @foreach($lessonPlan->topics as $topic)
                    @php $tStats = $topic->completionStats(); @endphp
                    <li class="list-group-item py-2 px-3 small topic-summary-item" data-topic-id="{{ $topic->id }}">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="font-weight-bold text-truncate" style="max-width:160px;">{{ $topic->title }}</span>
                            <span class="topic-summary-pct text-{{ $tStats['pct'] >= 80 ? 'success' : ($tStats['pct'] >= 50 ? 'warning' : 'muted') }}">
                                {{ $tStats['pct'] }}%
                            </span>
                        </div>
                        <div class="progress" style="height:5px;">
                            <div class="progress-bar bg-{{ $tStats['pct'] >= 80 ? 'success' : ($tStats['pct'] >= 50 ? 'warning' : 'secondary') }} topic-summary-bar"
                                 style="width:{{ $tStats['pct'] }}%"></div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

    </div>
</div>

{{-- Cover modal --}}
<div class="modal fade" id="coverModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fas fa-calendar-check mr-1"></i>Mark as Covered</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Date Covered</label>
                    <input type="date" id="cover-date" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Notes <span class="text-muted font-weight-normal">(optional)</span></label>
                    <textarea id="cover-notes" class="form-control form-control-sm" rows="2" placeholder="Any additional notes…"></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" id="btn-cover-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="btn-cover-confirm">
                    <i class="fas fa-check mr-1"></i>Mark Covered
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     Lesson Plan Generator Modal
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="lessonPlanModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header py-2 bg-info text-white">
                <h5 class="modal-title mb-0">
                    <i class="fas fa-file-alt mr-2"></i>
                    Lesson Plan — <span id="lp-subtopic-title"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">

                {{-- Step 1: Input form --}}
                <div id="lp-form-section" class="p-4">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-robot mr-1 text-info"></i>
                        Fill in the details below. The AI will generate a complete Tanzania MoEST-format lesson plan for this subtopic.
                    </p>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold small">Duration (minutes) <span class="text-danger">*</span></label>
                                <select id="lp-duration" class="form-control form-control-sm">
                                    <option value="30">30 min</option>
                                    <option value="40" selected>40 min</option>
                                    <option value="45">45 min</option>
                                    <option value="60">60 min</option>
                                    <option value="80">80 min (double)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold small">Number of Students</label>
                                <input type="number" id="lp-num-students" class="form-control form-control-sm" placeholder="e.g. 35" min="1" max="200">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Entry Behavior / Prior Knowledge</label>
                        <textarea id="lp-entry-behavior" class="form-control form-control-sm" rows="2"
                            placeholder="What should students already know before this lesson? e.g. Students should know basic algebra and can solve simple equations."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Available Teaching/Learning Materials</label>
                        <textarea id="lp-materials" class="form-control form-control-sm" rows="2"
                            placeholder="e.g. Chalkboard, textbook, charts, calculator, specimens…"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Additional Notes / Focus Areas <span class="text-muted font-weight-normal">(optional)</span></label>
                        <textarea id="lp-extra-notes" class="form-control form-control-sm" rows="2"
                            placeholder="Any special emphasis, challenges, or specific methods you want included…"></textarea>
                    </div>
                </div>

                {{-- Step 2: Generated plan --}}
                <div id="lp-result-section" class="d-none">
                    <div class="bg-light border-bottom px-4 py-2 d-flex align-items-center justify-content-between">
                        <span class="text-success font-weight-bold small"><i class="fas fa-check-circle mr-1"></i>Lesson plan generated</span>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary mr-2" id="lp-btn-back">
                                <i class="fas fa-arrow-left mr-1"></i>Edit Inputs
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="printLessonPlan()">
                                <i class="fas fa-print mr-1"></i>Print
                            </button>
                        </div>
                    </div>
                    <div id="lp-plan-output" class="p-4" style="font-size:13px; line-height:1.6;"></div>
                </div>

                {{-- Loading state --}}
                <div id="lp-loading-section" class="d-none text-center py-5">
                    <div class="spinner-border text-info mb-3" role="status"></div>
                    <p class="text-muted">Generating lesson plan… this may take 10–20 seconds.</p>
                </div>

            </div>
            <div class="modal-footer py-2" id="lp-footer-form">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info btn-sm" id="lp-btn-generate">
                    <i class="fas fa-robot mr-1"></i>Generate Lesson Plan
                </button>
            </div>
            <div class="modal-footer py-2 d-none" id="lp-footer-result">
                <small class="text-muted mr-auto"><i class="fas fa-save mr-1"></i>Plan saved automatically to this subtopic.</small>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit subtopic modal --}}
<div class="modal fade" id="editSubtopicModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Edit Subtopic</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-sub-id">
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Title</label>
                    <input type="text" id="edit-sub-title" class="form-control form-control-sm" maxlength="200">
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Notes</label>
                    <textarea id="edit-sub-notes" class="form-control form-control-sm" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-save-sub-edit">Save</button>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
.subtopic-actions { display:flex; gap:4px; align-items:center; flex-shrink:0; }
.subtopic-status-icon { font-size:15px; min-width:15px; }
.text-decoration-line-through { text-decoration: line-through; }
.drag-handle { cursor:grab; }
.topic-card .card-header { background:#f8f9fa; }

/* Lesson plan print styles */
#lp-plan-output table { width:100%; border-collapse:collapse; margin-bottom:1rem; font-size:12px; }
#lp-plan-output table td, #lp-plan-output table th { border:1px solid #dee2e6; padding:6px 8px; vertical-align:top; }
#lp-plan-output table th { background:#f8f9fa; font-weight:600; }
#lp-plan-output h5 { font-weight:700; margin-top:1.2rem; margin-bottom:.5rem; font-size:13px; text-transform:uppercase; border-bottom:1px solid #dee2e6; padding-bottom:3px; }
#lp-plan-output ul { padding-left:1.2rem; }

@media print {
    body > * { display:none !important; }
    #print-area { display:block !important; }
    #print-area { font-size:11pt; line-height:1.5; }
    #print-area table { width:100%; border-collapse:collapse; }
    #print-area table td, #print-area table th { border:1px solid #000; padding:5px; vertical-align:top; }
    #print-area h5 { font-weight:bold; margin-top:1rem; font-size:11pt; border-bottom:1px solid #000; }
}
</style>
@endpush

@push('js')
<script>
$(function () {
    const planId  = {{ $lessonPlan->id }};
    const canEdit = {{ $canEdit ? 'true' : 'false' }};

    let pendingBtn        = null;
    let pendingSubtopicId = null;

    // ── Helpers ───────────────────────────────────────────────────────────
    function showToast(msg, type = 'success') {
        const bg = type === 'success' ? '#28a745' : '#dc3545';
        const toast = $(`<div style="position:fixed;top:20px;right:20px;z-index:9999;
            background:${bg};color:#fff;padding:10px 18px;border-radius:6px;
            box-shadow:0 2px 8px rgba(0,0,0,.2);font-size:14px;">${msg}</div>`);
        $('body').append(toast);
        setTimeout(() => toast.fadeOut(400, () => toast.remove()), 3000);
    }

    function updateMainStats(stats) {
        $('#covered-count').text(stats.covered);
        $('#total-count').text(stats.total);
        const pct = stats.pct;
        $('#main-progress').css('width', pct + '%');
        $('#main-pct').text(pct + '%')
            .removeClass('text-success text-warning text-danger')
            .addClass(pct >= 80 ? 'text-success' : pct >= 50 ? 'text-warning' : 'text-danger');
    }

    function updateTopicBadge(topicId) {
        const card    = $(`.topic-card[data-topic-id="${topicId}"]`);
        const total   = card.find('.subtopic-item').length;
        const covered = card.find('.subtopic-item[data-status="covered"]').length;
        const pct     = total > 0 ? Math.round((covered / total) * 100) : 0;
        const color   = pct >= 80 ? 'success' : pct >= 50 ? 'warning' : 'secondary';

        card.find('.topic-pct-badge')
            .text(pct + '%')
            .attr('class', `badge badge-${color} mr-2 topic-pct-badge`);

        const summaryItem = $(`.topic-summary-item[data-topic-id="${topicId}"]`);
        summaryItem.find('.topic-summary-pct')
            .text(pct + '%')
            .attr('class', `topic-summary-pct text-${pct >= 80 ? 'success' : pct >= 50 ? 'warning' : 'muted'}`);
        summaryItem.find('.topic-summary-bar')
            .css('width', pct + '%')
            .attr('class', `progress-bar bg-${pct >= 80 ? 'success' : pct >= 50 ? 'warning' : 'secondary'} topic-summary-bar`);
    }

    // ── Add Topic ─────────────────────────────────────────────────────────
    function doAddTopic() {
        const title = $('#new-topic-input').val().trim();
        if (!title) return;

        $.post(`/topic-coverage/${planId}/topics`, { _token: '{{ csrf_token() }}', title })
            .done(function (topic) {
                $('#empty-topics-msg').remove();
                $('#topics-container').append(buildTopicHtml(topic));
                $('#topic-summary-list').append(buildSummaryItem(topic));
                $('#new-topic-input').val('');
                showToast('Topic added.');
            })
            .fail(() => showToast('Failed to add topic.', 'error'));
    }

    $('#btn-add-topic').on('click', doAddTopic);
    $('#new-topic-input').on('keydown', e => { if (e.key === 'Enter') doAddTopic(); });

    function buildTopicHtml(topic) {
        return `<div class="card shadow-sm mb-3 topic-card" data-topic-id="${topic.id}">
            <div class="card-header d-flex align-items-center py-2">
                <i class="fas fa-grip-vertical text-muted mr-2 drag-handle"></i>
                <span class="topic-title font-weight-bold flex-grow-1">${escHtml(topic.title)}</span>
                <span class="badge badge-secondary mr-2 topic-pct-badge">0%</span>
                <button class="btn btn-xs btn-outline-primary mr-1 btn-edit-topic" title="Rename topic"><i class="fas fa-pencil-alt"></i></button>
                <button class="btn btn-xs btn-outline-danger btn-delete-topic" title="Delete topic"><i class="fas fa-trash"></i></button>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush subtopics-list"></ul>
                <div class="px-3 py-2 border-top bg-light">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control input-new-subtopic" placeholder="Add subtopic…" maxlength="200">
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary btn-add-subtopic" type="button"><i class="fas fa-plus"></i> Add</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    }

    function buildSummaryItem(topic) {
        return `<li class="list-group-item py-2 px-3 small topic-summary-item" data-topic-id="${topic.id}">
            <div class="d-flex justify-content-between mb-1">
                <span class="font-weight-bold text-truncate" style="max-width:160px;">${escHtml(topic.title)}</span>
                <span class="topic-summary-pct text-muted">0%</span>
            </div>
            <div class="progress" style="height:5px;">
                <div class="progress-bar bg-secondary topic-summary-bar" style="width:0%"></div>
            </div>
        </li>`;
    }

    // ── Edit / Delete Topic ───────────────────────────────────────────────
    $(document).on('click', '.btn-edit-topic', function () {
        const card    = $(this).closest('.topic-card');
        const topicId = card.data('topic-id');
        const span    = card.find('.topic-title');
        const current = span.text().trim();
        const newTitle = prompt('Rename topic:', current);
        if (!newTitle || newTitle.trim() === current) return;

        $.ajax({ url: `/lesson-topics/${topicId}`, method: 'PUT',
            data: { _token: '{{ csrf_token() }}', title: newTitle.trim() } })
            .done(() => {
                span.text(newTitle.trim());
                $(`.topic-summary-item[data-topic-id="${topicId}"] .font-weight-bold`).text(newTitle.trim());
                showToast('Topic renamed.');
            })
            .fail(() => showToast('Failed to rename.', 'error'));
    });

    $(document).on('click', '.btn-delete-topic', function () {
        const card    = $(this).closest('.topic-card');
        const topicId = card.data('topic-id');
        if (!confirm('Delete this topic and all its subtopics?')) return;

        $.ajax({ url: `/lesson-topics/${topicId}`, method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' } })
            .done(function () {
                card.remove();
                $(`.topic-summary-item[data-topic-id="${topicId}"]`).remove();
                if ($('.topic-card').length === 0) {
                    $('#topics-container').prepend(
                        `<div class="text-center text-muted py-4" id="empty-topics-msg">
                            <i class="fas fa-list fa-3x mb-2 text-light"></i>
                            <p>No topics yet. Add your first topic on the right.</p>
                        </div>`
                    );
                }
                showToast('Topic deleted.');
            })
            .fail(() => showToast('Failed to delete.', 'error'));
    });

    // ── Add Subtopic ──────────────────────────────────────────────────────
    $(document).on('click', '.btn-add-subtopic', function () {
        const card    = $(this).closest('.topic-card');
        const topicId = card.data('topic-id');
        const input   = card.find('.input-new-subtopic');
        const title   = input.val().trim();
        if (!title) return;

        $.post(`/lesson-topics/${topicId}/subtopics`, { _token: '{{ csrf_token() }}', title })
            .done(function (sub) {
                card.find('.subtopics-list').append(buildSubtopicHtml(sub));
                input.val('');
                updateTopicBadge(topicId);
                showToast('Subtopic added.');
            })
            .fail(() => showToast('Failed to add subtopic.', 'error'));
    });

    $(document).on('keydown', '.input-new-subtopic', function (e) {
        if (e.key === 'Enter') $(this).closest('.card-body').find('.btn-add-subtopic').click();
    });

    function buildSubtopicHtml(sub) {
        const editBtns = canEdit
            ? `<button class="btn btn-xs btn-outline-secondary btn-edit-subtopic" title="Edit"><i class="fas fa-pencil-alt"></i></button>
               <button class="btn btn-xs btn-outline-danger btn-delete-subtopic" title="Delete"><i class="fas fa-times"></i></button>`
            : '';
        return `<li class="list-group-item px-3 py-2 subtopic-item d-flex align-items-center" data-subtopic-id="${sub.id}" data-status="pending">
            <i class="fas fa-circle text-muted mr-3 subtopic-status-icon"></i>
            <div class="flex-grow-1">
                <span class="subtopic-title">${escHtml(sub.title)}</span>
            </div>
            <div class="ml-2 subtopic-actions">
                <button class="btn btn-xs btn-success btn-toggle-covered"><i class="fas fa-check mr-1"></i>Covered</button>
                ${editBtns}
            </div>
        </li>`;
    }

    // ── Toggle covered / pending ──────────────────────────────────────────
    $(document).on('click', '.btn-toggle-covered', function () {
        const btn        = $(this);
        const item       = btn.closest('.subtopic-item');
        const subtopicId = item.data('subtopic-id');
        const isCovered  = item.data('status') === 'covered';

        if (isCovered) {
            toggleSubtopic(subtopicId, 'pending', null, null, btn);
        } else {
            pendingBtn        = btn;
            pendingSubtopicId = subtopicId;
            $('#cover-date').val('{{ now()->toDateString() }}');
            $('#cover-notes').val('');
            $('#coverModal').modal('show');
        }
    });

    $('#btn-cover-cancel').on('click', function () {
        pendingBtn        = null;
        pendingSubtopicId = null;
    });

    $('#btn-cover-confirm').on('click', function () {
        if (!pendingSubtopicId) return;
        const date  = $('#cover-date').val();
        const notes = $('#cover-notes').val().trim();
        const btn   = pendingBtn;
        const id    = pendingSubtopicId;
        pendingBtn        = null;
        pendingSubtopicId = null;
        $('#coverModal').modal('hide');
        toggleSubtopic(id, 'covered', date, notes, btn);
    });

    function toggleSubtopic(subtopicId, status, date, notes, btn) {
        $.ajax({
            url:    `/lesson-subtopics/${subtopicId}/toggle`,
            method: 'PATCH',
            data:   { _token: '{{ csrf_token() }}', status, date_covered: date, notes },
        }).done(function (res) {
            const item     = $(`.subtopic-item[data-subtopic-id="${subtopicId}"]`);
            const topicId  = item.closest('.topic-card').data('topic-id');
            const rawTitle = item.find('.subtopic-title').text().trim();
            const icon     = item.find('.subtopic-status-icon');

            item.data('status', res.status);

            if (res.status === 'covered') {
                icon.attr('class', 'fas fa-check-circle text-success mr-3 subtopic-status-icon');
                let html = `<span class="subtopic-title text-muted text-decoration-line-through">${escHtml(rawTitle)}</span>`;
                html += `<br><small class="text-success"><i class="fas fa-calendar-check mr-1"></i>Covered ${res.date_covered}</small>`;
                if (notes) html += `<br><small class="text-info"><i class="fas fa-sticky-note mr-1"></i>${escHtml(notes)}</small>`;
                item.find('.flex-grow-1').html(html);
                btn.attr('class', 'btn btn-xs btn-outline-warning btn-toggle-covered')
                   .html('<i class="fas fa-undo mr-1"></i>Unmark');
            } else {
                icon.attr('class', 'fas fa-circle text-muted mr-3 subtopic-status-icon');
                item.find('.flex-grow-1').html(`<span class="subtopic-title">${escHtml(rawTitle)}</span>`);
                btn.attr('class', 'btn btn-xs btn-success btn-toggle-covered')
                   .html('<i class="fas fa-check mr-1"></i>Covered');
            }

            updateTopicBadge(topicId);
            updateMainStats(res.stats);
            showToast(status === 'covered' ? 'Marked as covered.' : 'Marked as pending.');
        }).fail(() => {
            showToast('Failed to update status.', 'error');
        });
    }

    // ── Edit Subtopic modal ───────────────────────────────────────────────
    $(document).on('click', '.btn-edit-subtopic', function () {
        const item  = $(this).closest('.subtopic-item');
        const id    = item.data('subtopic-id');
        const title = item.find('.subtopic-title').text().trim();
        const notes = item.find('.text-info').text().replace(/^.*note\s*/i, '').trim();
        $('#edit-sub-id').val(id);
        $('#edit-sub-title').val(title);
        $('#edit-sub-notes').val(notes);
        $('#editSubtopicModal').modal('show');
    });

    $('#btn-save-sub-edit').on('click', function () {
        const id    = $('#edit-sub-id').val();
        const title = $('#edit-sub-title').val().trim();
        const notes = $('#edit-sub-notes').val().trim();
        if (!title) return;

        $.ajax({ url: `/lesson-subtopics/${id}`, method: 'PUT',
            data: { _token: '{{ csrf_token() }}', title, notes } })
            .done(() => {
                const item = $(`.subtopic-item[data-subtopic-id="${id}"]`);
                item.find('.subtopic-title').text(title);
                $('#editSubtopicModal').modal('hide');
                showToast('Subtopic updated.');
            })
            .fail(() => showToast('Failed to update.', 'error'));
    });

    // ── Delete Subtopic ───────────────────────────────────────────────────
    $(document).on('click', '.btn-delete-subtopic', function () {
        const item    = $(this).closest('.subtopic-item');
        const id      = item.data('subtopic-id');
        const topicId = item.closest('.topic-card').data('topic-id');
        if (!confirm('Delete this subtopic?')) return;

        $.ajax({ url: `/lesson-subtopics/${id}`, method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' } })
            .done(() => {
                item.remove();
                updateTopicBadge(topicId);
                showToast('Subtopic deleted.');
            })
            .fail(() => showToast('Failed to delete.', 'error'));
    });

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Lesson Plan Generator ─────────────────────────────────────────────
    let currentSubtopicId = null;

    $(document).on('click', '.btn-generate-plan', function () {
        currentSubtopicId = $(this).data('subtopic-id');
        const title       = $(this).data('subtopic-title');
        const hasPlan     = $(this).data('has-plan') === 1 || $(this).data('has-plan') === '1';

        $('#lp-subtopic-title').text(title);
        lpShowForm();

        if (hasPlan) {
            // Load and show existing plan immediately, teacher can regenerate from form
            lpShowLoading();
            $.get(`/lesson-subtopics/${currentSubtopicId}/plan`)
                .done(function (res) {
                    if (res.content) {
                        lpShowResult(res.content);
                    } else {
                        lpShowForm();
                    }
                })
                .fail(function () { lpShowForm(); });
        }

        $('#lessonPlanModal').modal('show');
    });

    $('#lp-btn-generate').on('click', function () {
        if (!currentSubtopicId) return;

        const duration = $('#lp-duration').val();
        if (!duration) { alert('Please select a duration.'); return; }

        lpShowLoading();

        $.ajax({
            url:    `/lesson-subtopics/${currentSubtopicId}/generate-plan`,
            method: 'POST',
            data: {
                _token:         '{{ csrf_token() }}',
                duration:       duration,
                num_students:   $('#lp-num-students').val(),
                entry_behavior: $('#lp-entry-behavior').val(),
                materials:      $('#lp-materials').val(),
                extra_notes:    $('#lp-extra-notes').val(),
            }
        }).done(function (res) {
            if (res.success) {
                lpShowResult(res.content);
                // Update button to show plan exists
                $(`.btn-generate-plan[data-subtopic-id="${currentSubtopicId}"]`)
                    .attr('data-has-plan', '1')
                    .html('<i class="fas fa-file-alt mr-1"></i>Plan ✓')
                    .attr('title', 'View / Regenerate Lesson Plan');
            } else {
                lpShowForm();
                showToast(res.error || 'Generation failed.', 'error');
            }
        }).fail(function (xhr) {
            lpShowForm();
            const msg = xhr.responseJSON?.error || 'Failed to generate lesson plan.';
            showToast(msg, 'error');
        });
    });

    $('#lp-btn-back').on('click', lpShowForm);

    function lpShowForm() {
        $('#lp-form-section').removeClass('d-none');
        $('#lp-result-section').addClass('d-none');
        $('#lp-loading-section').addClass('d-none');
        $('#lp-footer-form').removeClass('d-none');
        $('#lp-footer-result').addClass('d-none');
    }

    function lpShowLoading() {
        $('#lp-form-section').addClass('d-none');
        $('#lp-result-section').addClass('d-none');
        $('#lp-loading-section').removeClass('d-none');
        $('#lp-footer-form').addClass('d-none');
        $('#lp-footer-result').addClass('d-none');
    }

    function lpShowResult(html) {
        $('#lp-plan-output').html(html);
        $('#lp-form-section').addClass('d-none');
        $('#lp-loading-section').addClass('d-none');
        $('#lp-result-section').removeClass('d-none');
        $('#lp-footer-form').addClass('d-none');
        $('#lp-footer-result').removeClass('d-none');
    }
});

function printLessonPlan() {
    const content = document.getElementById('lp-plan-output').innerHTML;
    const win = window.open('', '_blank', 'width=900,height=700');
    win.document.write(`<!DOCTYPE html><html><head>
        <title>Lesson Plan</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
        <style>
            body { font-size:11pt; padding:20px; }
            table { width:100%; border-collapse:collapse; margin-bottom:1rem; }
            table td, table th { border:1px solid #000; padding:6px 8px; vertical-align:top; }
            table th { background:#f0f0f0; font-weight:bold; }
            h5 { font-weight:bold; margin-top:1rem; border-bottom:1px solid #333; padding-bottom:3px; font-size:11pt; text-transform:uppercase; }
            @media print { body { margin:10mm; } }
        </style>
    </head><body>${content}</body></html>`);
    win.document.close();
    setTimeout(() => { win.print(); }, 500);
}
</script>
@endpush
