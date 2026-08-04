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
    @php
        $audienceClass = ['all' => 'nb-all', 'staff' => 'nb-staff', 'guardians' => 'nb-guardians'][$notice->audience];
        $posterName = $notice->poster->name ?? 'Administration';
        $initials = strtoupper(collect(explode(' ', $posterName))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode(''));
    @endphp
    <div class="nb-card {{ $audienceClass }} {{ $notice->pinned ? 'nb-pinned' : '' }}">
        @if($notice->pinned)
            <div class="nb-ribbon"><i class="fas fa-thumbtack mr-1"></i>Pinned</div>
        @endif
        <div class="nb-avatar">{{ $initials }}</div>
        <div class="nb-main">
            <div class="nb-head">
                <h5 class="nb-title">{{ $notice->title }}</h5>
                <span class="nb-audience">{{ ucfirst($notice->audience) }}</span>
            </div>
            <p class="nb-body">{{ $notice->body }}</p>
            <div class="nb-meta">
                <span><i class="fas fa-user-tie mr-1"></i>{{ $posterName }}</span>
                <span><i class="far fa-clock mr-1"></i>{{ $notice->created_at->format('d M Y') }}</span>
                @if($notice->expires_at)
                    <span class="nb-expiry"><i class="fas fa-hourglass-half mr-1"></i>Expires {{ $notice->expires_at->format('d M Y') }}</span>
                @endif
            </div>
        </div>
        @can('manage notices')
        <div class="nb-actions">
            <a href="{{ route('notices.edit', $notice) }}" class="btn btn-outline-secondary btn-xs" title="Edit">
                <i class="fas fa-edit"></i>
            </a>
            <form action="{{ route('notices.destroy', $notice) }}" method="POST"
                  onsubmit="return confirm('Remove this notice?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-xs" title="Delete"><i class="fas fa-trash"></i></button>
            </form>
        </div>
        @endcan
    </div>
    @empty
    <div class="nb-empty">
        <i class="fas fa-bullhorn"></i>
        <p>No notices posted yet.</p>
    </div>
    @endforelse

    {{ $notices->links() }}
</div>
@stop

@section('css')
<style>
.nb-card{
    display:flex; align-items:flex-start; gap:1rem;
    background:#fff; border-radius:10px; border-left:4px solid #cbd5e1;
    box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 10px rgba(0,0,0,.04);
    padding:1.1rem 1.3rem; margin-bottom:1rem; position:relative;
    transition:transform .15s ease, box-shadow .15s ease;
}
.nb-card:hover{ transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,.09); }
.nb-card.nb-all{ border-left-color:#6366f1; }
.nb-card.nb-staff{ border-left-color:#0284c7; }
.nb-card.nb-guardians{ border-left-color:#059669; }
.nb-card.nb-pinned{ background:linear-gradient(135deg,#fffbeb 0%,#ffffff 45%); border-left-color:#d97706; }

.nb-ribbon{
    position:absolute; top:-9px; right:16px; background:#d97706; color:#fff;
    font-size:.62rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase;
    padding:.22rem .6rem; border-radius:20px; box-shadow:0 2px 6px rgba(217,119,6,.4);
}

.nb-avatar{
    flex-shrink:0; width:44px; height:44px; border-radius:50%;
    background:#eef2ff; color:#4338ca; font-weight:700; font-size:.85rem;
    display:flex; align-items:center; justify-content:center;
}
.nb-card.nb-staff .nb-avatar{ background:#e0f2fe; color:#075985; }
.nb-card.nb-guardians .nb-avatar{ background:#d1fae5; color:#065f46; }

.nb-main{ flex:1; min-width:0; }
.nb-head{ display:flex; align-items:center; justify-content:space-between; gap:.6rem; flex-wrap:wrap; }
.nb-title{ font-weight:700; color:#1e293b; margin:0; font-size:.98rem; }
.nb-audience{
    font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em;
    color:#64748b; background:#f1f5f9; border-radius:20px; padding:.16rem .58rem; white-space:nowrap;
}
.nb-body{ color:#475569; font-size:.86rem; margin:.45rem 0 .55rem; white-space:pre-line; }
.nb-meta{ display:flex; flex-wrap:wrap; gap:1.1rem; font-size:.72rem; color:#94a3b8; }
.nb-expiry{ color:#b45309; }

.nb-actions{ display:flex; flex-direction:column; gap:.35rem; align-self:flex-start; }

.nb-empty{ text-align:center; padding:3.5rem 1rem; color:#94a3b8; }
.nb-empty i{ font-size:2.4rem; margin-bottom:.8rem; display:block; color:#cbd5e1; }

@media(max-width:576px){ .nb-card{ flex-wrap:wrap; } .nb-actions{ flex-direction:row; } }
</style>
@stop
