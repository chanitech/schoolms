{{--
    School letterhead for browser-printed pages (receipts, statements).
    Self-sources the current school's info — no hardcoded school identity.
    Optional: $documentTitle.
--}}
@php $li = \App\Models\SchoolInfo::first(); @endphp
<div style="text-align:center; border-bottom:2px solid #333; padding-bottom:8px; margin-bottom:12px;">
    @if($li?->logo)
        <img src="{{ asset('storage/' . $li->logo) }}" alt="Logo" style="max-height:64px; margin-bottom:4px;">
    @endif
    <div style="font-size:1.15rem; font-weight:700; text-transform:uppercase;">{{ $li->name ?? config('app.name') }}</div>
    @if($li?->motto)<div style="font-size:.8rem; font-style:italic;">{{ $li->motto }}</div>@endif
    <div style="font-size:.75rem; color:#555;">
        {{ collect([$li->address ?? null, $li->phone ?? null, $li->email ?? null, $li->website ?? null])->filter()->implode(' | ') }}
    </div>
    @isset($documentTitle)
        <div style="font-size:.95rem; font-weight:700; margin-top:6px; text-decoration:underline;">{{ $documentTitle }}</div>
    @endisset
</div>
