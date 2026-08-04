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
            <h5 class="nb-title">
                {{ $notice->title }}
                @if($notice->created_at->gt(now()->subDays(3)))
                    <span class="nb-new">New</span>
                @endif
            </h5>
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
