@extends('adminlte::page')

@section('title', 'Automatic SMS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="m-0"><i class="fas fa-robot mr-2"></i>Automatic SMS to Parents</h1>
        <a href="{{ route('sms.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-history mr-1"></i> Sent Messages
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @unless($configured)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle mr-1"></i>
        The SMS gateway is not connected yet. You can choose your settings now; messages start going out once
        <code>BEEM_API_KEY</code> and <code>BEEM_SECRET_KEY</code> are set.
    </div>
    @endunless

    @php $canEdit = auth()->user()->hasAnyRole(['Admin', 'Principal']) || auth()->user()->is_super_admin; @endphp

    <form method="POST" action="{{ route('sms.automatic.save') }}">
        @csrf
        <div class="row">
            <div class="col-lg-7">
                @php
                    $items = [
                        'absence'      => ['fas fa-user-times', 'Absent from school', 'When a student is marked <b>Absent</b> in today\'s register, their parent gets an SMS. Sent once per student per day.'],
                        'results'      => ['fas fa-poll', 'Results published', 'When the head of school publishes an exam, parents of students in that exam get one SMS.'],
                        'receipt'      => ['fas fa-receipt', 'Payment receipt', 'When a payment is recorded, the parent gets the amount, the remaining balance and the receipt number.'],
                        'fee_reminder' => ['fas fa-bell', 'Fee reminders', 'Each morning at 08:00, parents with an unpaid balance get a reminder before the due date and on the due date.'],
                    ];
                @endphp
                <div class="card card-outline card-primary">
                    <div class="card-header"><h3 class="card-title">What to send</h3></div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                        @foreach($items as $key => [$icon, $label, $help])
                            <li class="list-group-item">
                                <div class="d-flex align-items-start">
                                    <div class="custom-control custom-switch mt-1">
                                        <input type="checkbox" class="custom-control-input" id="sw-{{ $key }}" name="{{ $key }}" value="1"
                                               {{ $settings[$key] ? 'checked' : '' }} {{ $canEdit ? '' : 'disabled' }}>
                                        <label class="custom-control-label" for="sw-{{ $key }}"></label>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="font-weight-bold"><i class="{{ $icon }} mr-1 text-muted"></i>{{ $label }}
                                            @if(($counts[$key] ?? 0) > 0)
                                                <span class="badge badge-light border ml-1">{{ $counts[$key] }} sent in 30 days</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small">{!! $help !!}</div>
                                        <div class="border rounded bg-light p-2 mt-2 small preview" data-key="{{ $key }}">{{ $previews[$key] }}</div>
                                        @if($key === 'fee_reminder')
                                            <div class="border rounded bg-light p-2 mt-1 small">{{ $previews['fee_due'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card card-outline card-primary">
                    <div class="card-header"><h3 class="card-title">Message options</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="language">Language</label>
                            <select name="language" id="language" class="form-control" {{ $canEdit ? '' : 'disabled' }}>
                                <option value="sw" {{ $settings['language'] === 'sw' ? 'selected' : '' }}>Kiswahili</option>
                                <option value="en" {{ $settings['language'] === 'en' ? 'selected' : '' }}>English</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="signature">Sign messages as</label>
                            <input type="text" name="signature" id="signature" maxlength="30" class="form-control"
                                   value="{{ old('signature', $settings['signature']) }}" placeholder="{{ $school->name }}" {{ $canEdit ? '' : 'disabled' }}>
                            <small class="form-text text-muted">A short school name keeps each message to one SMS, e.g. "Kitungwa Sec".</small>
                        </div>
                        <div class="form-group">
                            <label for="reminder_days">Send fee reminders</label>
                            <select name="reminder_days" id="reminder_days" class="form-control" {{ $canEdit ? '' : 'disabled' }}>
                                @foreach([1, 2, 3, 5, 7, 14] as $d)
                                    <option value="{{ $d }}" {{ (int) $settings['reminder_days'] === $d ? 'selected' : '' }}>{{ $d }} {{ $d === 1 ? 'day' : 'days' }} before the due date</option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle mr-1"></i>Each SMS is paid for from the school's SMS balance. Messages longer than
                            160 characters count as two. Parents without a valid phone number are skipped and shown in Sent Messages.
                        </p>
                    </div>
                    <div class="card-footer">
                        @if($canEdit)
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save settings</button>
                        @else
                            <span class="text-muted small">Only the head of school or an admin can change these settings.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
// Show each sample message's length so long ones (two SMS) are obvious.
document.querySelectorAll('.preview, .preview + .border').forEach(function (el) {
    var n = el.textContent.trim().length, parts = n <= 160 ? 1 : Math.ceil(n / 153);
    var tag = document.createElement('div');
    tag.className = 'text-right text-muted mt-1';
    tag.style.fontSize = '.75rem';
    tag.textContent = n + ' characters · ' + parts + ' SMS';
    el.appendChild(tag);
});
</script>
@stop
