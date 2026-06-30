@extends('adminlte::page')

@section('title', $timetable->title)

@section('content_header')
<div class="d-flex align-items-center justify-content-between flex-wrap">
    <div class="d-flex align-items-center">
        <a href="{{ route('timetables.index') }}" class="btn btn-secondary btn-sm mr-3">
            <i class="fas fa-arrow-left mr-1"></i>Back
        </a>
        <div>
            <h1 class="mb-0">
                <i class="fas fa-calendar-week text-primary mr-2"></i>{{ $timetable->title }}
            </h1>
            <div class="mt-1">
                {!! $timetable->statusBadge() !!}
                @if($timetable->type === 'class')
                    <span class="badge badge-info ml-1"><i class="fas fa-chalkboard mr-1"></i>Class Routine</span>
                @else
                    <span class="badge badge-warning ml-1"><i class="fas fa-file-alt mr-1"></i>Exam Timetable</span>
                @endif
                <small class="text-muted ml-2">{{ $timetable->session?->name }}</small>
            </div>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-1 mt-1 mt-md-0">
        @if(auth()->user()->hasAnyRole(['Admin', 'Academic']))
            @if($timetable->status !== 'published')
            <a href="{{ route('timetables.edit', $timetable) }}" class="btn btn-sm btn-warning">
                <i class="fas fa-edit mr-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('timetables.regenerate', $timetable) }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-secondary" onclick="return confirm('Regenerate will replace all current entries. Proceed?')">
                    <i class="fas fa-redo mr-1"></i>Regenerate
                </button>
            </form>
            @endif

            @if($canSubmit)
            <form method="POST" action="{{ route('timetables.submit', $timetable) }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-warning">
                    <i class="fas fa-paper-plane mr-1"></i>Submit for Review
                </button>
            </form>
            @endif

            @if($canPublish)
            <form method="POST" action="{{ route('timetables.publish', $timetable) }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-success" onclick="return confirm('Publish this timetable? It will become visible to all users.')">
                    <i class="fas fa-check-circle mr-1"></i>Publish
                </button>
            </form>
            @endif

            @if($timetable->status === 'published')
            <form method="POST" action="{{ route('timetables.unpublish', $timetable) }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Unpublish? It will be moved back to draft.')">
                    <i class="fas fa-eye-slash mr-1"></i>Unpublish
                </button>
            </form>
            @endif

            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print mr-1"></i>Print
            </button>
        @endif
    </div>
</div>
@stop

@section('content')

@foreach(['success','warning','error'] as $type)
@if(session($type))
<div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {!! session($type) !!}
</div>
@endif
@endforeach

{{-- Collision warnings --}}
@if(!empty($collisions))
<div class="alert alert-danger">
    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle mr-2"></i>Collisions Detected ({{ count($collisions) }})</h6>
    <ul class="mb-0 pl-3">
        @foreach($collisions as $c)
        <li>{{ $c }}</li>
        @endforeach
    </ul>
    <small class="d-block mt-1">Regenerate the timetable or resolve conflicts before submitting for review.</small>
</div>
@endif

{{-- ── Capacity Analysis Panel (Admin / Academic only, class timetables) ── --}}
@if(!empty($capacityAnalysis['over_capacity']) || !empty($capacityAnalysis['teacher_overload']))
<div class="card card-outline card-warning mb-3" id="capacity-panel">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
            Capacity Analysis
            <span class="badge badge-warning ml-2">{{ count($capacityAnalysis['over_capacity']) }} class(es) over capacity</span>
        </h3>
        <div class="card-tools">
            <small class="text-muted mr-2">
                {{ $capacityAnalysis['teaching_slots'] }} teaching slots &times; {{ $capacityAnalysis['days'] }} days = {{ $capacityAnalysis['available'] }} slots/class/week
            </small>
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">

        @if(!empty($capacityAnalysis['over_capacity']))
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Class</th>
                        <th class="text-center">Subjects</th>
                        <th class="text-center">Needed</th>
                        <th class="text-center">Available</th>
                        <th class="text-center text-danger">Deficit</th>
                        <th>Fix A — Reduce periods/week</th>
                        <th>Fix B — Add teaching periods/day</th>
                        <th>Fix C — Suggested subject cuts</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($capacityAnalysis['over_capacity'] as $cls)
                    <tr>
                        <td><strong>{{ $cls['class_name'] }}</strong></td>
                        <td class="text-center">{{ $cls['num_subjects'] }}</td>
                        <td class="text-center text-danger font-weight-bold">{{ $cls['needed'] }}</td>
                        <td class="text-center">{{ $cls['available'] }}</td>
                        <td class="text-center">
                            <span class="badge badge-danger">-{{ $cls['deficit'] }} ({{ $cls['pct'] }}%)</span>
                        </td>
                        <td>
                            <span class="text-success">
                                <i class="fas fa-sliders-h mr-1"></i>
                                Set default to <strong>&le;{{ $cls['uniform_max'] }}</strong> periods/week
                            </span>
                        </td>
                        <td>
                            <span class="text-info">
                                <i class="fas fa-plus-square mr-1"></i>
                                Add <strong>{{ $cls['extra_periods_day'] }}</strong> teaching period(s)/day in
                                <a href="{{ route('timetables.create') }}">Period Settings</a>
                            </span>
                        </td>
                        <td>
                            @if(!empty($cls['suggested_cuts']))
                                <small class="text-muted">{{ implode(', ', $cls['suggested_cuts']) }}</small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-3 py-2 border-top bg-light">
            <small class="text-muted">
                <i class="fas fa-lightbulb text-warning mr-1"></i>
                <strong>Fix A</strong>: lower "Default periods/week" when creating/regenerating.
                &nbsp;<strong>Fix B</strong>: add more active periods in Period Settings (e.g. add a P10, P11 row).
                &nbsp;<strong>Fix C</strong>: use per-subject override in the timetable form to reduce specific subjects.
                After changes, click <em>Regenerate</em>.
            </small>
        </div>
        @endif

        @if(!empty($capacityAnalysis['teacher_overload']))
        <div class="table-responsive {{ !empty($capacityAnalysis['over_capacity']) ? 'border-top' : '' }}">
            <div class="px-3 pt-2 pb-1"><strong class="text-danger"><i class="fas fa-user-clock mr-1"></i>Teacher Overload</strong></div>
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Teacher</th>
                        <th class="text-center">Classes</th>
                        <th class="text-center">Load (periods)</th>
                        <th class="text-center">Available</th>
                        <th class="text-center text-danger">Overload</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($capacityAnalysis['teacher_overload'] as $t)
                    <tr>
                        <td><strong>{{ $t['teacher_name'] }}</strong></td>
                        <td class="text-center">{{ $t['classes'] }}</td>
                        <td class="text-center text-danger font-weight-bold">{{ $t['load'] }}</td>
                        <td class="text-center">{{ $t['available'] }}</td>
                        <td class="text-center">
                            <span class="badge badge-danger">+{{ $t['overload'] }} ({{ $t['pct'] }}%)</span>
                        </td>
                        <td><small class="text-muted">Reassign some of this teacher's subjects to another teacher.</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>
@endif

<div class="row">
    {{-- Main timetable content --}}
    <div class="col-lg-9">

        @if($timetable->type === 'class')
        {{-- ── CLASS ROUTINE: tab per class ──────────────────────────────── --}}
        @php
            $days = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri'];

            // Compute period times from session_duration + school_start_time stored in settings
            $sessionDuration  = (int)($timetable->settings['session_duration'] ?? 40);
            $schoolStart      = $timetable->settings['school_start_time'] ?? '07:30';
            $timetableDays    = $timetable->settings['days'] ?? [1, 2, 3, 4, 5];

            // Special sessions that apply every configured teaching day push period start times forward.
            $allDaySessions = array_values(array_filter(
                $timetable->settings['special_sessions'] ?? [],
                function ($ss) use ($timetableDays) {
                    $ssDays = array_map('intval', $ss['days'] ?? [1, 2, 3, 4, 5]);
                    return empty(array_diff($timetableDays, $ssDays));
                }
            ));

            // Helper: push $cur past any all-day special session that covers it.
            $pushPast = function (int $cur) use ($allDaySessions): int {
                $changed = true;
                while ($changed) {
                    $changed = false;
                    foreach ($allDaySessions as $ss) {
                        [$sh, $sm] = explode(':', substr($ss['start_time'], 0, 5));
                        [$eh, $em] = explode(':', substr($ss['end_time'],   0, 5));
                        $s = (int)$sh * 60 + (int)$sm;
                        $e = (int)$eh * 60 + (int)$em;
                        if ($s <= $cur && $e > $cur) { $cur = $e; $changed = true; }
                    }
                }
                return $cur;
            };

            // Manual timing: use stored per-period times directly
            $computedTimes = [];
            if (($timetable->settings['timing_mode'] ?? 'auto') === 'manual' && !empty($timetable->settings['period_times'])) {
                $manualPeriodTimes = $timetable->settings['period_times'];
                foreach ($periods as $p) {
                    $mt = $manualPeriodTimes[$p->id] ?? ($manualPeriodTimes[(string)$p->id] ?? null);
                    $computedTimes[$p->id] = $mt
                        ? ['start' => $mt['start'], 'end' => $mt['end']]
                        : ['start' => '—:—',        'end' => '—:—'];
                }
            } else {
                [$sh, $sm] = explode(':', $schoolStart);
                $curMins   = (int)$sh * 60 + (int)$sm;
                foreach ($periods as $p) {
                    if (!$p->is_break) { $curMins = $pushPast($curMins); }
                    if ($p->is_break) {
                        [$bsh, $bsm] = explode(':', substr($p->start_time, 0, 5));
                        [$beh, $bem] = explode(':', substr($p->end_time,   0, 5));
                        $dur = max(((int)$beh * 60 + (int)$bem) - ((int)$bsh * 60 + (int)$bsm), 5);
                    } else {
                        $dur = $sessionDuration;
                    }
                    $computedTimes[$p->id] = [
                        'start' => sprintf('%02d:%02d', intdiv($curMins, 60), $curMins % 60),
                        'end'   => sprintf('%02d:%02d', intdiv($curMins + $dur, 60), ($curMins + $dur) % 60),
                    ];
                    $curMins += $dur;
                }
            }

            // Detect double sessions: period pairs assigned same class+subject+day
            // Grid now stores arrays of entries per cell; use the first entry's subject for detection
            $doublePairs = [];
            foreach ($grid as $clsId => $dayGrid) {
                foreach ($dayGrid as $dayNum => $periodGrid) {
                    $subjectPeriods = [];
                    foreach ($periodGrid as $pId => $cellEntries) {
                        $firstEntry = $cellEntries[0] ?? null;
                        if ($firstEntry) $subjectPeriods[$firstEntry->subject_id][] = $pId;
                    }
                    foreach ($subjectPeriods as $subjId => $pIds) {
                        if (count($pIds) >= 2) {
                            foreach ($pIds as $pId) {
                                $doublePairs["$clsId.$dayNum.$pId"] = true;
                            }
                        }
                    }
                }
            }

            // ── STEP 1: Build lookup of teaching-period time windows ──────────
            // Only teaching (non-break) periods define "blocked" time slots.
            $teachingWindows = [];   // [['s' => mins, 'e' => mins], ...]
            foreach ($periods as $p) {
                if ($p->is_break) continue;
                [$sh, $sm] = explode(':', $computedTimes[$p->id]['start']);
                [$eh, $em] = explode(':', $computedTimes[$p->id]['end']);
                $teachingWindows[] = ['s' => (int)$sh*60+(int)$sm, 'e' => (int)$eh*60+(int)$em];
            }

            // ── STEP 2: Classify each special session ─────────────────────────
            // INLINE  = fits cleanly between periods (no overlap with any teaching period)
            // OVERLAY = overlaps one or more teaching periods → shown in "Daily Programme" below
            $inlineSessions  = [];
            $overlaySessions = [];
            foreach ($specialSessions as $ss) {
                [$bsh, $bsm] = explode(':', substr($ss['start_time'], 0, 5));
                [$beh, $bem] = explode(':', substr($ss['end_time'],   0, 5));
                $bStart = (int)$bsh*60+(int)$bsm;
                $bEnd   = (int)$beh*60+(int)$bem;
                $conflicts = false;
                foreach ($teachingWindows as $tw) {
                    // Overlap: band_start < period_end AND band_end > period_start
                    if ($bStart < $tw['e'] && $bEnd > $tw['s']) {
                        $conflicts = true;
                        break;
                    }
                }
                if ($conflicts) $overlaySessions[] = $ss;
                else            $inlineSessions[]  = $ss;
            }

            // ── STEP 3: Build per-day-column band rows from inline sessions ───
            // Group sessions that share identical time slots into one row (e.g. same
            // break that has different names on different days). Sessions with different
            // times get separate rows. Day-specific override: later session wins per day.
            $ssBands = [];
            foreach ($inlineSessions as $ss) {
                $ssDays = array_map('intval', $ss['days'] ?? [1,2,3,4,5]);
                $placed = false;
                foreach ($ssBands as &$band) {
                    // Only group sessions whose time slots are IDENTICAL (not just overlapping).
                    // This prevents unrelated activities from being merged into one row.
                    $sameSlot = $ss['start_time'] === $band['start'] && $ss['end_time'] === $band['end'];
                    if ($sameSlot) {
                        foreach ($ssDays as $d) { $band['day_map'][$d] = $ss; }
                        $placed = true;
                        break;
                    }
                    // Allow merging sessions that slightly differ in time only if they are
                    // the SAME type (e.g. Short Break 08:40-08:45 same across all days).
                    $closeOverlap = $ss['start_time'] < $band['end'] && $ss['end_time'] > $band['start']
                                 && ($ss['type'] ?? '') === ($band['rep_type'] ?? '');
                    if ($closeOverlap) {
                        foreach ($ssDays as $d) { $band['day_map'][$d] = $ss; }
                        $band['start'] = min($band['start'], $ss['start_time']);
                        $band['end']   = max($band['end'],   $ss['end_time']);
                        $placed = true;
                        break;
                    }
                }
                unset($band);
                if (!$placed) {
                    $nb = [
                        'start'    => $ss['start_time'],
                        'end'      => $ss['end_time'],
                        'rep_type' => $ss['type'] ?? 'free',
                        'day_map'  => [],
                    ];
                    foreach ($ssDays as $d) { $nb['day_map'][$d] = $ss; }
                    $ssBands[] = $nb;
                }
            }

            // ── STEP 4: Build sorted timeline — periods + inline bands only ───
            $timeline = [];
            foreach ($periods as $p) {
                $timeline[] = ['kind' => 'period', 'sort' => $computedTimes[$p->id]['start'] ?? '99:99', 'data' => $p];
            }
            foreach ($ssBands as $band) {
                $timeline[] = ['kind' => 'ss_band', 'sort' => $band['start'], 'data' => $band];
            }
            usort($timeline, function ($a, $b) {
                $cmp = strcmp($a['sort'], $b['sort']);
                if ($cmp !== 0) return $cmp;
                // Same start time: bands before periods (e.g. Short Break before Period 3)
                if ($a['kind'] === 'ss_band' && $b['kind'] === 'period') return -1;
                if ($a['kind'] === 'period'  && $b['kind'] === 'ss_band') return  1;
                return 0;
            });

            // ── STEP 5: Group overlay sessions per day for the Daily Programme ─
            // dayOverlays[day_number] = [ ['name'=>..,'time'=>..,'icon'=>..,'bg'=>..], ... ]
            $dayOverlays = [];
            foreach ($overlaySessions as $ss) {
                $ssDays = array_map('intval', $ss['days'] ?? [1,2,3,4,5]);
                foreach ($ssDays as $d) {
                    $dayOverlays[$d][] = $ss;
                }
            }
            // Sort each day's overlay sessions by start time
            foreach ($dayOverlays as &$dList) {
                usort($dList, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));
            }
            unset($dList);

            // Icon map for special session types
            $ssIcons = [
                'assembly'   => '🏁',
                'prayer'     => '🕌',
                'break'      => '☕',
                'self_study' => '📚',
                'sports'     => '⚽',
                'sdp'        => '📖',
                'debate'     => '🗣️',
                'free'       => '🌙',
            ];
            $ssBg = [
                'assembly'   => 'table-secondary',
                'prayer'     => 'ss-prayer',
                'break'      => 'table-warning',
                'self_study' => 'ss-study',
                'sports'     => 'ss-sports',
                'sdp'        => 'ss-sdp',
                'debate'     => 'ss-debate',
                'free'       => 'table-light',
            ];
        @endphp

        <ul class="nav nav-tabs" id="classTabs">
            @foreach($classes as $idx => $cls)
            <li class="nav-item">
                <a class="nav-link {{ $idx === 0 ? 'active' : '' }}"
                   data-toggle="tab" href="#class_{{ $cls->id }}">
                    {{ $cls->name }}
                </a>
            </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($classes as $idx => $cls)
            <div class="tab-pane {{ $idx === 0 ? 'active' : '' }} pt-2" id="class_{{ $cls->id }}">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">{{ $cls->name }} — Weekly Schedule</h3>
                        @if(!empty($specialSessions))
                        <div class="card-tools">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Shaded rows = fixed sessions
                            </small>
                        </div>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 timetable-grid">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width:130px">Period / Session</th>
                                        @foreach($days as $d => $label)
                                        <th class="text-center">{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($timeline as $item)

                                        @if($item['kind'] === 'period')
                                        @php
                                            $period  = $item['data'];
                                            $ctStart = $computedTimes[$period->id]['start'] ?? substr($period->start_time,0,5);
                                            $ctEnd   = $computedTimes[$period->id]['end']   ?? substr($period->end_time,0,5);
                                        @endphp
                                        <tr class="{{ $period->is_break ? 'table-secondary' : '' }}">
                                            <td class="font-weight-bold small">
                                                <div>{{ $period->name }}</div>
                                                <div class="text-muted" style="font-size:0.7rem">
                                                    {{ $ctStart }}–{{ $ctEnd }}
                                                    @if(!$period->is_break)
                                                    <span class="text-info ml-1">({{ $sessionDuration }}m)</span>
                                                    @endif
                                                </div>
                                            </td>
                                            @foreach($days as $d => $dayLabel)
                                            @php
                                                $cellEntries = $grid[$cls->id][$d][$period->id] ?? [];
                                                $isDouble    = !empty($cellEntries) && !empty($doublePairs[$cls->id . '.' . $d . '.' . $period->id]);
                                                $isCombo     = count($cellEntries) > 1;
                                            @endphp
                                            <td class="text-center align-middle p-1 {{ $period->is_break ? '' : 'slot-cell' }}">
                                                @if($period->is_break)
                                                    <span class="text-muted small">— BREAK —</span>
                                                @elseif($isCombo)
                                                    {{-- Combination slot: multiple subjects at the same time --}}
                                                    @foreach($cellEntries as $ce)
                                                    @php $teacherName = $ce->teacher ? ($ce->teacher->name ?: trim($ce->teacher->first_name.' '.$ce->teacher->last_name)) : null; @endphp
                                                    <div class="subject-pill bg-warning text-dark rounded p-1 mb-1">
                                                        @if($loop->first)<div style="font-size:0.58rem;font-weight:700;letter-spacing:0.5px">⬡ COMBO</div>@endif
                                                        <div class="font-weight-bold" style="font-size:0.78rem">{{ $ce->subject?->name ?? '—' }}</div>
                                                        @if($teacherName)<div style="font-size:0.65rem;opacity:0.85">{{ $teacherName }}</div>@endif
                                                    </div>
                                                    @if(!$loop->last)
                                                    <div class="text-muted" style="font-size:0.6rem;line-height:1">— or —</div>
                                                    @endif
                                                    @endforeach
                                                @elseif(!empty($cellEntries))
                                                    @php $entry = $cellEntries[0]; @endphp
                                                    <div class="subject-pill {{ $isDouble ? 'bg-success' : 'bg-primary' }} text-white rounded p-1">
                                                        @if($isDouble)
                                                        <div style="font-size:0.6rem;opacity:0.9;letter-spacing:0.5px">◆ DOUBLE</div>
                                                        @endif
                                                        <div class="font-weight-bold" style="font-size:0.78rem">
                                                            {{ $entry->subject?->name ?? '—' }}
                                                        </div>
                                                        @if($entry->teacher)
                                                        <div style="font-size:0.65rem;opacity:0.85">
                                                            {{ $entry->teacher->name ?: trim($entry->teacher->first_name . ' ' . $entry->teacher->last_name) }}
                                                        </div>
                                                        @endif
                                                        @if($entry->room)
                                                        <div style="font-size:0.65rem;opacity:0.75">Rm: {{ $entry->room }}</div>
                                                        @endif
                                                        <div style="font-size:0.6rem;opacity:0.75">
                                                            {{ $entry->start_time ?? $ctStart }}–{{ $entry->end_time ?? $ctEnd }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted" style="font-size:0.7rem">—</span>
                                                @endif
                                            </td>
                                            @endforeach
                                        </tr>

                                        @else
                                        {{-- SPECIAL SESSION BAND (one merged row per time-slot group) --}}
                                        @php
                                            $band        = $item['data'];
                                            $dayMap      = $band['day_map'];
                                            $allSessions = array_values($dayMap);
                                            $uniqueNames = array_unique(array_column($allSessions, 'name'));
                                            $isSame      = count($uniqueNames) === 1;
                                            // Representative session for the row header label
                                            $repSs   = $allSessions[0] ?? null;
                                            $repType = $repSs ? ($repSs['type'] ?? 'free') : 'free';
                                            $repIcon = $ssIcons[$repType] ?? '📌';
                                            $label   = $isSame ? $repSs['name'] : implode(' / ', $uniqueNames);
                                        @endphp
                                        <tr class="special-session-row">
                                            <td class="font-weight-bold small align-middle">
                                                <span style="font-size:1rem">{{ $repIcon }}</span>
                                                {{ $label }}
                                                <div class="text-muted" style="font-size:0.7rem">
                                                    {{ $band['start'] }}–{{ $band['end'] }}
                                                </div>
                                            </td>
                                            @foreach($days as $d => $dayLabel)
                                            @if(isset($dayMap[$d]))
                                                @php
                                                    $ss     = $dayMap[$d];
                                                    $ssT    = $ss['type'] ?? 'free';
                                                    $cellBg = $ssBg[$ssT] ?? 'table-light';
                                                    $icon   = $ssIcons[$ssT] ?? '📌';
                                                @endphp
                                                <td class="text-center align-middle {{ $cellBg }} small font-italic">
                                                    {{ $icon }} {{ strtoupper($ss['name']) }}
                                                    @if(!$isSame)
                                                    <div style="font-size:0.65rem;opacity:0.8">{{ $ss['start_time'] }}–{{ $ss['end_time'] }}</div>
                                                    @endif
                                                </td>
                                            @else
                                                <td></td>
                                            @endif
                                            @endforeach
                                        </tr>

                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ── DAILY PROGRAMME (overlay sessions shown per-day below the grid) ── --}}
        @if(!empty($overlaySessions))
        @php
            $dpDayNames = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];
        @endphp
        <div class="card card-outline card-warning mt-2">
            <div class="card-header py-2">
                <h3 class="card-title mb-0">
                    <i class="fas fa-clock mr-1 text-warning"></i>
                    Daily Programme <small class="text-muted ml-1">(activities running alongside / after teaching periods)</small>
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:200px">Session</th>
                            @foreach($days as $d => $label)
                            <th class="text-center">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Collect all unique time-slots from overlay sessions
                            $overlaySlots = [];
                            foreach($overlaySessions as $ss) {
                                $slot = $ss['start_time'] . '|' . $ss['end_time'];
                                if(!isset($overlaySlots[$slot])) $overlaySlots[$slot] = [];
                                foreach(array_map('intval', $ss['days'] ?? [1,2,3,4,5]) as $d) {
                                    $overlaySlots[$slot][$d] = $ss;
                                }
                            }
                            // Sort slots by start time
                            ksort($overlaySlots);
                        @endphp
                        @foreach($overlaySlots as $slotKey => $slotDayMap)
                        @php
                            [$slotStart, $slotEnd] = explode('|', $slotKey);
                            $anySession = array_values($slotDayMap)[0];
                            $repType = $anySession['type'] ?? 'free';
                            $repIcon = $ssIcons[$repType] ?? '📌';
                        @endphp
                        <tr>
                            <td class="font-weight-bold small align-middle">
                                <span style="font-size:.9rem">{{ $repIcon }}</span>
                                {{ $anySession['name'] }}
                                <div class="text-muted" style="font-size:.68rem">{{ $slotStart }}–{{ $slotEnd }}</div>
                            </td>
                            @foreach($days as $d => $dayLabel)
                            @php
                                $ss    = $slotDayMap[$d] ?? null;
                                $ssT   = $ss ? ($ss['type'] ?? 'free') : null;
                                $cellBg = $ss ? ($ssBg[$ssT] ?? 'table-light') : '';
                                $icon  = $ss ? ($ssIcons[$ssT] ?? '📌') : '';
                            @endphp
                            <td class="text-center small align-middle {{ $cellBg }}">
                                @if($ss)
                                    <span class="font-italic font-weight-bold">
                                        {{ $icon }} {{ strtoupper($ss['name']) }}
                                    </span>
                                    @if($ss['start_time'] !== $slotStart || $ss['end_time'] !== $slotEnd)
                                    <div style="font-size:.65rem;opacity:.8">{{ $ss['start_time'] }}–{{ $ss['end_time'] }}</div>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- ── ALL SESSIONS LEGEND ─────────────────────────────────────────── --}}
        @if(!empty($specialSessions))
        <div class="card card-outline card-secondary mt-2">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap align-items-center" style="gap:6px">
                    <small class="font-weight-bold text-muted mr-1">
                        <i class="fas fa-info-circle mr-1"></i>All Fixed Sessions:
                    </small>
                    @foreach($specialSessions as $ss)
                    @php
                        $ssType   = $ss['type'] ?? 'free';
                        $isInline = in_array($ss, $inlineSessions, true);
                        $dayShort = array_map(fn($d) => ['Mon','Tue','Wed','Thu','Fri'][$d-1] ?? '', $ss['days'] ?? []);
                    @endphp
                    <span class="badge badge-{{ $ss['color'] ?? 'secondary' }} py-1 px-2"
                          title="{{ $isInline ? 'Shown inline in grid' : 'Shown in Daily Programme below' }}">
                        {{ $ssIcons[$ssType] ?? '📌' }} {{ $ss['name'] }}
                        <small class="ml-1 opacity-75">{{ $ss['start_time'] }}–{{ $ss['end_time'] }}</small>
                        <small class="ml-1">({{ implode(',', $dayShort) }})</small>
                        @if(!$isInline)
                            <i class="fas fa-arrow-down ml-1" title="See Daily Programme"></i>
                        @endif
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @else
        {{-- ── EXAM TIMETABLE: grouped by date, matching PDF format ───── --}}
        @if(empty($examGrid))
            <div class="alert alert-warning">
                <i class="fas fa-info-circle mr-1"></i>No exam entries generated. Check your exam dates and class/subject configuration.
            </div>
        @else
            @foreach($examGrid as $date => $timeSlots)
            <div class="card card-outline card-warning mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-day mr-1"></i>
                        {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 exam-timetable">
                            <thead class="thead-dark">
                                <tr>
                                    <th rowspan="2" class="align-middle text-center" style="width:80px">Session</th>
                                    <th colspan="4" class="text-center border-right">First Session</th>
                                    <th colspan="4" class="text-center">Second Session</th>
                                </tr>
                                <tr class="thead-light">
                                    <th>Class</th>
                                    <th>Venue</th>
                                    <th>Subject</th>
                                    <th class="border-right">Invigilators</th>
                                    <th>Class</th>
                                    <th>Venue</th>
                                    <th>Subject</th>
                                    <th>Invigilators</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $slotKeys  = array_keys($timeSlots);
                                    $slot1Key  = $slotKeys[0] ?? null;
                                    $slot2Key  = $slotKeys[1] ?? null;
                                    $allClassIds = array_unique(array_merge(
                                        $slot1Key ? array_keys($timeSlots[$slot1Key] ?? []) : [],
                                        $slot2Key ? array_keys($timeSlots[$slot2Key] ?? []) : []
                                    ));
                                    // Build time labels
                                    $slot1Label = $slot1Key ? str_replace('-', ' – ', $slot1Key) : '—';
                                    $slot2Label = $slot2Key ? str_replace('-', ' – ', $slot2Key) : '—';
                                @endphp
                                {{-- One row per class --}}
                                @foreach($allClassIds as $clsIdx => $clsId)
                                @php
                                    $cls      = $classes->firstWhere('id', $clsId);
                                    $e1       = ($slot1Key && isset($timeSlots[$slot1Key][$clsId]))
                                                    ? $timeSlots[$slot1Key][$clsId][0] : null;
                                    $e2       = ($slot2Key && isset($timeSlots[$slot2Key][$clsId]))
                                                    ? $timeSlots[$slot2Key][$clsId][0] : null;
                                @endphp
                                <tr>
                                    @if($clsIdx === 0)
                                    <td rowspan="{{ count($allClassIds) }}" class="align-middle text-center small font-weight-bold bg-light">
                                        {{ $slot1Label }}<br>
                                        <span class="text-muted">&</span><br>
                                        {{ $slot2Label }}
                                    </td>
                                    @endif

                                    {{-- First session columns --}}
                                    <td class="font-weight-bold small">{{ $cls?->name ?? "Class #$clsId" }}</td>
                                    <td class="small text-muted">{{ $e1?->room ?? 'G' }}</td>
                                    <td class="small">
                                        @if($e1)
                                        <strong>{{ $e1->subject?->name ?? '—' }}</strong>
                                        @else <span class="text-muted">—</span> @endif
                                    </td>
                                    <td class="small border-right">
                                        @if($e1 && $e1->invigilator_ids)
                                            @foreach($e1->invigilator_ids as $invId)
                                                @php $inv = $invigilators[$invId] ?? null; @endphp
                                                @if($inv)
                                                <div>{{ $inv->name ?: $inv->full_name }}</div>
                                                @endif
                                            @endforeach
                                        @else <span class="text-muted">—</span> @endif
                                    </td>

                                    {{-- Second session columns --}}
                                    <td class="font-weight-bold small">{{ $cls?->name ?? "Class #$clsId" }}</td>
                                    <td class="small text-muted">{{ $e2?->room ?? 'G' }}</td>
                                    <td class="small">
                                        @if($e2)
                                        <strong>{{ $e2->subject?->name ?? '—' }}</strong>
                                        @else <span class="text-muted">—</span> @endif
                                    </td>
                                    <td class="small">
                                        @if($e2 && $e2->invigilator_ids)
                                            @foreach($e2->invigilator_ids as $invId)
                                                @php $inv = $invigilators[$invId] ?? null; @endphp
                                                @if($inv)
                                                <div>{{ $inv->name ?: $inv->full_name }}</div>
                                                @endif
                                            @endforeach
                                        @else <span class="text-muted">—</span> @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Staff key (like the PDFs show at the bottom) --}}
            @if($invigilators->isNotEmpty())
            <div class="card card-outline card-secondary mt-2">
                <div class="card-header py-2">
                    <h3 class="card-title small"><i class="fas fa-users mr-1"></i>Invigilator Key</h3>
                </div>
                <div class="card-body py-2">
                    <div class="row">
                        @foreach($invigilators->chunk(4) as $chunk)
                        @foreach($chunk as $inv)
                        <div class="col-md-3 col-6 small mb-1">
                            <strong>{{ $inv->name ?: $inv->full_name }}</strong>
                        </div>
                        @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        @endif
        @endif

    </div>

    {{-- Right sidebar: info + review --}}
    <div class="col-lg-3">

        {{-- Info card --}}
        <div class="card card-outline card-secondary mb-3">
            <div class="card-header"><h3 class="card-title">Details</h3></div>
            <div class="card-body p-2">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted small">Session</th>
                        <td class="small">{{ $timetable->session?->name ?? '—' }}</td></tr>
                    <tr><th class="text-muted small">Created by</th>
                        <td class="small">{{ $timetable->creator?->name ?? ($timetable->creator ? $timetable->creator->first_name . ' ' . $timetable->creator->last_name : '—') }}</td></tr>
                    <tr><th class="text-muted small">Created</th>
                        <td class="small">{{ $timetable->created_at->format('d M Y H:i') }}</td></tr>
                    @if($timetable->published_at)
                    <tr><th class="text-muted small">Published</th>
                        <td class="small">{{ $timetable->published_at->format('d M Y') }}</td></tr>
                    @endif
                    @if($timetable->type === 'class')
                    <tr><th class="text-muted small">Session</th>
                        <td class="small">{{ $timetable->settings['session_duration'] ?? 40 }} min</td></tr>
                    <tr><th class="text-muted small">Start</th>
                        <td class="small">{{ $timetable->settings['school_start_time'] ?? '07:30' }}</td></tr>
                    @endif
                    <tr><th class="text-muted small">Entries</th>
                        <td class="small">{{ count($entries) }}</td></tr>
                    <tr><th class="text-muted small">Collisions</th>
                        <td class="small">
                            @if(empty($collisions))
                                <span class="text-success"><i class="fas fa-check-circle mr-1"></i>None</span>
                            @else
                                <span class="text-danger"><i class="fas fa-times-circle mr-1"></i>{{ count($collisions) }}</span>
                            @endif
                        </td></tr>
                </table>
                @if($timetable->notes)
                <hr class="my-1">
                <small class="text-muted">{{ $timetable->notes }}</small>
                @endif
            </div>
        </div>

        {{-- Review/approval panel --}}
        @if($timetable->status === 'pending_review' || $timetable->reviews->count())
        <div class="card card-outline card-warning mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-tasks mr-1"></i>Review Status</h3></div>
            <div class="card-body p-2">
                {{-- Existing reviews --}}
                @foreach($timetable->reviews as $rev)
                <div class="d-flex align-items-center mb-2">
                    <div class="mr-2">
                        @if($rev->action === 'approved')
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        @else
                            <i class="fas fa-times-circle text-danger fa-lg"></i>
                        @endif
                    </div>
                    <div>
                        <div class="font-weight-bold small">
                            {{ $rev->reviewer?->name ?? ($rev->reviewer ? $rev->reviewer->first_name . ' ' . $rev->reviewer->last_name : '—') }}
                            <span class="badge badge-secondary ml-1">{{ strtoupper($rev->reviewer_role) }}</span>
                        </div>
                        <div class="text-muted" style="font-size:0.7rem">
                            {{ ucfirst($rev->action) }} · {{ $rev->reviewed_at?->format('d M Y H:i') }}
                        </div>
                        @if($rev->notes)
                        <div class="text-muted small mt-1">"{{ $rev->notes }}"</div>
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- HOD approval counter --}}
                <div class="text-center mt-2 mb-2">
                    <span id="hodApprovalCount" class="badge badge-{{ $timetable->hodApprovalsCount() > 0 ? 'success' : 'secondary' }} p-2">
                        <i class="fas fa-user-check mr-1"></i>
                        {{ $timetable->hodApprovalsCount() }} HOD Approval(s)
                    </span>
                </div>

                {{-- Review form for eligible users --}}
                @if($canReview)
                <hr>
                <div id="reviewForm">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Your Decision</label>
                        <div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="actApprove" name="reviewAction" class="custom-control-input" value="approved" checked>
                                <label class="custom-control-label text-success" for="actApprove">Approve</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="actReject" name="reviewAction" class="custom-control-input" value="rejected">
                                <label class="custom-control-label text-danger" for="actReject">Reject</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <textarea id="reviewNotes" class="form-control form-control-sm" rows="2" placeholder="Notes (optional)"></textarea>
                    </div>
                    <button class="btn btn-sm btn-primary w-100" id="submitReviewBtn">
                        <i class="fas fa-paper-plane mr-1"></i>Submit Review
                    </button>
                    <div id="reviewMsg" class="mt-2" style="display:none"></div>
                </div>
                @elseif($myReview)
                <div class="alert alert-success py-2 mb-0 mt-2 small">
                    <i class="fas fa-check mr-1"></i>You have already reviewed this timetable.
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Review AJAX --}}
@if($canReview)
<script>
document.getElementById('submitReviewBtn')?.addEventListener('click', function () {
    const action = document.querySelector('input[name="reviewAction"]:checked')?.value;
    const notes  = document.getElementById('reviewNotes').value;
    const btn    = this;
    const msg    = document.getElementById('reviewMsg');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Submitting…';

    fetch('{{ route("timetables.review", $timetable) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ action, notes }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            msg.style.display = 'block';
            msg.className = 'alert alert-success small py-2';
            msg.innerHTML = '<i class="fas fa-check mr-1"></i>Review submitted. Page will reload…';
            document.getElementById('hodApprovalCount').textContent = data.hod_approvals + ' HOD Approval(s)';
            setTimeout(() => location.reload(), 1500);
        } else {
            msg.style.display = 'block';
            msg.className = 'alert alert-danger small py-2';
            msg.textContent = data.error || 'An error occurred.';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i>Submit Review';
        }
    })
    .catch(() => {
        msg.style.display = 'block';
        msg.className = 'alert alert-danger small py-2';
        msg.textContent = 'Network error. Please try again.';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i>Submit Review';
    });
});
</script>
@endif

@stop

@section('css')
<style>
.timetable-grid th, .timetable-grid td { vertical-align: middle; }
.subject-pill { line-height: 1.2; min-width: 70px; display: inline-block; }
.slot-cell { min-width: 90px; }
.gap-1 { gap: 0.25rem; }
.exam-timetable th, .exam-timetable td { vertical-align: middle; font-size: 0.82rem; }
.exam-timetable thead tr:first-child th { text-align: center; }

/* Special session row colours */
.ss-prayer  { background-color: #d1ecf1 !important; }
.ss-study   { background-color: #d4edda !important; }
.ss-sports  { background-color: #f8d7da !important; }
.ss-sdp     { background-color: #e2d9f3 !important; }
.ss-debate  { background-color: #cce5ff !important; }
.special-session-row td { border-top: 2px dashed #adb5bd !important; border-bottom: 2px dashed #adb5bd !important; }

@media print {
    .content-header, .main-sidebar, .main-footer,
    .btn, form[method="POST"], .nav-tabs { display: none !important; }
    .tab-content .tab-pane { display: block !important; opacity: 1 !important; }
    .card { border: 1px solid #dee2e6 !important; }
}
</style>
@stop
