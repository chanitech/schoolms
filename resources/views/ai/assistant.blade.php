@extends('adminlte::page')

@section('title', 'Chani Technologies AI Assistant')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm mr-3">
                <i class="fas fa-arrow-left mr-1"></i>Back
            </a>
            <h1 class="mb-0">
                <i class="fas fa-robot text-primary"></i> Chani Technologies AI
            </h1>
        </div>
        <div class="d-flex align-items-center mt-1 mt-md-0">
            <span class="badge badge-info mr-2"><i class="fas fa-microchip mr-1"></i>Chani Technologies AI</span>
            <span class="badge badge-success"><i class="fas fa-plug mr-1"></i>Live School Data</span>
        </div>
    </div>
@stop

@section('content')
<div class="row" style="height: calc(100vh - 175px); min-height: 500px;">

    {{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
    <div class="col-md-3 d-flex flex-column" style="height:100%;">

        {{-- New Chat --}}
        <button id="new-chat-btn" class="btn btn-primary btn-block mb-2">
            <i class="fas fa-plus mr-1"></i> New Conversation
        </button>

        {{-- Conversation list --}}
        <div class="card flex-fill mb-2" style="overflow:hidden;">
            <div class="card-header py-2 px-3">
                <small class="font-weight-bold text-muted"><i class="fas fa-history mr-1"></i>Recent Chats</small>
            </div>
            <div class="card-body p-0" style="overflow-y:auto; flex:1;">
                <div class="list-group list-group-flush" id="conversation-list">
                    @forelse($conversations as $conv)
                        <div class="conv-item list-group-item list-group-item-action p-2 d-flex align-items-center"
                             data-id="{{ $conv->id }}" data-title="{{ $conv->title ?? 'Untitled Chat' }}">
                            <div class="flex-fill overflow-hidden mr-1" style="cursor:pointer;" onclick="loadConversation({{ $conv->id }}, '{{ addslashes($conv->title ?? 'Untitled Chat') }}')">
                                <div class="text-truncate" style="max-width:160px; font-size:13px;">
                                    {{ $conv->title ?? 'Untitled Chat' }}
                                </div>
                                <small class="text-muted" style="font-size:10px;">{{ $conv->created_at->diffForHumans() }}</small>
                            </div>
                            <button class="btn btn-xs btn-link text-danger delete-conv-btn p-0 ml-1" data-id="{{ $conv->id }}" title="Delete">
                                <i class="fas fa-trash-alt fa-xs"></i>
                            </button>
                        </div>
                    @empty
                        <div id="no-convs" class="p-3 text-muted small text-center">
                            <i class="fas fa-comments fa-2x mb-2 d-block text-light"></i>
                            No conversations yet.<br>Start a new chat!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick Prompts --}}
        <div class="card mb-2">
            <div class="card-header py-2 px-3">
                <small class="font-weight-bold text-muted"><i class="fas fa-bolt mr-1"></i>Quick Prompts</small>
            </div>
            <div class="card-body p-2" style="max-height: 280px; overflow-y:auto;">
                <div class="mb-1"><small class="text-muted font-weight-bold">OVERVIEW</small></div>
                @foreach(['School overview', 'Finance summary', 'Library summary', 'Dormitory summary'] as $p)
                    <button class="btn btn-xs btn-outline-secondary mb-1 quick-prompt d-block w-100 text-left" data-prompt="{{ $p }}">{{ $p }}</button>
                @endforeach
                <div class="mb-1 mt-2"><small class="text-muted font-weight-bold">ACADEMICS</small></div>
                @foreach(['Top 5 students in school', 'List all classes', 'Which subjects each teacher teaches?', 'List all exams'] as $p)
                    <button class="btn btn-xs btn-outline-secondary mb-1 quick-prompt d-block w-100 text-left" data-prompt="{{ $p }}">{{ $p }}</button>
                @endforeach
                <div class="mb-1 mt-2"><small class="text-muted font-weight-bold">FINANCE & HR</small></div>
                @foreach(['Fee defaulters', 'Finance summary', 'Staff summary', 'Staff on leave', 'Staff loan summary'] as $p)
                    <button class="btn btn-xs btn-outline-secondary mb-1 quick-prompt d-block w-100 text-left" data-prompt="{{ $p }}">{{ $p }}</button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Chat Area ───────────────────────────────────────────────────── --}}
    <div class="col-md-9 d-flex flex-column" style="height:100%;">
        <div class="card flex-fill d-flex flex-column" style="overflow:hidden;">

            {{-- Header --}}
            <div class="card-header d-flex align-items-center py-2">
                <span class="badge badge-success mr-2" id="status-badge">
                    <i class="fas fa-circle" style="font-size:8px;"></i> Online
                </span>
                <span id="chat-title" class="font-weight-bold text-truncate flex-fill" style="max-width:400px;">
                    New Conversation
                </span>
                <span class="ml-auto small text-muted">
                    <i class="fas fa-database mr-1"></i>Live school data
                </span>
            </div>

            {{-- Messages --}}
            <div id="chat-messages" class="flex-fill" style="overflow-y:auto; padding:20px 24px; background:#f5f7fa;">

                {{-- Welcome bubble --}}
                <div class="ai-bubble" id="welcome-msg">
                    <div class="bubble-avatar"><i class="fas fa-robot"></i></div>
                    <div>
                        <div class="bubble-content">
                            <strong>👋 Hello! I'm Chani Technologies AI</strong>
                            <span class="badge badge-info ml-1" style="font-size:10px;">Powered by Chani Technologies</span>
                            <br><br>
                            I have <strong>live access</strong> to all school data — students, marks, classes, finance, staff, library, dormitories, loans, and more.
                            <br><br>
                            <strong>Try asking:</strong><br>
                            • <em>"Show school overview"</em><br>
                            • <em>"Top 5 students in Form 2"</em><br>
                            • <em>"Find student Amina"</em><br>
                            • <em>"Who has unpaid fees?"</em><br>
                            • <em>"Finance summary"</em><br>
                            • <em>"Staff on leave"</em>
                        </div>
                        <div class="bubble-time">Just now</div>
                    </div>
                </div>

            </div>

            {{-- Input area --}}
            <div class="card-footer py-2 px-3 bg-white border-top">
                <div class="input-group">
                    <textarea id="message-input" class="form-control" rows="1"
                        placeholder="Ask anything about the school..."
                        autocomplete="off" style="resize:none; border-radius:20px 0 0 20px; padding:8px 14px;"></textarea>
                    <div class="input-group-append">
                        <button class="btn btn-primary" id="send-btn" onclick="sendMessage()"
                                style="border-radius: 0 20px 20px 0;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted"><kbd>Enter</kbd> send &nbsp;·&nbsp; <kbd>Shift+Enter</kbd> newline</small>
                    <small id="char-count" class="text-muted">0 / 2000</small>
                    <span id="response-time" class="small text-muted"></span>
                </div>
            </div>

        </div>
    </div>

</div>
@stop

@push('css')
<style>
/* ── Layout ── */
.content-wrapper { overflow: hidden; }

/* ── Bubbles ── */
.ai-bubble, .user-bubble {
    display: flex;
    align-items: flex-start;
    margin-bottom: 18px;
}
.user-bubble { flex-direction: row-reverse; }

.bubble-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: #007bff; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0;
}
.user-bubble .bubble-avatar { background: #28a745; }

.bubble-content {
    max-width: 80%;
    background: #fff;
    border-radius: 0 14px 14px 14px;
    padding: 10px 14px;
    margin: 0 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    font-size: 13.5px;
    line-height: 1.65;
    word-break: break-word;
    position: relative;
}
.user-bubble .bubble-content {
    background: #007bff; color: #fff;
    border-radius: 14px 0 14px 14px;
}

/* ── Markdown in AI bubbles ── */
.bubble-content h1, .bubble-content h2 {
    font-size: 15px; font-weight: 700;
    margin: 12px 0 5px; border-bottom: 1px solid #e9ecef; padding-bottom: 3px;
}
.bubble-content h3 { font-size: 13.5px; font-weight: 700; margin: 10px 0 4px; }
.bubble-content h4 { font-size: 13px; font-weight: 700; margin: 8px 0 3px; }
.bubble-content ul, .bubble-content ol { padding-left: 18px; margin-bottom: 6px; }
.bubble-content li { margin-bottom: 2px; }
.bubble-content p { margin-bottom: 6px; }
.bubble-content strong { font-weight: 700; }
.bubble-content em { font-style: italic; }
.bubble-content hr { border-top: 1px solid #eee; margin: 8px 0; }
.bubble-content code {
    background: #f1f3f5; border-radius: 3px; padding: 1px 5px;
    font-size: 12px; color: #e83e8c;
}
.user-bubble .bubble-content code { background: rgba(255,255,255,0.25); color: #fff; }
.bubble-content pre { background: #f1f3f5; border-radius: 6px; padding: 8px; overflow-x: auto; margin: 6px 0; }
.bubble-content table { width:100%; border-collapse:collapse; margin:8px 0; font-size:12px; }
.bubble-content th { background:#e9ecef; font-weight:600; padding:5px 8px; border:1px solid #dee2e6; text-align:left; }
.bubble-content td { padding:4px 8px; border:1px solid #dee2e6; }
.bubble-content tr:nth-child(even) td { background:#f8f9fa; }
.user-bubble .bubble-content table th { background:rgba(255,255,255,0.2); }
.user-bubble .bubble-content table th,
.user-bubble .bubble-content table td { border-color:rgba(255,255,255,0.25); }
.bubble-content blockquote { border-left:3px solid #007bff; padding-left:10px; color:#6c757d; margin:6px 0; }

/* ── Copy button on AI bubble ── */
.bubble-copy-btn {
    position: absolute; top: 6px; right: 8px;
    background: transparent; border: none;
    color: #adb5bd; font-size: 11px; cursor: pointer;
    padding: 2px 4px; border-radius: 3px; display: none;
}
.ai-bubble .bubble-content:hover .bubble-copy-btn { display: block; }
.bubble-copy-btn:hover { color: #007bff; background: #f0f4ff; }

/* ── Time stamp ── */
.bubble-time { font-size: 11px; color: #adb5bd; margin-top: 4px; padding: 0 10px; }
.user-bubble .bubble-time { text-align: right; }

/* ── Typing dots ── */
.typing-dots span {
    display:inline-block; width:8px; height:8px; border-radius:50%;
    background: #adb5bd; margin: 0 2px;
    animation: bounce-dot 1.3s infinite;
}
.typing-dots span:nth-child(2) { animation-delay:.15s; }
.typing-dots span:nth-child(3) { animation-delay:.3s; }
@keyframes bounce-dot {
    0%,60%,100% { transform:translateY(0); }
    30%          { transform:translateY(-7px); }
}

/* ── Tool call details ── */
.tool-call-details {
    background: #f5f7fa; border: 1px solid #e9ecef;
    border-radius: 6px; padding: 5px 10px;
    margin-top: 8px; font-size: 11px; font-family: monospace;
}
.tool-call-details summary { font-weight: 600; color: #6c757d; cursor: pointer; user-select: none; }
.tool-call-details ul { margin: 4px 0; padding-left: 14px; }
.tool-call-details li { color: #495057; }

/* ── Conversation items ── */
.conv-item { border-left: 3px solid transparent; transition: border-color .15s; }
.conv-item.active { border-left-color: #007bff; background: #f0f5ff; }
.conv-item:hover { background: #f8f9fa; }
.delete-conv-btn { opacity: 0; transition: opacity .15s; }
.conv-item:hover .delete-conv-btn { opacity: 1; }

/* ── Quick prompts ── */
.quick-prompt { font-size: 11.5px; white-space: normal; text-align: left; }

/* ── Status badge ── */
#status-badge { font-size: 11px; padding: 4px 8px; }

/* ── Textarea auto-resize ── */
#message-input { overflow-y: hidden; min-height: 36px; max-height: 120px; }

/* ── Responsive ── */
@media (max-width: 768px) {
    .bubble-content { max-width: 92%; }
    .col-md-3 { height: auto !important; margin-bottom: 1rem; }
}
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
let currentConversationId = null;
let messageStartTime = null;

marked.setOptions({ breaks: true, gfm: true });

// ── Auto-resize textarea ──────────────────────────────────────────────────
const textarea = document.getElementById('message-input');
textarea.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    const len = this.value.length;
    $('#char-count').text(len + ' / 2000').toggleClass('text-danger', len > 1900);
});

// ── Key bindings ──────────────────────────────────────────────────────────
$('#message-input').on('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});

// ── New chat ──────────────────────────────────────────────────────────────
$('#new-chat-btn').on('click', function () {
    currentConversationId = null;
    $('#chat-title').text('New Conversation');
    $('#response-time').text('');
    $('#status-badge').removeClass('badge-danger badge-warning').addClass('badge-success').html('<i class="fas fa-circle" style="font-size:8px;"></i> Online');
    $('.conv-item').removeClass('active');
    $('#chat-messages').html(welcomeHtml());
    $('#message-input').val('').css('height','').focus();
    $('#char-count').text('0 / 2000');
});

// ── Load conversation ─────────────────────────────────────────────────────
function loadConversation(id, title) {
    currentConversationId = id;
    $('#chat-title').text(title);
    $('#response-time').text('');
    $('.conv-item').removeClass('active');
    $(`.conv-item[data-id="${id}"]`).addClass('active');
    $('#chat-messages').html('<div class="text-center p-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i><br><small class="mt-2 d-block">Loading…</small></div>');

    $.get('/ai-assistant/conversation/' + id, function (data) {
        $('#chat-messages').empty();
        data.messages.forEach(msg => appendMessage(msg.role, msg.content, msg.time, msg.function_calls));
        scrollBottom();
    }).fail(function () {
        $('#chat-messages').html('<div class="alert alert-danger m-3">Failed to load conversation.</div>');
    });
}

// ── Delete conversation ───────────────────────────────────────────────────
$(document).on('click', '.delete-conv-btn', function (e) {
    e.stopPropagation();
    const id  = $(this).data('id');
    const row = $(this).closest('.conv-item');
    if (!confirm('Delete this conversation?')) return;

    $.ajax({ url: '/ai-assistant/conversation/' + id, type: 'DELETE',
             data: { _token: '{{ csrf_token() }}' } })
    .done(function () {
        row.remove();
        if (currentConversationId == id) {
            currentConversationId = null;
            $('#chat-title').text('New Conversation');
            $('#chat-messages').html(welcomeHtml());
        }
        if ($('.conv-item').length === 0) {
            $('#conversation-list').html(`
                <div id="no-convs" class="p-3 text-muted small text-center">
                    <i class="fas fa-comments fa-2x mb-2 d-block text-light"></i>
                    No conversations yet.
                </div>`);
        }
    })
    .fail(() => alert('Failed to delete. Please try again.'));
});

// ── Quick prompts ─────────────────────────────────────────────────────────
$(document).on('click', '.quick-prompt', function () {
    $('#message-input').val($(this).data('prompt')).trigger('input');
    sendMessage();
});

// ── Send message ──────────────────────────────────────────────────────────
function sendMessage() {
    const message = $('#message-input').val().trim();
    if (!message) return;
    if (message.length > 2000) { alert('Message too long (max 2000 characters).'); return; }

    appendMessage('user', message, 'Just now', null);
    $('#message-input').val('').css('height','').prop('disabled', true);
    $('#char-count').text('0 / 2000');
    $('#send-btn').prop('disabled', true);
    setStatus('thinking');
    $('#response-time').text('');
    messageStartTime = Date.now();
    showTyping();

    $.post('/ai-assistant/send', {
        _token: '{{ csrf_token() }}',
        message: message,
        conversation_id: currentConversationId
    })
    .done(function (data) {
        removeTyping();
        currentConversationId = data.conversation_id;
        appendMessage('assistant', data.reply, data.timestamp, data.function_calls);

        const elapsed = ((Date.now() - messageStartTime) / 1000).toFixed(1);
        $('#response-time').html(`<i class="fas fa-clock mr-1"></i>${elapsed}s`);
        setStatus('online');

        // Add/update conversation in sidebar
        const shortTitle = message.length > 45 ? message.substring(0, 45) + '…' : message;
        let existing = $(`.conv-item[data-id="${data.conversation_id}"]`);
        if (existing.length === 0) {
            $('#no-convs').remove();
            $('#conversation-list').prepend(buildConvItem(data.conversation_id, shortTitle));
        }
        $('.conv-item').removeClass('active');
        $(`.conv-item[data-id="${data.conversation_id}"]`).addClass('active');
        $('#chat-title').text(shortTitle);
    })
    .fail(function (xhr) {
        removeTyping();
        setStatus('error');
        const err = xhr.responseJSON?.message || 'Connection error. Please try again.';
        appendMessage('assistant', '⚠️ ' + err, '', null);
        setTimeout(() => setStatus('online'), 3000);
    })
    .always(function () {
        $('#message-input').prop('disabled', false).focus();
        $('#send-btn').prop('disabled', false);
    });
}

// ── Append message ────────────────────────────────────────────────────────
function appendMessage(role, content, time, functionCalls) {
    const isUser   = role === 'user';
    const formatted = isUser ? escHtml(content).replace(/\n/g, '<br>') : marked.parse(content || '');

    // Tool calls panel
    let toolHtml = '';
    if (!isUser && functionCalls && functionCalls.length > 0) {
        const items = functionCalls.map(fc => {
            let args = '';
            try { args = JSON.stringify(JSON.parse(fc.function.arguments), null, 2); } catch(e) { args = fc.function.arguments; }
            return `<li><code>${escHtml(fc.function.name)}</code><pre style="font-size:10px;max-height:80px;overflow-y:auto;">${escHtml(args)}</pre></li>`;
        }).join('');
        toolHtml = `<details class="tool-call-details"><summary>🔧 ${functionCalls.length} tool call(s) used</summary><ul>${items}</ul></details>`;
    }

    // Copy button (AI only)
    const copyBtn = isUser ? '' : `<button class="bubble-copy-btn" onclick="copyBubble(this)" title="Copy response"><i class="fas fa-copy"></i></button>`;

    const html = `
        <div class="${isUser ? 'user-bubble' : 'ai-bubble'}">
            <div class="bubble-avatar"><i class="fas fa-${isUser ? 'user' : 'robot'}"></i></div>
            <div>
                <div class="bubble-content">
                    ${copyBtn}
                    ${formatted}
                    ${toolHtml}
                </div>
                <div class="bubble-time">${escHtml(time || '')}</div>
            </div>
        </div>`;
    $('#chat-messages').append(html);
    scrollBottom();
}

// ── Copy AI response ──────────────────────────────────────────────────────
function copyBubble(btn) {
    const content = $(btn).siblings().filter(function() { return !$(this).hasClass('tool-call-details'); }).text().trim();
    navigator.clipboard.writeText(content).then(() => {
        $(btn).html('<i class="fas fa-check"></i>').css('color','#28a745');
        setTimeout(() => $(btn).html('<i class="fas fa-copy"></i>').css('color',''), 2000);
    });
}

// ── Typing indicator ──────────────────────────────────────────────────────
function showTyping() {
    $('#chat-messages').append(`
        <div class="ai-bubble" id="typing-indicator">
            <div class="bubble-avatar"><i class="fas fa-robot"></i></div>
            <div class="bubble-content typing-dots" style="padding:14px 18px;">
                <span></span><span></span><span></span>
            </div>
        </div>`);
    scrollBottom();
}
function removeTyping() { $('#typing-indicator').remove(); }

// ── Status badge ──────────────────────────────────────────────────────────
function setStatus(state) {
    const badge = $('#status-badge');
    badge.removeClass('badge-success badge-warning badge-danger');
    const map = {
        online:   ['badge-success', '<i class="fas fa-circle" style="font-size:8px;"></i> Online'],
        thinking: ['badge-warning', '<i class="fas fa-spinner fa-spin" style="font-size:10px;"></i> Thinking…'],
        error:    ['badge-danger',  '<i class="fas fa-exclamation-circle" style="font-size:10px;"></i> Error'],
    };
    badge.addClass(map[state][0]).html(map[state][1]);
}

// ── Helpers ───────────────────────────────────────────────────────────────
function scrollBottom() {
    const el = document.getElementById('chat-messages');
    if (el) el.scrollTop = el.scrollHeight;
}

function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function welcomeHtml() {
    return `<div class="ai-bubble" id="welcome-msg">
        <div class="bubble-avatar"><i class="fas fa-robot"></i></div>
        <div>
            <div class="bubble-content">
                <strong>👋 Hello! I'm Chani Technologies AI</strong>
                <span class="badge badge-info ml-1" style="font-size:10px;">Powered by Chani Technologies</span>
                <br><br>
                I have <strong>live access</strong> to all school data.<br>Ask me anything about students, marks, fees, staff, library, or dormitories.
            </div>
            <div class="bubble-time">Just now</div>
        </div>
    </div>`;
}

function buildConvItem(id, title) {
    return `<div class="conv-item list-group-item list-group-item-action p-2 d-flex align-items-center active" data-id="${id}" data-title="${escHtml(title)}">
        <div class="flex-fill overflow-hidden mr-1" style="cursor:pointer;" onclick="loadConversation(${id}, '${escHtml(title)}')">
            <div class="text-truncate" style="max-width:160px; font-size:13px;">${escHtml(title)}</div>
            <small class="text-muted" style="font-size:10px;">Just now</small>
        </div>
        <button class="btn btn-xs btn-link text-danger delete-conv-btn p-0 ml-1" data-id="${id}" title="Delete">
            <i class="fas fa-trash-alt fa-xs"></i>
        </button>
    </div>`;
}
</script>
@endpush
