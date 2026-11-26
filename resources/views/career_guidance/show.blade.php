@extends('adminlte::page')

@php
    $s = $a['student'];
    $ranked = array_values($a['fits']);
    $top = array_slice(array_filter($ranked, fn ($f) => ! $f['missing']), 0, 3) ?: array_slice($ranked, 0, 3);
    $topCodes = array_column($top, 'code');
    $arrow = ['up' => 'fa-arrow-up text-success', 'down' => 'fa-arrow-down text-danger', 'flat' => 'fa-arrow-right text-muted'];
@endphp

@section('title', 'Career Guidance — ' . $s->first_name . ' ' . $s->last_name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0"><i class="fas fa-compass mr-2"></i>{{ $s->first_name }} {{ $s->middle_name }} {{ $s->last_name }}</h1>
            <small class="text-muted">{{ $a['class'] }} · Adm. {{ $s->admission_no }}@if($a['form']) · Form {{ $a['form'] }}@endif</small>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('career-guidance.index', ['class_id' => $s->class_id]) }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left mr-1"></i>Class</a>
            <a href="{{ route('career-guidance.report', $s) }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-print mr-1"></i>Print report</a>
            <button type="button" id="ask-ai" class="btn btn-primary btn-sm"><i class="fas fa-robot mr-1"></i>Ask AI for advice</button>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">

    @if($a['form'] && $a['form'] <= 2)
        <div class="alert alert-info"><i class="fas fa-info-circle mr-1"></i>
            {{ $s->first_name }} is in Form {{ $a['form'] }}. Combinations are chosen after Form IV, so read these as the direction to strengthen now, not a final choice.</div>
    @endif

    <div class="card" id="ai-card" style="display:none">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-robot mr-1"></i>AI career advice</h3></div>
        <div class="card-body" id="ai-body"></div>
    </div>

    {{-- Predicted combinations --}}
    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title">Predicted A-level combinations</h3>
            <div class="card-tools small text-muted pt-1">Credit (C) = {{ $a['credit'] }} marks</div></div>
        <div class="card-body">
            @if(! $a['subjects'])
                <p class="text-muted mb-0">No marks recorded yet — predictions appear after the first exam is entered.</p>
            @else
            <div class="row">
                @foreach($top as $i => $f)
                <div class="col-lg-4">
                    <div class="card border mb-3">
                        <div class="card-header py-2">
                            <span class="badge badge-dark mr-1">#{{ $i + 1 }}</span>
                            <strong>{{ $f['code'] }}</strong> <span class="text-muted small">— {{ implode(', ', $f['needs']) }}</span>
                            <span class="float-right font-weight-bold">{{ $f['score'] }}</span>
                        </div>
                        <div class="card-body py-2 small">
                            @if($f['ready'])
                                <div class="text-success mb-1"><i class="fas fa-check-circle mr-1"></i>Credit in all three subjects</div>
                            @else
                                @foreach($f['gaps'] as $subj => $need)
                                    <div class="text-warning mb-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $subj }}: needs +{{ $need }} for a credit</div>
                                @endforeach
                                @foreach($f['missing'] as $m)
                                    <div class="text-muted mb-1"><i class="fas fa-minus-circle mr-1"></i>No {{ $m }} marks yet</div>
                                @endforeach
                            @endif
                            @if($f['aptitude_pct'] !== null)
                                <div class="text-muted mb-1">{{ $f['aptitude'] }} aptitude: {{ $f['aptitude_pct'] }}%</div>
                            @endif
                            <div class="mt-2"><strong>Careers:</strong> {{ $f['careers'] }}</div>
                            <div class="mt-2"><strong>Typical university courses:</strong>
                                <ul class="pl-3 mb-0">
                                    @foreach($f['courses'] as [$course, $where])
                                        <li>{{ $course }} <span class="text-muted">({{ $where }})</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light"><tr><th>Other combinations</th><th>Builds on</th><th class="text-right">Fit</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($ranked as $f)
                    @continue(in_array($f['code'], $topCodes, true))
                    <tr>
                        <td><strong>{{ $f['code'] }}</strong></td>
                        <td>{{ implode(', ', $f['needs']) }}</td>
                        <td class="text-right">{{ $f['score'] }}</td>
                        <td class="small">
                            @if($f['ready']) <span class="text-success">Ready</span>
                            @elseif($f['missing']) <span class="text-muted">No {{ implode(', ', $f['missing']) }} marks</span>
                            @else <span class="text-warning">{{ collect($f['gaps'])->map(fn ($g, $sub) => "$sub +$g")->implode(', ') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    @if($a['a_level'])
        @php $c = config('career.combinations.' . $a['a_level']); @endphp
        <div class="card">
            <div class="card-header"><h3 class="card-title">University pathways for {{ $a['a_level'] }} (current A-level combination)</h3></div>
            <div class="card-body"><ul class="mb-0">@foreach($c['courses'] as [$course, $where])<li>{{ $course }} <span class="text-muted">({{ $where }})</span></li>@endforeach</ul></div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Subject performance</h3></div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light"><tr><th>Subject</th><th class="text-right">Average</th><th class="text-right">Class</th><th class="text-center">Grade</th><th>Trend</th></tr></thead>
                        <tbody>
                        @forelse($a['subjects'] as $name => $p)
                            <tr>
                                <td>{{ $name }}</td>
                                <td class="text-right font-weight-bold">{{ $p['avg'] }}</td>
                                <td class="text-right text-muted">{{ $p['class_avg'] ?? '—' }}</td>
                                <td class="text-center">{{ $p['grade'] }}</td>
                                <td class="small"><i class="fas {{ $arrow[$p['direction']] }} mr-1"></i>{{ $p['exams'] > 1 ? round($p['first']) . ' → ' . round($p['last']) : 'one exam' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted text-center py-3">No marks yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Strength by area</h3></div>
                <div class="card-body">
                    @forelse($a['areas'] as $area => $v)
                        <div class="d-flex justify-content-between small"><span>{{ $area }}</span><span>{{ $v }}</span></div>
                        <div class="progress progress-sm mb-2"><div class="progress-bar bg-primary" style="width: {{ min(100, $v) }}%"></div></div>
                    @empty
                        <p class="text-muted mb-0">No marks yet.</p>
                    @endforelse
                </div>
            </div>

            @if($a['can']['aptitude'])
            <div class="card">
                <div class="card-header"><h3 class="card-title">Aptitude test</h3></div>
                <div class="card-body">
                    @if($ap = $a['aptitude'])
                        <p class="small text-muted mb-2">{{ $ap['date'] }} · total {{ $ap['total'] }} of {{ $ap['max'] }} ({{ $ap['pct'] }}%)</p>
                        @foreach($ap['sections'] as $sec => $v)
                            <div class="d-flex justify-content-between small"><span>{{ $sec }}</span><span>{{ $v['got'] }} / {{ $v['max'] }}</span></div>
                            <div class="progress progress-sm mb-2"><div class="progress-bar bg-info" style="width: {{ $v['pct'] }}%"></div></div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No aptitude test taken yet.</p>
                    @endif
                </div>
            </div>
            @endif

            @if($a['can']['interests'])
            <div class="card">
                <div class="card-header"><h3 class="card-title">Interests</h3></div>
                <div class="card-body">
                    @if($in = $a['interests'])
                        <p class="small text-muted mb-2">Interest inventory, {{ $in['date'] }}</p>
                        <dl class="mb-0 small">
                            @foreach($in['answers'] as $label => $ans)
                                <dt>{{ $label }}</dt><dd>{{ $ans }}</dd>
                            @endforeach
                        </dl>
                    @else
                        <p class="text-muted mb-0">Interest inventory not completed yet.</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <p class="small text-muted"><i class="fas fa-info-circle mr-1"></i>{{ $tcuNote }}</p>
</div>
@stop

@section('css')
<style>
    #ai-body table { width: 100%; margin-bottom: 1rem; border-collapse: collapse; font-size: .9rem; }
    #ai-body th, #ai-body td { border: 1px solid #dee2e6; padding: .35rem .5rem; vertical-align: top; }
    #ai-body th { background: #f4f6f9; }
    #ai-body h1, #ai-body h2, #ai-body h3 { font-size: 1.1rem; font-weight: 600; margin-top: 1rem; }
    .dark-mode #ai-body th { background: #3a3f44; }
    .dark-mode #ai-body th, .dark-mode #ai-body td { border-color: #4b5157; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/marked@4.3.0/marked.min.js"></script>
<script>
document.getElementById('ask-ai').addEventListener('click', function () {
    var btn = this, card = document.getElementById('ai-card'), body = document.getElementById('ai-body');
    card.style.display = '';
    body.innerHTML = '<div class="text-muted"><span class="spinner-border spinner-border-sm mr-2"></span>Preparing advice from {{ $s->first_name }}\'s results…</div>';
    btn.disabled = true;
    fetch('{{ route('career-guidance.advice', $s) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
    })
    .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(function (d) { body.innerHTML = marked.parse(d.advice || 'No advice returned.'); })
    .catch(function (e) { body.innerHTML = '<div class="text-danger">Could not get AI advice (' + e.message + '). Try again in a moment.</div>'; })
    .finally(function () { btn.disabled = false; card.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
});
</script>
@stop
