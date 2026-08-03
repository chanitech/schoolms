<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body  { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #111; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data th, table.data td { border: 1px solid #888; padding: 4px 5px; text-align: left; vertical-align: top; }
        table.data th { background: #eef2f7; font-size: 9px; text-transform: uppercase; }
        table.data tr:nth-child(even) td { background: #fafbfc; }
        .meta { font-size: 8.5px; color: #555; margin-bottom: 2px; }
        .sig-block {
            margin-top: 18px; padding: 8px 10px; border: 1.2px solid #333;
            background: #f8f9fa; font-size: 8.5px; page-break-inside: avoid;
        }
        .sig-title { font-weight: bold; font-size: 9.5px; margin-bottom: 3px; }
        .sig-code  { font-family: DejaVu Sans Mono, monospace; font-weight: bold; font-size: 11px; letter-spacing: 1px; }
        .footer { position: fixed; bottom: -8mm; left: 0; right: 0; font-size: 7.5px; color: #777; text-align: center; }
    </style>
</head>
<body>
    @include('partials.pdf-letterhead', ['documentTitle' => strtoupper($title)])

    <div class="meta">{{ $summary }} — generated {{ now()->format('d M Y H:i') }}</div>

    <table class="data">
        <thead>
            <tr>
                <th style="width:18px">#</th>
                @foreach($columns as $col)<th>{{ $col }}</th>@endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                @foreach($row as $cell)<td>{{ $cell }}</td>@endforeach
            </tr>
            @empty
            <tr><td colspan="{{ count($columns) + 1 }}" style="text-align:center; padding:12px;">No records match the selected filters.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Digital signature block --}}
    <div class="sig-block">
        <div class="sig-title">DIGITAL SIGNATURE &amp; INTEGRITY</div>
        <table style="width:100%; border-collapse:collapse; font-size:8.5px;">
            <tr>
                <td style="width:55%; vertical-align:top; padding-right:10px;">
                    Generated electronically by <strong>{{ $signature->signer->name ?? 'System' }}</strong>
                    on {{ $signature->created_at->format('d M Y, H:i:s') }}.<br>
                    Every approval shown in this report was recorded in the system by the named
                    officer at the stated time. This document requires no handwritten signature.<br><br>
                    <strong>Verify this document:</strong> visit
                    <em>{{ route('verify.document') }}</em> and enter the code.
                </td>
                <td style="vertical-align:top; text-align:right;">
                    Verification code<br>
                    <span class="sig-code">{{ $signature->code }}</span><br><br>
                    Integrity hash (SHA-256)<br>
                    <span style="font-family:monospace; font-size:7.5px;">{{ substr($signature->content_hash, 0, 32) }}…</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ $title }} · {{ $signature->code }} · unauthorised alteration invalidates this document<br>
        Powered by <strong>ShulePRO</strong> — a Chani Technologies product · +255 713 209 535 · www.chanitech.co.tz · info@chanitech.co.tz
    </div>
</body>
</html>
