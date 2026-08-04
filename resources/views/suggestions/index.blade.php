@extends('adminlte::page')

@section('title', 'My Suggestions')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-comment-dots mr-2"></i>My Suggestions</h1>
        <div>
            @if($canManage)
            <a href="{{ route('suggestions.manage') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-inbox mr-1"></i> Manage All
            </a>
            @endif
            <a href="{{ route($baseRoute . '.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> New Suggestion
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')

    <div class="callout callout-info py-2">
        <i class="fas fa-info-circle mr-1"></i>
        Use this to send a suggestion, opinion, complaint, or compliment to the school administration.
        Submissions sent anonymously don't appear in this list, since we don't record who sent them.
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Response</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mine as $s)
                    @php $badge = ['new' => 'secondary', 'in_review' => 'warning', 'resolved' => 'success', 'dismissed' => 'danger'][$s->status]; @endphp
                    <tr>
                        <td class="small">{{ $s->created_at->format('d M Y') }}</td>
                        <td><span class="badge badge-light border">{{ ucfirst($s->category) }}</span></td>
                        <td>{{ $s->subject }}</td>
                        <td><span class="badge badge-{{ $badge }}">{{ ucfirst(str_replace('_', ' ', $s->status)) }}</span></td>
                        <td class="small">{{ $s->admin_response ? \Illuminate\Support\Str::limit($s->admin_response, 60) : '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">You haven't submitted anything yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
