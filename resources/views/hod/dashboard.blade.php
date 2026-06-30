@extends('adminlte::page')

@section('title', 'HOD Dashboard – ' . $dept->name)

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
    <div>
        <h1 class="m-0">
            <i class="fas fa-user-tie mr-2 text-primary"></i>
            HOD Dashboard
            <small class="text-muted" style="font-size:.55em;font-weight:400">{{ $dept->name }} Department</small>
        </h1>
        <small class="text-muted"><i class="fas fa-calendar-alt mr-1"></i>{{ $now->format('F Y') }}</small>
    </div>
    <div class="d-flex" style="gap:.4rem">
        <a href="{{ route('leaves.received') }}" class="btn btn-sm btn-outline-warning position-relative">
            <i class="fas fa-file-signature mr-1"></i>Leave Requests
            @if($leavesPending > 0)
            <span class="badge badge-danger" style="position:absolute;top:-6px;right:-6px;font-size:.6rem">{{ $leavesPending }}</span>
            @endif
        </a>
        <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-boxes mr-1"></i>Inventory
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

@if(session('error'))
<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('error') }}</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     TOP KPI ROW
══════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Staff</span>
                <span class="info-box-number">{{ $totalStaff }}</span>
                <span class="progress-description">in {{ $dept->name }}</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        @php $arColor = $attRate === null ? 'secondary' : ($attRate >= 90 ? 'success' : ($attRate >= 75 ? 'warning' : 'danger')); @endphp
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-{{ $arColor }}"><i class="fas fa-calendar-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Attendance Rate</span>
                <span class="info-box-number">{{ $attRate !== null ? $attRate.'%' : 'N/A' }}</span>
                <span class="progress-description">{{ $attPresent }}/{{ $attTotal }} days this month</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        @php $covColor = $overallCoverage >= 80 ? 'success' : ($overallCoverage >= 50 ? 'primary' : ($overallCoverage >= 25 ? 'warning' : 'danger')); @endphp
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-{{ $covColor }}"><i class="fas fa-book-open"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Curriculum Coverage</span>
                <span class="info-box-number">{{ $overallCoverage }}%</span>
                <span class="progress-description">{{ $coveredSubtopics }}/{{ $totalSubtopics }} subtopics</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info"><i class="fas fa-wallet"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Budget Approved</span>
                <span class="info-box-number" style="font-size:1rem">TZS {{ number_format($budgetApproved, 0) }}</span>
                <span class="progress-description">of TZS {{ number_format($budgetTotal, 0) }} submitted</span>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 1 — STAFF OVERVIEW
══════════════════════════════════════════════════════════════════ --}}
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Staff Overview — {{ $now->format('F Y') }}</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body p-0">

        {{-- Today's quick status --}}
        <div class="px-3 pt-3 pb-2">
            <h6 class="text-muted mb-2" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em">Today's Status</h6>
            <div class="d-flex flex-wrap" style="gap:.4rem">
                @foreach($staffDetails as $sd)
                @php
                    $todayStatus = $todayAtt[$sd['staff']->id] ?? null;
                    $dot = $todayStatus === 'present' ? 'success' : ($todayStatus === 'late' ? 'warning' : ($todayStatus === 'absent' ? 'danger' : 'secondary'));
                @endphp
                <span class="badge badge-{{ $dot }} py-1 px-2" style="font-size:.72rem" title="{{ $todayStatus ?? 'not marked' }}">
                    <i class="fas fa-circle mr-1" style="font-size:.5rem"></i>{{ $sd['staff']->first_name }}
                </span>
                @endforeach
            </div>
        </div>

        {{-- Per-staff table --}}
        <div class="table-responsive mt-2">
            <table class="table table-sm table-hover mb-0" style="font-size:.84rem">
                <thead class="thead-light">
                    <tr>
                        <th>Staff Member</th>
                        <th>Position</th>
                        <th>Attendance</th>
                        <th>Leaves</th>
                        <th>Sessions Logged</th>
                        <th>Job Cards</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffDetails as $sd)
                    @php
                        $ar = $sd['att_rate'];
                        $arBadge = $ar === null ? 'secondary' : ($ar >= 90 ? 'success' : ($ar >= 75 ? 'warning' : 'danger'));
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $sd['staff']->first_name }} {{ $sd['staff']->last_name }}</strong>
                            @if($sd['staff']->user_id === auth()->id())
                            <span class="badge badge-primary ml-1" style="font-size:.6rem">You</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $sd['staff']->position ?: '—' }}</td>
                        <td>
                            @if($ar !== null)
                            <span class="badge badge-{{ $arBadge }}">{{ $ar }}%</span>
                            <small class="text-muted ml-1">{{ $sd['att_days'] }}/{{ $sd['att_total'] }} days</small>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($sd['leaves'] > 0)
                            <span class="badge badge-warning">{{ $sd['leaves'] }} approved</span>
                            @else
                            <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            @if($sd['session_logs'] > 0)
                            <span class="badge badge-info">{{ $sd['session_logs'] }}</span>
                            @else
                            <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            @if($sd['jc_rate'] !== null)
                            <span class="badge badge-{{ $sd['jc_rate'] >= 80 ? 'success' : 'warning' }}">{{ $sd['jc_rate'] }}% done</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 2 — ACADEMIC
══════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-lg-7">
        <div class="card card-outline card-success shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-book-open mr-2"></i>Academic — Curriculum Coverage</h3>
                <div class="card-tools">
                    <a href="{{ route('topic-coverage.evaluation') }}" class="btn btn-tool btn-sm text-success" title="Full Evaluation">
                        <i class="fas fa-chart-line"></i>
                    </a>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">

                {{-- Overall bar --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted" style="font-size:.82rem">Overall Department Coverage</span>
                        <strong class="text-{{ $covColor }}">{{ $overallCoverage }}%</strong>
                    </div>
                    <div class="progress" style="height:10px;border-radius:5px">
                        <div class="progress-bar bg-{{ $covColor }}" style="width:{{ $overallCoverage }}%"></div>
                    </div>
                    <small class="text-muted">{{ $coveredSubtopics }} of {{ $totalSubtopics }} subtopics covered across {{ $totalPlans }} lesson plans</small>
                </div>

                {{-- Per-plan breakdown --}}
                @if($planStats->isEmpty())
                <div class="text-center text-muted py-3">
                    <i class="fas fa-info-circle mr-1"></i>No lesson plans for {{ $dept->name }} subjects yet.
                    <div class="mt-1"><a href="{{ route('topic-coverage.index') }}" class="btn btn-sm btn-outline-success mt-1">Create Lesson Plan</a></div>
                </div>
                @else
                <div style="max-height:220px;overflow-y:auto">
                    @foreach($planStats as $ps)
                    @php $pc = $ps['pct'] >= 80 ? 'success' : ($ps['pct'] >= 50 ? 'primary' : ($ps['pct'] >= 25 ? 'warning' : 'danger')); @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1" style="font-size:.8rem">
                            <span class="text-truncate" style="max-width:70%" title="{{ $ps['plan']->subject->name }} – {{ $ps['plan']->schoolClass->name }}">
                                {{ $ps['plan']->subject->name }}
                                <span class="text-muted">· {{ $ps['plan']->schoolClass->name }}</span>
                            </span>
                            <span class="text-{{ $pc }} font-weight-bold">{{ $ps['pct'] }}%
                                <small class="text-muted font-weight-normal">({{ $ps['covered'] }}/{{ $ps['total'] }})</small>
                            </span>
                        </div>
                        <div class="progress" style="height:5px;border-radius:3px">
                            <div class="progress-bar bg-{{ $pc }}" style="width:{{ $ps['pct'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-outline card-info shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chalkboard-teacher mr-2"></i>Sessions — {{ $now->format('F') }}</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="text-info" style="font-size:1.4rem;font-weight:700">{{ $sessTotal }}</div>
                        <small class="text-muted">Logged</small>
                    </div>
                    <div class="col-4">
                        <div class="{{ $sessRate !== null && $sessRate < 80 ? 'text-danger' : 'text-success' }}" style="font-size:1.4rem;font-weight:700">
                            {{ $sessRate !== null ? $sessRate.'%' : 'N/A' }}
                        </div>
                        <small class="text-muted">Attended</small>
                    </div>
                    <div class="col-4">
                        <div class="{{ $topicRate !== null && $topicRate < 60 ? 'text-warning' : 'text-primary' }}" style="font-size:1.4rem;font-weight:700">
                            {{ $topicRate !== null ? $topicRate.'%' : 'N/A' }}
                        </div>
                        <small class="text-muted">Topic Logged</small>
                    </div>
                </div>

                {{-- Mini doughnut --}}
                @if($sessTotal > 0)
                <canvas id="sessChart" height="140"></canvas>
                @else
                <div class="text-center text-muted py-3"><i class="fas fa-calendar-times mr-1"></i>No sessions recorded this month.</div>
                @endif

                <hr class="my-2">

                {{-- Recent sessions --}}
                <h6 class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase">Recent Sessions</h6>
                @foreach($recentSessions->take(5) as $sl)
                @php $sc = ['attended'=>'success','late'=>'warning','absent'=>'danger'][$sl->status] ?? 'secondary'; @endphp
                <div class="d-flex align-items-center mb-1" style="font-size:.78rem;gap:.4rem">
                    <span class="badge badge-{{ $sc }}" style="min-width:55px">{{ ucfirst($sl->status) }}</span>
                    <span class="text-truncate">{{ $sl->subject->name ?? '—' }} · {{ $sl->schoolClass->name ?? '—' }}</span>
                    <span class="text-muted ml-auto">{{ $sl->session_date->format('d M') }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 3 — LEAVES & JOB CARDS (side by side)
══════════════════════════════════════════════════════════════════ --}}
<div class="row">
    {{-- Leaves --}}
    <div class="col-lg-6">
        <div class="card card-outline card-warning shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-file-signature mr-2"></i>Leave Requests</h3>
                <div class="d-flex align-items-center" style="gap:.4rem">
                    @if($leavesPending > 0)
                    <span class="badge badge-danger">{{ $leavesPending }} pending</span>
                    @endif
                    <a href="{{ route('leaves.received') }}" class="btn btn-xs btn-outline-warning">Manage</a>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                @forelse($recentLeaves as $lv)
                @php $lc = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$lv->status] ?? 'secondary'; @endphp
                <div class="d-flex align-items-center px-3 py-2 border-bottom" style="font-size:.84rem">
                    <div class="flex-grow-1">
                        <strong>{{ $lv->requester->first_name ?? '—' }} {{ $lv->requester->last_name ?? '' }}</strong>
                        <small class="text-muted d-block">{{ ucfirst($lv->type) }} · {{ $lv->start_date->format('d M') }} – {{ $lv->end_date->format('d M Y') }}</small>
                    </div>
                    <span class="badge badge-{{ $lc }} ml-2">{{ ucfirst($lv->status) }}</span>
                </div>
                @empty
                <div class="text-center text-muted py-4"><i class="fas fa-check-circle text-success mr-1"></i>No leave requests.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Job Cards --}}
    <div class="col-lg-6">
        <div class="card card-outline card-secondary shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-briefcase mr-2"></i>Job Cards</h3>
                <div class="d-flex align-items-center" style="gap:.4rem">
                    <a href="{{ route('jobcards.index') }}" class="btn btn-xs btn-outline-secondary">View All</a>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="text-warning font-weight-bold" style="font-size:1.3rem">{{ $jcPending }}</div>
                        <small class="text-muted">Pending</small>
                    </div>
                    <div class="col-4">
                        <div class="text-info font-weight-bold" style="font-size:1.3rem">{{ $jcInProgress }}</div>
                        <small class="text-muted">In Progress</small>
                    </div>
                    <div class="col-4">
                        <div class="text-success font-weight-bold" style="font-size:1.3rem">{{ $jcCompleted }}</div>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
                @foreach($recentJobs as $jc)
                @php $jcs = ['pending'=>'warning','in_progress'=>'info','completed'=>'success'][$jc->status] ?? 'secondary'; @endphp
                <div class="d-flex align-items-center mb-2" style="font-size:.82rem;gap:.5rem">
                    <span class="badge badge-{{ $jcs }}" style="min-width:68px">{{ str_replace('_',' ',ucfirst($jc->status)) }}</span>
                    <div class="flex-grow-1 text-truncate">
                        <span>{{ $jc->title }}</span>
                        <small class="text-muted d-block">Assigned to: {{ $jc->assignee->first_name ?? '—' }}</small>
                    </div>
                    @if($jc->due_date)
                    <small class="text-muted">{{ $jc->due_date->format('d M') }}</small>
                    @endif
                </div>
                @endforeach
                @if($recentJobs->isEmpty())
                <div class="text-center text-muted py-2"><i class="fas fa-tasks mr-1"></i>No job cards for this department.</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 4 — FINANCIAL / BUDGETS
══════════════════════════════════════════════════════════════════ --}}
<div class="card card-outline card-dark shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-university mr-2"></i>Department Budgets</h3>
        <div class="d-flex align-items-center" style="gap:.4rem">
            <a href="{{ route('finance.budgets.hod') }}" class="btn btn-xs btn-outline-dark">Full Budget Dashboard</a>
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="info-box mb-0">
                    <span class="info-box-icon bg-secondary"><i class="fas fa-file-invoice"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Submitted</span>
                        <span class="info-box-number" style="font-size:.95rem">TZS {{ number_format($budgetTotal, 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box mb-0">
                    <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Approved</span>
                        <span class="info-box-number" style="font-size:.95rem">TZS {{ number_format($budgetApproved, 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box mb-0">
                    <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pending Approval</span>
                        <span class="info-box-number" style="font-size:.95rem">TZS {{ number_format($budgetPending, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Budget status breakdown --}}
        @if($budgetByStatus->isNotEmpty())
        <div class="d-flex flex-wrap mb-3" style="gap:.5rem">
            @foreach(['pending'=>'warning','partially_approved'=>'info','approved'=>'success','in_use'=>'primary','completed'=>'dark'] as $st => $c)
            @if(isset($budgetByStatus[$st]))
            <span class="badge badge-{{ $c }} py-1 px-2">{{ ucwords(str_replace('_',' ',$st)) }}: {{ $budgetByStatus[$st] }}</span>
            @endif
            @endforeach
        </div>
        @endif

        {{-- Recent budgets table --}}
        @if($recentBudgets->isNotEmpty())
        <table class="table table-sm table-hover mb-0" style="font-size:.84rem">
            <thead class="thead-light">
                <tr><th>Month/Year</th><th>Total</th><th>Status</th><th>Step</th></tr>
            </thead>
            <tbody>
                @foreach($recentBudgets as $bg)
                @php $bc = ['pending'=>'warning','partially_approved'=>'info','approved'=>'success','in_use'=>'primary','completed'=>'dark'][$bg->status] ?? 'secondary'; @endphp
                <tr>
                    <td>{{ $bg->month }}/{{ $bg->year }}</td>
                    <td>TZS {{ number_format($bg->total_amount, 0) }}</td>
                    <td><span class="badge badge-{{ $bc }}">{{ ucwords(str_replace('_',' ',$bg->status)) }}</span></td>
                    <td class="text-muted">{{ ucfirst($bg->current_step ?? '—') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-center text-muted py-3"><i class="fas fa-file-invoice mr-1"></i>No budgets submitted yet.</div>
        @endif

        <div class="mt-2">
            <a href="{{ route('finance.budgets.create') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus mr-1"></i>Submit New Budget
            </a>
        </div>
    </div>
</div>

</div>
@endsection

@push('js')
@if($sessTotal > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@3/dist/chart.min.js"></script>
<script>
new Chart(document.getElementById('sessChart'), {
    type: 'doughnut',
    data: {
        labels: ['Attended', 'Late', 'Absent', 'Not logged'],
        datasets: [{
            data: [
                {{ $sessionLogs->where('status','attended')->count() }},
                {{ $sessionLogs->where('status','late')->count() }},
                {{ $sessAbsent }},
                0
            ],
            backgroundColor: ['#28a745','#ffc107','#dc3545','#6c757d'],
            borderWidth: 2,
        }]
    },
    options: {
        cutout: '65%',
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
    }
});
</script>
@endif
@endpush
