@php
    $s = $a['student'];
    $ranked = array_values($a['fits']);
    $top = array_slice(array_filter($ranked, fn ($f) => ! $f['missing']), 0, 3) ?: array_slice($ranked, 0, 3);
    $topCodes = array_column($top, 'code');
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Career Guidance — {{ $s->first_name }} {{ $s->last_name }}</title>
<style>
    @page { margin: 28px 34px 40px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
    h2 { font-size: 13px; text-align: center; margin: 4px 0 10px; letter-spacing: .5px; }
    h3 { font-size: 11px; margin: 12px 0 5px; border-bottom: 1px solid #999; padding-bottom: 2px; }
    table { width: 100%; border-collapse: collapse; }
    .grid td, .grid th { border: 1px solid #888; padding: 3px 5px; }
    .grid th { background: #eee; text-align: left; }
    .r { text-align: right; } .c { text-align: center; } .muted { color: #666; }
    .combo { border: 1px solid #888; padding: 5px 7px; margin-bottom: 6px; }
    .combo .head { font-weight: bold; font-size: 10.5px; }
    .ok { color: #1e7b34; } .warn { color: #9a6700; }
    .lines div { border-bottom: 1px dotted #888; height: 16px; }
    .sign td { width: 33%; padding-top: 22px; vertical-align: bottom; }
    .sign .line { border-top: 1px solid #333; padding-top: 2px; }
</style>
</head>
<body>
    @include('partials.pdf-letterhead')

    <h2>CAREER GUIDANCE REPORT</h2>

    <table class="grid">
        <tr><th style="width:18%">Student</th><td>{{ $s->first_name }} {{ $s->middle_name }} {{ $s->last_name }}</td>
            <th style="width:15%">Adm. No</th><td>{{ $s->admission_no }}</td></tr>
        <tr><th>Class</th><td>{{ $a['class'] }}</td><th>Date</th><td>{{ now()->format('d/m/Y') }}</td></tr>
    </table>

    @if($a['form'] && $a['form'] <= 2)
        <p class="muted">The student is in Form {{ $a['form'] }}. A-level combinations are chosen after Form IV; this report shows the direction to strengthen now.</p>
    @endif

    <h3>Predicted A-level combinations</h3>
    @if(! $a['subjects'])
        <p class="muted">No marks recorded yet.</p>
    @else
        @foreach($top as $i => $f)
            <div class="combo">
                <div class="head">{{ $i + 1 }}. {{ $f['code'] }} — {{ implode(', ', $f['needs']) }} <span style="float:right">Fit {{ $f['score'] }}</span></div>
                <div>
                    @if($f['ready'])<span class="ok">Credit (C) or better in all three subjects.</span>
                    @else
                        @foreach($f['gaps'] as $subj => $need)<span class="warn">{{ $subj }} needs +{{ $need }} for a credit. </span>@endforeach
                        @foreach($f['missing'] as $m)<span class="muted">No {{ $m }} marks yet. </span>@endforeach
                    @endif
                </div>
                <div><strong>Careers:</strong> {{ $f['careers'] }}</div>
                <div><strong>Typical university courses:</strong>
                    {{ collect($f['courses'])->map(fn ($c) => $c[0] . ' (' . $c[1] . ')')->implode('; ') }}</div>
            </div>
        @endforeach

        <table class="grid" style="margin-top:4px">
            <tr><th>Other combinations</th><th class="r">Fit</th><th>Status</th></tr>
            @foreach($ranked as $f)
                @continue(in_array($f['code'], $topCodes, true))
                <tr><td>{{ $f['code'] }} ({{ implode(', ', $f['needs']) }})</td><td class="r">{{ $f['score'] }}</td>
                    <td>@if($f['ready'])Ready @elseif($f['missing'])No {{ implode(', ', $f['missing']) }} marks @else{{ collect($f['gaps'])->map(fn ($g, $sub) => "$sub +$g")->implode(', ') }}@endif</td></tr>
            @endforeach
        </table>
    @endif

    <h3>Subject performance</h3>
    <table class="grid">
        <tr><th>Subject</th><th class="r">Average</th><th class="r">Class average</th><th class="c">Grade</th><th>Trend (first → latest exam)</th></tr>
        @forelse($a['subjects'] as $name => $p)
            <tr><td>{{ $name }}</td><td class="r">{{ $p['avg'] }}</td><td class="r">{{ $p['class_avg'] ?? '—' }}</td><td class="c">{{ $p['grade'] }}</td>
                <td>{{ $p['exams'] > 1 ? round($p['first']) . ' → ' . round($p['last']) . ' (' . ['up' => 'improving', 'down' => 'falling', 'flat' => 'steady'][$p['direction']] . ')' : 'one exam' }}</td></tr>
        @empty
            <tr><td colspan="5" class="muted">No marks yet.</td></tr>
        @endforelse
    </table>

    <table style="margin-top:8px"><tr>
        <td style="width:50%; vertical-align:top; padding-right:8px">
            <h3>Strength by area</h3>
            <table class="grid">@foreach($a['areas'] as $area => $v)<tr><td>{{ $area }}</td><td class="r">{{ $v }}</td></tr>@endforeach</table>
        </td>
        <td style="width:50%; vertical-align:top; padding-left:8px">
            @if($a['can']['aptitude'])
                <h3>Aptitude test</h3>
                @if($ap = $a['aptitude'])
                    <table class="grid">
                        @foreach($ap['sections'] as $sec => $v)<tr><td>{{ $sec }}</td><td class="r">{{ $v['got'] }} / {{ $v['max'] }} ({{ $v['pct'] }}%)</td></tr>@endforeach
                        <tr><th>Total ({{ $ap['date'] }})</th><th class="r">{{ $ap['total'] }} / {{ $ap['max'] }}</th></tr>
                    </table>
                @else <p class="muted">No aptitude test taken yet.</p> @endif
            @endif
        </td>
    </tr></table>

    @if($a['can']['interests'] && ($in = $a['interests']))
        <h3>Interests (inventory {{ $in['date'] }})</h3>
        <table class="grid">@foreach($in['answers'] as $label => $ans)<tr><td style="width:32%">{{ $label }}</td><td>{{ $ans }}</td></tr>@endforeach</table>
    @endif

    <h3>Counsellor's comments</h3>
    <div class="lines"><div></div><div></div><div></div></div>

    <table class="sign"><tr>
        <td><div class="line">Career counsellor — name &amp; signature</div></td>
        <td style="padding-left:14px"><div class="line">Parent / guardian — signature</div></td>
        <td style="padding-left:14px"><div class="line">Head of school — signature &amp; stamp</div></td>
    </tr></table>

    <p class="muted" style="margin-top:12px">Predictions use the average of all recorded exams in the O-level subjects each combination builds on; a credit (C) starts at {{ $a['credit'] }} marks. {{ $tcuNote }}</p>

    @include('partials.pdf-powered-by')
</body>
</html>
