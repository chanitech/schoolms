@extends('adminlte::page')

@section('title', 'My Session Log')

@php
$statusCfg = [
    'attended' => ['label'=>'Attended', 'btn'=>'btn-success',   'badge'=>'success',   'icon'=>'fa-check-circle'],
    'late'     => ['label'=>'Late',     'btn'=>'btn-warning',   'badge'=>'warning',   'icon'=>'fa-clock'],
    'absent'   => ['label'=>'Absent',   'btn'=>'btn-danger',    'badge'=>'danger',    'icon'=>'fa-times-circle'],
    'other'    => ['label'=>'Other',    'btn'=>'btn-secondary', 'badge'=>'secondary', 'icon'=>'fa-question-circle'],
];
@endphp

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:.5rem">
        <div>
            <h1 class="m-0 text-dark">
                <i class="fas fa-chalkboard-teacher mr-2 text-primary"></i>
                @if($isHR && $viewingTeacher->id !== auth()->id())
                    {{ $viewingTeacher->name }}'s Sessions
                @else
                    My Teaching Sessions
                @endif
            </h1>
            <small class="text-muted">Week of {{ $weekStart->format('d M') }} – {{ $weekEnd->format('d M Y') }}</small>
        </div>
        <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
            @if($isHR && $teachers->count())
                <form method="GET" action="{{ route('timetables.my-sessions') }}" class="d-inline-flex align-items-center" style="gap:.4rem">
                    <input type="hidden" name="week" value="{{ $weekOffset }}">
                    <select name="teacher_id" class="form-control form-control-sm" onchange="this.form.submit()" style="min-width:180px">
                        <option value="">— All / My Sessions —</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" @selected($teacherId == $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            <a href="{{ route('timetables.my-sessions', array_filter(['week' => $weekOffset - 1, 'teacher_id' => $isHR ? $teacherId : null])) }}"
               class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-left"></i> Prev</a>
            <a href="{{ route('timetables.my-sessions', array_filter(['teacher_id' => $isHR ? $teacherId : null])) }}"
               class="btn btn-sm btn-outline-primary">This Week</a>
            <a href="{{ route('timetables.my-sessions', array_filter(['week' => $weekOffset + 1, 'teacher_id' => $isHR ? $teacherId : null])) }}"
               class="btn btn-sm btn-outline-secondary">Next <i class="fas fa-chevron-right"></i></a>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            <strong>Could not save:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @php
        $presentCount = 0; $lateCount = 0; $absentCount = 0; $pendingCount = 0; $pastTotal = 0;
        $topicsCoveredThisWeek = 0; $subtopicsCoveredThisWeek = 0;
        $dayNames = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];

        foreach($weekDates as $dow => $date) {
            if ($date->isFuture()) continue;
            foreach(($dayEntries[$dow] ?? []) as $entry) {
                $pastTotal++;
                $log = $logsMap[$entry->id . '_' . $date->toDateString()] ?? null;
                if ($log?->status === 'attended')  $presentCount++;
                elseif ($log?->status === 'late')  $lateCount++;
                elseif ($log?->status === 'absent') $absentCount++;
                else $pendingCount++;
                if ($log?->lesson_topic_id) $topicsCoveredThisWeek++;
                if ($log) $subtopicsCoveredThisWeek += $log->coveredSubtopics->count();
            }
        }
        $presentRate = $pastTotal > 0 ? round(($presentCount + $lateCount) / $pastTotal * 100) : null;
    @endphp

    {{-- ── Stats Row ── --}}
    <div class="row mb-3">
        <div class="col-6 col-sm-3">
            <div class="info-box shadow-sm mb-3">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-calendar-week"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Sessions This Week</span>
                    <span class="info-box-number">{{ array_sum(array_map('count', $dayEntries)) }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="info-box shadow-sm mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-double"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Attended / Late</span>
                    <span class="info-box-number">{{ $presentCount }} / {{ $lateCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="info-box shadow-sm mb-3">
                <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-book-open"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Topics / Subtopics</span>
                    <span class="info-box-number">{{ $topicsCoveredThisWeek }} / {{ $subtopicsCoveredThisWeek }}</span>
                    <span class="info-box-text" style="font-size:.72rem">logged this week</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="info-box shadow-sm mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-chart-pie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Attendance Rate</span>
                    <span class="info-box-number">
                        @if($presentRate !== null){{ $presentRate }}%@else—@endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Curriculum Progress ── --}}
    @if($lessonPlans->count())
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tasks mr-2"></i>Curriculum Progress</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body pb-2">
            <div class="row">
                @foreach($lessonPlans as $planKey => $plan)
                    @php
                        $totalSubs   = 0;
                        $coveredSubs = 0;
                        foreach($plan->topics as $t) {
                            $totalSubs   += $t->subtopics_count ?? 0;
                            $coveredSubs += $t->covered_count ?? 0;
                        }
                        $planPct = $totalSubs > 0 ? round($coveredSubs / $totalSubs * 100) : 0;
                        $planColor = $planPct >= 80 ? 'success' : ($planPct >= 50 ? 'primary' : ($planPct >= 25 ? 'warning' : 'danger'));
                    @endphp
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card card-body p-3 border h-100" style="border-radius:8px">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div>
                                    <strong class="d-block" style="font-size:.9rem">{{ $plan->subject?->name ?? '—' }}</strong>
                                    <small class="text-muted">{{ $plan->schoolClass?->name ?? '' }}</small>
                                </div>
                                <span class="badge badge-{{ $planColor }} px-2 py-1">{{ $planPct }}%</span>
                            </div>
                            <div class="progress mb-1" style="height:8px;border-radius:4px">
                                <div class="progress-bar bg-{{ $planColor }}" style="width:{{ $planPct }}%"></div>
                            </div>
                            <small class="text-muted">{{ $coveredSubs }}/{{ $totalSubs }} subtopics covered</small>
                            @if($plan->topics->count())
                            <div class="mt-2" style="max-height:120px;overflow-y:auto">
                                @foreach($plan->topics as $t)
                                    @php
                                        $tPct = ($t->subtopics_count ?? 0) > 0
                                            ? round(($t->covered_count ?? 0) / $t->subtopics_count * 100)
                                            : 0;
                                    @endphp
                                    <div class="d-flex align-items-center mb-1" style="gap:.4rem">
                                        <div class="flex-grow-1">
                                            <div style="font-size:.75rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px" title="{{ $t->title }}">
                                                {{ $t->title }}
                                            </div>
                                            <div class="progress" style="height:4px;border-radius:2px">
                                                <div class="progress-bar bg-{{ $tPct >= 100 ? 'success' : 'info' }}" style="width:{{ $tPct }}%"></div>
                                            </div>
                                        </div>
                                        <small class="text-muted text-nowrap">{{ $t->covered_count ?? 0 }}/{{ $t->subtopics_count ?? 0 }}</small>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ── Day Tabs ── --}}
    <div class="card card-primary card-outline shadow">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="dayTabs" role="tablist">
                @foreach($weekDates as $dow => $date)
                    @php
                        $isToday  = $date->isToday();
                        $isFuture = $date->isFuture();
                        $dayList  = $dayEntries[$dow] ?? [];
                        $dAtt = 0; $dLate = 0; $dAbs = 0; $dOth = 0;
                        foreach($dayList as $e) {
                            $lk = $logsMap[$e->id . '_' . $date->toDateString()] ?? null;
                            match($lk?->status) {
                                'attended' => $dAtt++, 'late' => $dLate++,
                                'absent'   => $dAbs++, 'other' => $dOth++,
                                default    => null,
                            };
                        }
                        $dLogged  = $dAtt + $dLate + $dAbs + $dOth;
                        $dPending = count($dayList) - $dLogged;
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link @if($dow == $activeTab) active @endif @if($isToday) font-weight-bold @endif"
                           id="tab-{{ $dow }}" data-toggle="tab" href="#day-{{ $dow }}" role="tab">
                            <span>{{ $dayNames[$dow] }}</span><br>
                            <small class="text-muted">{{ $date->format('d M') }}</small>
                            @if(count($dayList) > 0)
                                <br>
                                @if($dAtt + $dLate > 0)
                                    <span class="badge badge-success badge-pill">{{ $dAtt + $dLate }}</span>
                                @endif
                                @if($dAbs > 0)
                                    <span class="badge badge-danger badge-pill">{{ $dAbs }}</span>
                                @endif
                                @if($dPending > 0 && !$isFuture)
                                    <span class="badge badge-warning badge-pill">{{ $dPending }}</span>
                                @endif
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="dayTabContent">
                @foreach($weekDates as $dow => $date)
                    @php
                        $dayList  = $dayEntries[$dow] ?? [];
                        $isFuture = $date->isFuture();
                    @endphp
                    <div class="tab-pane fade @if($dow == $activeTab) show active @endif"
                         id="day-{{ $dow }}" role="tabpanel">

                        @if(empty($dayList))
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-couch fa-2x mb-2"></i>
                                <p class="mb-0">No sessions scheduled for {{ $dayNames[$dow] }}.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="90">Period</th>
                                            <th width="120">Time</th>
                                            <th>Class</th>
                                            <th>Subject</th>
                                            <th width="80">Room</th>
                                            <th class="text-center" style="min-width:280px">Status</th>
                                            <th style="min-width:220px">Topic Covered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dayList as $entry)
                                            @php
                                                $logKey = $entry->id . '_' . $date->toDateString();
                                                $log    = $logsMap[$logKey] ?? null;
                                                $canAct = !$isFuture;
                                                $rowBg  = match($log?->status) {
                                                    'attended' => 'table-success',
                                                    'late'     => 'table-warning',
                                                    'absent'   => 'table-danger',
                                                    default    => $isFuture ? '' : '',
                                                };
                                                $planKey = "{$entry->subject_id}_{$entry->class_id}";
                                                $hasPlan = isset($lessonPlans[$planKey]);
                                            @endphp
                                            <tr class="{{ $rowBg }}">
                                                <td>
                                                    <span class="badge badge-secondary badge-pill px-2">
                                                        {{ $entry->period?->name ?? '—' }}
                                                    </span>
                                                </td>
                                                <td class="text-nowrap small">
                                                    <i class="far fa-clock text-muted mr-1"></i>
                                                    {{ $entry->start_time ?? $entry->period?->start_time ?? '—' }}–{{ $entry->end_time ?? $entry->period?->end_time ?? '' }}
                                                </td>
                                                <td><i class="fas fa-users text-muted mr-1"></i>{{ $entry->schoolClass?->name ?? '—' }}</td>
                                                <td><strong>{{ $entry->subject?->name ?? '—' }}</strong></td>
                                                <td>{{ $entry->room ?: '—' }}</td>

                                                {{-- Status Column --}}
                                                <td class="text-center align-middle">
                                                    @if($isFuture)
                                                        <span class="badge badge-light text-muted border px-3 py-1">
                                                            <i class="far fa-calendar mr-1"></i> Upcoming
                                                        </span>
                                                    @else
                                                        @php
                                                            // Attendance is marked by the class coordinator/management,
                                                            // not self-reported by the subject teacher.
                                                            $canMark = $isHR || in_array($entry->class_id, $coordinatedClassIds ?? []);
                                                        @endphp
                                                        {{-- Status buttons / badge --}}
                                                        @if($log && $log->status)
                                                            @php $sc = $statusCfg[$log->status]; @endphp
                                                            <div class="d-flex align-items-center justify-content-center flex-wrap" style="gap:.3rem">
                                                                <span class="badge badge-{{ $sc['badge'] }} px-3 py-1" style="font-size:.82rem">
                                                                    <i class="fas {{ $sc['icon'] }} mr-1"></i>{{ $sc['label'] }}
                                                                </span>
                                                                @if($canMark)
                                                                <button class="btn btn-xs btn-outline-secondary"
                                                                        data-toggle="collapse"
                                                                        data-target="#form-{{ $entry->id }}-{{ $dow }}">
                                                                    <i class="fas fa-pencil-alt"></i>
                                                                </button>
                                                                @endif
                                                            </div>
                                                            @if($log->notes)
                                                                <small class="text-muted d-block mt-1">
                                                                    <i class="fas fa-comment-alt mr-1"></i>{{ $log->notes }}
                                                                </small>
                                                            @endif
                                                        @elseif($canMark)
                                                            <div class="d-flex flex-wrap justify-content-center" style="gap:.25rem">
                                                                @foreach($statusCfg as $statusKey => $scfg)
                                                                    <button type="button"
                                                                            class="btn btn-sm {{ $scfg['btn'] }} quick-status-btn"
                                                                            data-entry="{{ $entry->id }}"
                                                                            data-dow="{{ $dow }}"
                                                                            data-status="{{ $statusKey }}"
                                                                            data-target="#form-{{ $entry->id }}-{{ $dow }}">
                                                                        <i class="fas {{ $scfg['icon'] }} mr-1"></i>{{ $scfg['label'] }}
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="badge badge-light text-muted border px-3 py-1">
                                                                <i class="far fa-hourglass mr-1"></i> Awaiting coordinator
                                                            </span>
                                                            @if($log?->notes)
                                                                <small class="text-muted d-block mt-1">
                                                                    <i class="fas fa-comment-alt mr-1"></i>{{ $log->notes }}
                                                                </small>
                                                            @endif
                                                        @endif

                                                        {{-- Collapsible log form --}}
                                                        <div class="collapse mt-2" id="form-{{ $entry->id }}-{{ $dow }}">
                                                            <form method="POST"
                                                                  action="{{ route('timetables.log-session', $entry) }}"
                                                                  class="session-log-form text-left"
                                                                  data-entry="{{ $entry->id }}"
                                                                  data-has-plan="{{ $hasPlan ? 'true' : 'false' }}">
                                                                @csrf
                                                                <input type="hidden" name="session_date" value="{{ $date->toDateString() }}">
                                                                <input type="hidden" name="week" value="{{ $weekOffset }}">
                                                                @if($isHR && $teacherId) <input type="hidden" name="teacher_id" value="{{ $teacherId }}"> @endif

                                                                {{-- Status radio --}}
                                                                <div class="mb-2">
                                                                    <div class="d-flex flex-wrap" style="gap:.25rem">
                                                                        @foreach($statusCfg as $sk => $sc2)
                                                                            <div class="custom-control custom-radio custom-control-inline">
                                                                                <input type="radio" id="s-{{ $entry->id }}-{{ $sk }}"
                                                                                       name="status" value="{{ $sk }}"
                                                                                       class="custom-control-input"
                                                                                       @checked($log?->status === $sk)>
                                                                                <label class="custom-control-label small" for="s-{{ $entry->id }}-{{ $sk }}">
                                                                                    <i class="fas {{ $sc2['icon'] }} text-{{ $sc2['badge'] }}"></i> {{ $sc2['label'] }}
                                                                                </label>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>

                                                                {{-- Notes --}}
                                                                <div class="mb-2">
                                                                    <textarea name="notes" rows="1" class="form-control form-control-sm"
                                                                              placeholder="Notes (optional)">{{ $log?->notes }}</textarea>
                                                                </div>

                                                                <button type="submit" class="btn btn-sm btn-primary btn-block">
                                                                    <i class="fas fa-save mr-1"></i> Save Status
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </td>

                                                {{-- Topic Column --}}
                                                <td class="align-middle">
                                                    @if($isFuture)
                                                        <span class="text-muted small">—</span>
                                                    @elseif(!$hasPlan)
                                                        <span class="text-muted small">
                                                            <i class="fas fa-info-circle mr-1"></i>No lesson plan
                                                        </span>
                                                    @else
                                                        {{-- Show logged topic/subtopics or input --}}
                                                        @if($log && $log->lesson_topic_id)
                                                            <div>
                                                                <span class="badge badge-info mb-1" style="font-size:.75rem;white-space:normal;text-align:left">
                                                                    <i class="fas fa-book mr-1"></i>{{ $log->topic?->title ?? '—' }}
                                                                </span>
                                                                @if($log->coveredSubtopics->count())
                                                                    <div class="mt-1">
                                                                        @foreach($log->coveredSubtopics as $sub)
                                                                            <span class="badge badge-light border text-dark" style="font-size:.7rem">
                                                                                <i class="fas fa-check text-success mr-1"></i>{{ $sub->title }}
                                                                            </span>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                                <button type="button" class="btn btn-xs btn-outline-secondary mt-1 topic-edit-btn"
                                                                        data-entry="{{ $entry->id }}"
                                                                        data-date="{{ $date->toDateString() }}"
                                                                        data-logged-topic="{{ $log->lesson_topic_id }}"
                                                                        data-logged-subs='@json($log->coveredSubtopics->pluck("id"))'
                                                                        data-status="{{ $log->status }}"
                                                                        data-notes="{{ $log->notes ?? '' }}">
                                                                    <i class="fas fa-pencil-alt"></i> Edit Topic
                                                                </button>
                                                            </div>
                                                        @else
                                                            <button type="button" class="btn btn-xs btn-outline-primary topic-edit-btn"
                                                                    data-entry="{{ $entry->id }}"
                                                                    data-date="{{ $date->toDateString() }}"
                                                                    data-logged-topic=""
                                                                    data-logged-subs='[]'
                                                                    data-status=""
                                                                    data-notes="">
                                                                <i class="fas fa-plus mr-1"></i>Log Topic
                                                            </button>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>

                                            {{-- Topic form (hidden, shown via modal) --}}
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ── Topic / Subtopic Modal ── --}}
<div class="modal fade" id="topicModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-book-open mr-2"></i>Log Topic Covered</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="topicForm" method="POST" action="">
                @csrf
                <input type="hidden" name="session_date" id="tf_date">
                <input type="hidden" name="week" value="{{ $weekOffset }}">
                @if($isHR && $teacherId) <input type="hidden" name="teacher_id" value="{{ $teacherId }}"> @endif
                <div class="modal-body">
                    <div id="topicLoading" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2 text-muted">Loading lesson plan…</p>
                    </div>
                    <div id="topicContent" style="display:none">
                        <div class="form-group">
                            <label class="font-weight-bold">Topic Taught <span class="text-muted font-weight-normal">(select from lesson plan)</span></label>
                            <select name="lesson_topic_id" id="tf_topic" class="form-control">
                                <option value="">— Select topic —</option>
                            </select>
                        </div>
                        <div class="form-group" id="subtopicSection" style="display:none">
                            <label class="font-weight-bold">Subtopics Covered</label>
                            <small class="text-muted d-block mb-2">Check all subtopics you covered in this session. Checked ones will be marked as covered in the curriculum.</small>
                            <div id="subtopicList" class="subtopic-checklist"></div>
                        </div>
                        <div class="form-group">
                            <label>Mark Session Status</label>
                            <div class="d-flex flex-wrap" style="gap:.5rem" id="tf_statusRow">
                                @foreach($statusCfg as $sk => $sc2)
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="tf_s_{{ $sk }}" name="status" value="{{ $sk }}" class="custom-control-input">
                                        <label class="custom-control-label" for="tf_s_{{ $sk }}">
                                            <i class="fas {{ $sc2['icon'] }} text-{{ $sc2['badge'] }}"></i> {{ $sc2['label'] }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes <small class="text-muted">(optional)</small></label>
                            <textarea name="notes" id="tf_notes" rows="2" class="form-control" placeholder="Any notes for this session…"></textarea>
                        </div>
                    </div>
                    <div id="topicNoPlan" class="alert alert-info" style="display:none">
                        <i class="fas fa-info-circle mr-1"></i>
                        No lesson plan found for this subject and class.
                        <a href="{{ route('topic-coverage.index') }}" class="alert-link">Create a lesson plan</a> first.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="tf_save">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('css')
<style>
    .nav-tabs .nav-link { text-align:center; min-width:110px; padding:.6rem .8rem; }
    .nav-tabs .nav-link.active { border-bottom-color:transparent; }
    .info-box { margin-bottom:1rem; }
    .table td, .table th { vertical-align:middle; }
    .badge-pill { font-size:.7rem; }

    .subtopic-checklist { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:.4rem; }
    .subtopic-item { display:flex; align-items:flex-start; gap:.4rem; padding:.4rem .6rem; border:1px solid #e9ecef; border-radius:6px; cursor:pointer; transition:background .15s; }
    .subtopic-item:hover { background:#f0f4ff; }
    .subtopic-item.is-covered { background:#f0fdf4; border-color:#86efac; }
    .subtopic-item input[type=checkbox] { margin-top:.1rem; flex-shrink:0; }
    .subtopic-item .sub-label { font-size:.82rem; line-height:1.3; }
    .subtopic-item .covered-badge { font-size:.65rem; color:#16a34a; }
</style>
@endpush

@push('js')
<script>
(function () {
    const CSRF = '{{ csrf_token() }}';
    let currentTopics = [];
    let currentEntryId = null;

    // ── Quick status buttons (open form pre-filled) ──
    document.querySelectorAll('.quick-status-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.dataset.target;
            const entryId = this.dataset.entry;
            const status  = this.dataset.status;
            // Pre-check the radio in the form
            const radio = document.getElementById(`s-${entryId}-${status}`);
            if (radio) radio.checked = true;
            $(target).collapse('show');
        });
    });

    // ── Topic edit buttons → open modal ──
    document.querySelectorAll('.topic-edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            openTopicModal(this);
        });
    });

    function openTopicModal(btn) {
        const entryId    = btn.dataset.entry;
        const date       = btn.dataset.date;
        const logTopic   = parseInt(btn.dataset.loggedTopic) || null;
        const logSubs    = JSON.parse(btn.dataset.loggedSubs || '[]');
        const logStatus  = btn.dataset.status || '';
        const logNotes   = btn.dataset.notes  || '';
        currentEntryId   = entryId;

        // Set form action
        document.getElementById('topicForm').action = `/timetables/entries/${entryId}/log`;
        document.getElementById('tf_date').value = date;

        // Reset modal state
        document.getElementById('topicLoading').style.display = '';
        document.getElementById('topicContent').style.display = 'none';
        document.getElementById('topicNoPlan').style.display = 'none';
        document.getElementById('tf_save').style.display = '';

        // Pre-fill status radio
        document.querySelectorAll('#topicModal input[name="status"]').forEach(r => r.checked = false);
        if (logStatus) {
            const radio = document.getElementById('tf_s_' + logStatus);
            if (radio) radio.checked = true;
        }

        // Pre-fill notes
        document.getElementById('tf_notes').value = logNotes;

        $('#topicModal').modal('show');

        // Load topics from server
        fetch(`/timetables/entries/${entryId}/topics`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('topicLoading').style.display = 'none';
            const noPlanEl = document.getElementById('topicNoPlan');
            if (data.no_plan) {
                noPlanEl.innerHTML = '<i class="fas fa-info-circle mr-1"></i>No lesson plan found for this subject and class. <a href="{{ route('topic-coverage.index') }}" class="alert-link">Create a lesson plan</a> first.';
                noPlanEl.style.display = '';
                document.getElementById('tf_save').style.display = 'none';
                return;
            }
            if (!data.topics || data.topics.length === 0) {
                noPlanEl.innerHTML = '<i class="fas fa-book mr-1"></i>A lesson plan exists but has no topics yet. <a href="{{ route('topic-coverage.index') }}" class="alert-link">Add topics to the lesson plan</a> first, then you can log them here.';
                noPlanEl.style.display = '';
                document.getElementById('tf_save').style.display = 'none';
                return;
            }
            document.getElementById('tf_save').style.display = '';
            currentTopics = data.topics;
            buildTopicSelect(data.topics, logTopic);
            if (logTopic) {
                renderSubtopics(logTopic, logSubs);
            }
            document.getElementById('topicContent').style.display = '';
        })
        .catch(() => {
            document.getElementById('topicLoading').innerHTML = '<div class="alert alert-danger">Failed to load topics.</div>';
        });
    }

    function buildTopicSelect(topics, selectedId) {
        const sel = document.getElementById('tf_topic');
        sel.innerHTML = '<option value="">— Select topic —</option>';
        topics.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.title;
            if (t.id === selectedId) opt.selected = true;
            sel.appendChild(opt);
        });
        sel.onchange = function () {
            renderSubtopics(parseInt(this.value), []);
        };
        if (selectedId) sel.dispatchEvent(new Event('change'));
    }

    function renderSubtopics(topicId, checkedIds) {
        const section = document.getElementById('subtopicSection');
        const list    = document.getElementById('subtopicList');

        if (!topicId) {
            section.style.display = 'none';
            list.innerHTML = '';
            return;
        }

        const topic = currentTopics.find(t => t.id === topicId);
        if (!topic || !topic.subtopics.length) {
            section.style.display = 'none';
            return;
        }

        section.style.display = '';
        list.innerHTML = topic.subtopics.map(s => {
            const checked      = checkedIds.includes(s.id);
            const coveredBadge = s.covered ? '<span class="covered-badge"><i class="fas fa-check-circle"></i> Already covered</span>' : '';
            return `<label class="subtopic-item ${s.covered ? 'is-covered' : ''}">
                <input type="checkbox" name="covered_subtopic_ids[]" value="${s.id}" ${checked ? 'checked' : ''}>
                <span>
                    <span class="sub-label">${escHtml(s.title)}</span>
                    ${coveredBadge}
                </span>
            </label>`;
        }).join('');
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Make the whole subtopic-item label clickable correctly ──
    document.getElementById('subtopicList')?.addEventListener('change', e => {
        if (e.target.type === 'checkbox') {
            e.target.closest('.subtopic-item')?.classList.toggle('is-covered', e.target.checked);
        }
    });

})();
</script>
@endpush
