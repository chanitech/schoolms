{{--
    School letterhead for DomPDF documents. Self-sources the current
    school's info (tenant-scoped), so no template ever hardcodes a school.
    Optional: $documentTitle — printed under the school details.
--}}
@php
    $li = \App\Models\SchoolInfo::first();
    $liLogo = null;
    if ($li?->logo) {
        $liPath = storage_path('app/public/' . $li->logo);
        if (file_exists($liPath)) {
            $liExt  = strtolower(pathinfo($liPath, PATHINFO_EXTENSION));
            $liMime = in_array($liExt, ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/' . $liExt;
            $liLogo = "data:{$liMime};base64," . base64_encode(file_get_contents($liPath));
        }
    }
@endphp
<table style="width:100%; border-collapse:collapse; margin-bottom:12px; border-bottom:2px solid #333;">
    <tr>
        @if($liLogo)
        <td style="width:70px; vertical-align:middle; padding-bottom:6px;">
            <img src="{{ $liLogo }}" alt="Logo" style="max-height:60px; max-width:65px;">
        </td>
        @endif
        <td style="text-align:center; vertical-align:middle; padding-bottom:6px;">
            <div style="font-size:15px; font-weight:bold; text-transform:uppercase;">{{ $li->name ?? config('app.name') }}</div>
            @if($li?->motto)<div style="font-size:9px; font-style:italic;">{{ $li->motto }}</div>@endif
            <div style="font-size:8.5px; color:#444; margin-top:2px;">
                {{ collect([$li->address ?? null, $li->phone ?? null, $li->email ?? null, $li->website ?? null])->filter()->implode(' | ') }}
            </div>
            @isset($documentTitle)
                <div style="font-size:11px; font-weight:bold; margin-top:5px; text-decoration:underline;">{{ $documentTitle }}</div>
            @endisset
        </td>
        @if($liLogo)<td style="width:70px;"></td>@endif
    </tr>
</table>
