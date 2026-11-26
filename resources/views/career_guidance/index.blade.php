@extends('adminlte::page')

@section('title', 'Career Guidance')

@section('content_header')
    <h1 class="m-0"><i class="fas fa-compass mr-2"></i>Career Guidance</h1>
    <small class="text-muted">Predicted A-level combinations, careers and typical university courses from each student's results</small>
@stop

@section('content')
<div class="container-fluid">

    <div class="card card-outline card-primary">
        <div class="card-body pb-2">
            <form method="GET" class="form-row align-items-end">
                <div class="form-group col-md-3">
                    <label for="class_id">Class</label>
                    <select name="class_id" id="class_id" class="form-control" onchange="this.form.submit()">
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $class && $class->id === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="combo">Suited to combination</label>
                    <select name="combo" id="combo" class="form-control">
                        <option value="">All combinations</option>
                        @foreach(array_keys($combinations) as $code)
                            <option value="{{ $code }}" {{ $combo === $code ? 'selected' : '' }}>{{ $code }} — {{ implode(', ', $combinations[$code]['o_level']) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All students</option>
                        <option value="ready" {{ $status === 'ready' ? 'selected' : '' }}>Ready for their top combination</option>
                        <option value="support" {{ $status === 'support' ? 'selected' : '' }}>Need support to reach a credit</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <button class="btn btn-primary"><i class="fas fa-filter mr-1"></i>Filter</button>
                    <a href="{{ route('career-guidance.index', ['class_id' => $class?->id]) }}" class="btn btn-default">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if($class)
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Strongest area — {{ $class->name }}</h3></div>
                <div class="card-body">
                    @forelse($byArea as $area => $n)
                        <div class="d-flex justify-content-between small"><span>{{ $area }}</span><span>{{ $n }} {{ Str::plural('student', $n) }}</span></div>
                        <div class="progress progress-sm mb-2"><div class="progress-bar bg-primary" style="width: {{ $total ? round($n / $total * 100) : 0 }}%"></div></div>
                    @empty
                        <p class="text-muted mb-0">No marks recorded for this class yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Top predicted combination</h3></div>
                <div class="card-body">
                    @forelse($byCombo as $code => $n)
                        <div class="d-flex justify-content-between small"><span><strong>{{ $code }}</strong> — {{ $combinations[$code]['careers'] ?? '' }}</span><span class="text-nowrap ml-2">{{ $n }}</span></div>
                        <div class="progress progress-sm mb-2"><div class="progress-bar bg-success" style="width: {{ $total ? round($n / $total * 100) : 0 }}%"></div></div>
                    @empty
                        <p class="text-muted mb-0">No predictions yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Students ({{ $rows->count() }} of {{ $total }})</h3>
            <div class="card-tools small text-muted pt-1">
                <span class="badge badge-success">PCM 62.1</span> ready (credit in all three)
                <span class="badge badge-warning ml-2">PCB 48.0</span> needs work
                <span class="ml-2">· Credit (C) = {{ $credit }}</span>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th><th>Student</th><th>Adm. No</th><th class="text-right">Average</th>
                        <th>Strongest area</th><th>Predicted combinations</th><th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows->values() as $i => $r)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><a href="{{ route('career-guidance.show', $r['student']) }}">{{ $r['student']->first_name }} {{ $r['student']->last_name }}</a></td>
                        <td>{{ $r['student']->admission_no }}</td>
                        <td class="text-right">{{ $r['average'] ?? '—' }}</td>
                        <td>{{ $r['strongest'] ?? '—' }}</td>
                        <td>
                            @if($r['subjects'])
                                @foreach($r['top'] as $f)
                                    @php
                                        $tip = $f['missing'] ? 'No record for ' . implode(', ', $f['missing'])
                                            : ($f['gaps'] ? collect($f['gaps'])->map(fn ($g, $s) => "$s +$g")->implode(', ') . ' for a credit' : 'Credit in ' . implode(', ', $f['needs']));
                                    @endphp
                                    <span class="badge badge-{{ $f['ready'] ? 'success' : ($f['missing'] ? 'secondary' : 'warning') }}" title="{{ $tip }}">{{ $f['code'] }} {{ $f['score'] }}</span>
                                @endforeach
                            @else
                                <span class="text-muted small">No marks yet</span>
                            @endif
                        </td>
                        <td class="text-right"><a href="{{ route('career-guidance.show', $r['student']) }}" class="btn btn-xs btn-outline-primary">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No students match.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer small text-muted">
            <i class="fas fa-info-circle mr-1"></i>Predictions use the average of all recorded exams in the O-level subjects each combination builds on. {{ config('career.tcu_note') }}
        </div>
    </div>
    @endif
</div>
@stop
