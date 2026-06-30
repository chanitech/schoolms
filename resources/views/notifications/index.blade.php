@extends('adminlte::page')
@section('title', 'Notifications')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold" style="font-size:1.3rem">
        <i class="far fa-bell mr-2" style="color:#4e73df"></i>Notifications
    </h1>
    @if($notifications->total() > 0)
    <form action="{{ route('notifications.mark-all-read') }}" method="POST">
        @csrf
        <button class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.8rem">
            <i class="fas fa-check-double mr-1"></i>Mark all read
        </button>
    </form>
    @endif
</div>
@endsection

@section('content')
<div class="container-fluid">

@forelse($notifications as $notif)
@php
    $data  = $notif->data;
    $read  = !is_null($notif->read_at);
    $colors = ['success'=>'#28a745','danger'=>'#dc3545','warning'=>'#f59e0b','info'=>'#17a2b8','secondary'=>'#6c757d'];
    $bg    = $colors[$data['color'] ?? 'info'] ?? '#4e73df';
@endphp
<div class="card mb-2 shadow-sm" style="border-radius:10px;border:1.5px solid {{ $read ? '#e3e6f0' : '#c3cfe2' }};{{ $read ? '' : 'background:#f0f4ff' }}">
    <div class="card-body py-3 d-flex align-items-start" style="gap:.9rem">
        <div style="width:38px;height:38px;border-radius:50%;background:{{ $bg }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="{{ $data['icon'] ?? 'fas fa-bell' }} text-white" style="font-size:.8rem"></i>
        </div>
        <div style="flex:1">
            <div class="d-flex justify-content-between align-items-start">
                <strong style="font-size:.88rem;color:#1a1a2e">{{ $data['title'] ?? 'Notification' }}</strong>
                <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
            </div>
            <p class="mb-1 text-muted" style="font-size:.82rem">{{ $data['message'] ?? '' }}</p>
            <div class="d-flex" style="gap:.5rem">
                @if(!empty($data['url']) && $data['url'] !== '#')
                <a href="{{ $data['url'] }}" class="btn btn-sm btn-outline-primary" style="border-radius:6px;font-size:.75rem;padding:.2rem .6rem">
                    <i class="fas fa-arrow-right mr-1"></i>View
                </a>
                @endif
                <form action="{{ route('notifications.destroy', $notif->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" style="border-radius:6px;font-size:.75rem;padding:.2rem .6rem">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @if(!$read)
        <div style="width:8px;height:8px;border-radius:50%;background:#4e73df;margin-top:6px;flex-shrink:0" title="Unread"></div>
        @endif
    </div>
</div>
@empty
<div class="text-center py-5 text-muted">
    <i class="far fa-bell-slash fa-3x d-block mb-3 opacity-50"></i>
    <p>No notifications yet.</p>
</div>
@endforelse

@if($notifications->hasPages())
<div class="mt-3">{{ $notifications->links() }}</div>
@endif

</div>
@endsection
