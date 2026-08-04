@extends('adminlte::page')

@section('title', 'Manage Suggestions')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-inbox mr-2"></i>Suggestions & Opinions Inbox</h1>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')

    <div class="row">
        @foreach([['New', 'new', 'secondary', 'fa-envelope'], ['In Review', 'in_review', 'warning', 'fa-search'], ['Resolved', 'resolved', 'success', 'fa-check'], ['Dismissed', 'dismissed', 'danger', 'fa-times']] as [$label, $key, $color, $icon])
        <div class="col-6 col-md-3">
            <div class="small-box-custom sb-{{ $color === 'secondary' ? 'blue' : ($color === 'warning' ? 'orange' : ($color === 'success' ? 'green' : 'red')) }}">
                <div class="sb-inner">
                    <h3>{{ $counts[$key] ?? 0 }}</h3>
                    <p>{{ $label }}</p>
                </div>
                <div class="sb-icon"><i class="fas {{ $icon }}"></i></div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <select name="status" class="form-control form-control-sm mr-2">
                    <option value="">All statuses</option>
                    @foreach(['new' => 'New', 'in_review' => 'In Review', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed'] as $sk => $sl)
                        <option value="{{ $sk }}" {{ request('status') === $sk ? 'selected' : '' }}>{{ $sl }}</option>
                    @endforeach
                </select>
                <select name="category" class="form-control form-control-sm mr-2">
                    <option value="">All types</option>
                    @foreach(['suggestion','opinion','complaint','compliment','other'] as $c)
                        <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('suggestions.manage') }}" class="btn btn-sm btn-outline-secondary ml-1">Reset</a>
            </form>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>From</th>
                        <th>Type</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suggestions as $s)
                    @php $badge = ['new' => 'secondary', 'in_review' => 'warning', 'resolved' => 'success', 'dismissed' => 'danger'][$s->status]; @endphp
                    <tr>
                        <td class="small">{{ $s->created_at->format('d M Y H:i') }}</td>
                        <td>
                            @if($s->is_anonymous)
                                <span class="text-muted"><i class="fas fa-user-secret mr-1"></i>Anonymous {{ $s->submitter_role }}</span>
                            @else
                                {{ $s->submitter->name ?? '—' }}
                                <div class="small text-muted">{{ $s->submitter_role }}</div>
                            @endif
                        </td>
                        <td><span class="badge badge-light border">{{ ucfirst($s->category) }}</span></td>
                        <td>{{ $s->subject }}</td>
                        <td><span class="badge badge-{{ $badge }}">{{ ucfirst(str_replace('_', ' ', $s->status)) }}</span></td>
                        <td class="text-right">
                            <button type="button" class="btn btn-xs btn-primary view-btn"
                                data-toggle="modal" data-target="#viewModal"
                                data-subject="{{ $s->subject }}"
                                data-message="{{ $s->message }}"
                                data-from="{{ $s->is_anonymous ? 'Anonymous ' . $s->submitter_role : ($s->submitter->name ?? '—') }}"
                                data-category="{{ ucfirst($s->category) }}"
                                data-date="{{ $s->created_at->format('d M Y, H:i') }}"
                                data-response="{{ $s->admin_response }}"
                                data-status="{{ $s->status }}"
                                data-action="{{ route('suggestions.respond', $s) }}">
                                <i class="fas fa-eye mr-1"></i>View / Respond
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No suggestions submitted yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $suggestions->links() }}</div>
    </div>
</div>

{{-- View / Respond modal --}}
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-comment-dots"></i> <span id="mSubject"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">
                    From <strong id="mFrom"></strong> &middot; <span id="mCategory"></span> &middot; <span id="mDate"></span>
                </p>
                <div class="border rounded p-3 bg-light mb-3" id="mMessage" style="white-space:pre-wrap"></div>

                <form id="respondForm" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Administration response</label>
                        <textarea name="admin_response" id="mResponse" rows="4" class="form-control" required maxlength="3000"
                            placeholder="Write your reply — visible to the sender if they submitted this with their name attached…"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Set status</label>
                        <select name="status" id="mStatus" class="form-control" required>
                            <option value="in_review">In Review</option>
                            <option value="resolved">Resolved</option>
                            <option value="dismissed">Dismissed</option>
                        </select>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Response</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
@include('partials.dashboard-widgets-css')
@stop

@section('js')
<script>
    $('.view-btn').click(function() {
        const d = $(this).data();
        $('#mSubject').text(d.subject);
        $('#mFrom').text(d.from);
        $('#mCategory').text(d.category);
        $('#mDate').text(d.date);
        $('#mMessage').text(d.message);
        $('#mResponse').val(d.response || '');
        $('#mStatus').val(d.status === 'new' ? 'in_review' : d.status);
        $('#respondForm').attr('action', d.action);
    });
</script>
@stop
