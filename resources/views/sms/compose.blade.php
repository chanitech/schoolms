@extends('adminlte::page')

@section('title', 'Send SMS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-sms mr-2"></i>Send SMS</h1>
        <a href="{{ route('sms.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-history mr-1"></i> Sent Messages
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @unless(config('services.beem.api_key') && config('services.beem.secret_key'))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle mr-1"></i>
        SMS gateway is not configured yet — set <code>BEEM_API_KEY</code> and <code>BEEM_SECRET_KEY</code> in <code>.env</code> before sending.
    </div>
    @endunless

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header"><h3 class="card-title">Compose Message</h3></div>
        <form method="POST" action="{{ route('sms.send') }}">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Send to</label>
                    <select name="audience" id="audience" class="form-control" required>
                        <option value="all_guardians" {{ old('audience') == 'all_guardians' ? 'selected' : '' }}>All parents/guardians</option>
                        <option value="class_guardians" {{ old('audience') == 'class_guardians' ? 'selected' : '' }}>Parents/guardians of one class</option>
                        <option value="all_staff" {{ old('audience') == 'all_staff' ? 'selected' : '' }}>All staff</option>
                        <option value="custom" {{ old('audience') == 'custom' ? 'selected' : '' }}>Custom phone numbers</option>
                    </select>
                </div>

                <div class="form-group" id="classField" style="display:none">
                    <label>Class</label>
                    <select name="class_id" class="form-control">
                        <option value="">Select a class</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="customField" style="display:none">
                    <label>Phone numbers</label>
                    <textarea name="custom_numbers" rows="3" class="form-control"
                        placeholder="0712345678, 0765432100, ...">{{ old('custom_numbers') }}</textarea>
                    <small class="text-muted">Separate with commas, spaces, or new lines.</small>
                </div>

                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" id="message" rows="4" maxlength="918" class="form-control" required>{{ old('message') }}</textarea>
                    <small class="text-muted">
                        <span id="charCount">0</span> characters ·
                        <span id="segCount">1</span> SMS segment(s) — each segment (~153 chars) is billed separately by the gateway.
                    </small>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Send this SMS now? This cannot be undone.')">
                    <i class="fas fa-paper-plane mr-1"></i> Send
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    const audience = document.getElementById('audience');
    const classField = document.getElementById('classField');
    const customField = document.getElementById('customField');
    function toggleFields() {
        classField.style.display = audience.value === 'class_guardians' ? 'block' : 'none';
        customField.style.display = audience.value === 'custom' ? 'block' : 'none';
    }
    audience.addEventListener('change', toggleFields);
    toggleFields();

    const messageEl = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    const segCount = document.getElementById('segCount');
    function updateCount() {
        const len = messageEl.value.length;
        charCount.textContent = len;
        segCount.textContent = Math.max(1, Math.ceil(len / 153));
    }
    messageEl.addEventListener('input', updateCount);
    updateCount();
</script>
@stop
