@extends('adminlte::page')

@section('title', 'Notice Board')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-bullhorn mr-2"></i>Notice Board</h1>
        @can('manage notices')
        <a href="{{ route('notices.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Post Notice
        </a>
        @endcan
    </div>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')

    @forelse($notices as $notice)
    <div class="card shadow-sm {{ $notice->pinned ? 'card-outline card-warning' : 'border-0' }}">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h5 class="mb-1">
                    @if($notice->pinned)<i class="fas fa-thumbtack text-warning mr-1" title="Pinned"></i>@endif
                    {{ $notice->title }}
                    <span class="badge badge-light border ml-1">{{ ucfirst($notice->audience) }}</span>
                </h5>
                @can('manage notices')
                <div>
                    <a href="{{ route('notices.edit', $notice) }}" class="btn btn-outline-secondary btn-xs">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('notices.destroy', $notice) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Remove this notice?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-xs"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
                @endcan
            </div>
            <p class="mb-1" style="white-space: pre-line;">{{ $notice->body }}</p>
            <small class="text-muted">
                {{ $notice->poster->name ?? 'Administration' }} &middot; {{ $notice->created_at->format('d M Y') }}
                @if($notice->expires_at)
                    &middot; Expires {{ $notice->expires_at->format('d M Y') }}
                @endif
            </small>
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">
        <i class="fas fa-bullhorn fa-2x mb-2"></i>
        <p>No notices posted yet.</p>
    </div>
    @endforelse

    {{ $notices->links() }}
</div>
@stop
