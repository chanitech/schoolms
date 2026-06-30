<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $school->name ?? 'School' }} – {{ $exam->name ?? 'Exam' }} Results</title>
    <style>
        @page { size: A3 landscape; margin: 8mm; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #111827;
            background: #ffffff;
            line-height: 1.35;
        }

        /* ── HEADER ──────────────────────────────── */
        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: linear-gradient(180deg, #0d5b47 0%, #1F7A63 100%);
            border: 3px solid #0a4535;
        }
        .header td { border: none; padding: 14px 18px; vertical-align: middle; }
        .logo { width: 95px; text-align: center; }
        .logo img { width: 78px; height: 78px; object-fit: contain; border-radius: 4px; }
        .school-name {
            font-size: 32px; font-weight: 900; color: #ffffff;
            letter-spacing: 2px; text-transform: uppercase; text-align: center;
        }
        .exam-title {
            font-size: 20px; font-weight: 900; color: #a7f3d0;
            text-align: center; margin-top: 5px; letter-spacing: 1px;
        }
        .subtitle {
            font-size: 11px; color: #d1fae5; text-align: center;
            margin-top: 4px; font-weight: 700; letter-spacing: .5px;
        }

        /* ── INFO BAR ────────────────────────────── */
        .info-bar {
            width: 100%; border-collapse: collapse; margin-bottom: 10px;
        }
        .info-bar td {
            border: 2px solid #0d5b47;
            background: #0d5b47;
            color: #ffffff;
            padding: 9px 12px; font-size: 13px; font-weight: 900;
            text-align: center; text-transform: uppercase; letter-spacing: .5px;
        }

        /* ── TOP PERFORMER BANNER ─────────────────── */
        .champion-box {
            width: 100%; border: 3px solid #f59e0b;
            background: #fffbeb; margin-bottom: 10px;
            border-collapse: collapse;
        }
        .champion-box td { border: none; padding: 12px 20px; }
        .champion-label {
            font-size: 11px; font-weight: 900; color: #92400e;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;
        }
        .champion-trophy { font-size: 26px; text-align: center; }
        .champion-name {
            font-size: 26px; font-weight: 900; color: #111827;
        }
        .champion-stats {
            font-size: 13px; font-weight: 700; color: #374151; margin-top: 4px;
        }

        /* ── SUMMARY STATS ───────────────────────── */
        .summary-bar {
            width: 100%; border-collapse: collapse; margin-bottom: 10px;
        }
        .summary-bar td {
            border: 2px solid #d1d5db;
            text-align: center; padding: 12px 8px; background: #f9fafb;
        }
        .stat-number {
            font-size: 26px; font-weight: 900; color: #0d5b47;
        }
        .stat-label {
            font-size: 10px; font-weight: 900; color: #6b7280;
            text-transform: uppercase; margin-top: 2px; letter-spacing: .5px;
        }

        /* ── GENERIC TABLE ────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #0d5b47; color: #ffffff;
            border: 1px solid #0a4535;
            padding: 8px 5px;
            font-size: 10.5px; font-weight: 900; text-transform: uppercase;
            letter-spacing: .3px;
        }
        td {
            border: 1px solid #9ca3af;
            padding: 6px 5px;
            text-align: center; font-size: 11px; font-weight: 700;
        }
        tbody tr:nth-child(even) td { background: #f3f4f6; }
        tbody tr:nth-child(odd)  td { background: #ffffff; }

        .text-left { text-align: left !important; }
        .font-bold { font-weight: 900; }

        /* Top 10 rank highlights */
        .rank-gold td   { background: #fef9c3 !important; }
        .rank-silver td { background: #f1f5f9 !important; }
        .rank-bronze td { background: #fff7ed !important; }

        /* Grade cell colours — bright for notice-board readability */
        .grade-A { background-color: #bbf7d0 !important; color: #14532d !important; font-weight: 900 !important; }
        .grade-B { background-color: #bae6fd !important; color: #0c4a6e !important; font-weight: 900 !important; }
        .grade-C { background-color: #fef08a !important; color: #713f12 !important; font-weight: 900 !important; }
        .grade-D { background-color: #fed7aa !important; color: #7c2d12 !important; font-weight: 900 !important; }
        .grade-F { background-color: #fecaca !important; color: #7f1d1d !important; font-weight: 900 !important; }
        .grade-- { background-color: #f3f4f6 !important; }

        /* Division badge */
        .div-badge {
            display: inline-block; padding: 2px 8px;
            background: #dcfce7; border: 1px solid #86efac;
            color: #14532d; font-size: 10px; font-weight: 900;
            border-radius: 3px;
        }

        /* ── THREE-COLUMN MINI STATS ─────────────── */
        .triple {
            width: 100%; border-collapse: separate;
            border-spacing: 8px; margin-bottom: 10px;
        }
        .triple td { vertical-align: top; border: none; padding: 0; }
        .mini-box {
            border: 2px solid #0d5b47;
            border-radius: 4px; overflow: hidden;
        }
        .mini-title {
            background: #0d5b47; color: #ffffff;
            font-size: 11px; font-weight: 900; padding: 7px;
            text-align: center; text-transform: uppercase; letter-spacing: .5px;
        }

        /* ── SECTION TITLE ───────────────────────── */
        .section-title {
            background: #0d5b47; color: #ffffff;
            padding: 9px 12px;
            font-size: 13px; font-weight: 900;
            margin: 12px 0 6px 0;
            text-transform: uppercase; letter-spacing: 1px;
            text-align: center; border-left: 6px solid #f59e0b;
        }

        /* ── SUBJECT TABLE ───────────────────────── */
        .subject-table th { font-size: 10px; }
        .subject-table td { font-size: 10.5px; }
        .subject-table .best-row  td { background: #bbf7d0 !important; color: #14532d !important; font-weight: 900 !important; }
        .subject-table .worst-row td { background: #fecaca !important; color: #7f1d1d !important; font-weight: 900 !important; }

        /* ── COMPLETE RESULTS TABLE ──────────────── */
        .results-table th { font-size: 9.5px; padding: 7px 3px; }
        .results-table td { font-size: 10px; padding: 5px 3px; }
        .results-table .name-cell {
            font-size: 11px; font-weight: 900; text-align: left;
        }
        .results-table .rank-cell {
            font-size: 12px; font-weight: 900; color: #0d5b47;
        }

        /* ── TOP 10 TABLE ────────────────────────── */
        .top10-table th { font-size: 11px; padding: 9px 6px; }
        .top10-table td { font-size: 11.5px; padding: 7px 5px; }
        .top10-table .name-td { font-size: 13px; font-weight: 900; text-align: left; }
        .top10-table .pos-td  { font-size: 18px; font-weight: 900; color: #0d5b47; }

        /* ── FOOTER ──────────────────────────────── */
        .footer {
            margin-top: 14px; border-top: 2px solid #d1d5db;
            padding-top: 8px; text-align: center;
            font-size: 10px; font-weight: 700; color: #6b7280;
        }

        tr  { page-break-inside: avoid; }
        thead { display: table-header-group; }
    </style>
</head>
<body>

@php
    $school = $school ?? (object)[
        'name' => 'MEMA ASEP Learning Centre',
        'motto'=> 'Maadili, Elimu, Maendeleo, Amani',
        'address' => 'Kisarawe, Pwani',
        'phone' => '+255', 'email' => 'info@mema.or.tz', 'website' => 'www.mema.ac.tz',
        'logo_left' => 'vendor/adminlte/dist/img/MEMA.png',
        'logo_right' => 'vendor/adminlte/dist/img/schoolms-icon.png',
    ];
    $grades = $grades ?? collect([
        (object)['name'=>'A','min_mark'=>75,'max_mark'=>100],
        (object)['name'=>'B','min_mark'=>60,'max_mark'=>74],
        (object)['name'=>'C','min_mark'=>50,'max_mark'=>59],
        (object)['name'=>'D','min_mark'=>40,'max_mark'=>49],
        (object)['name'=>'F','min_mark'=>0,'max_mark'=>39],
    ]);

    $logoLeftPath  = public_path($school->logo_left  ?? 'vendor/adminlte/dist/img/MEMA.png');
    $logoRightPath = public_path($school->logo_right ?? 'vendor/adminlte/dist/img/schoolms-icon.png');
    $logoLeft  = file_exists($logoLeftPath)  ? base64_encode(file_get_contents($logoLeftPath))  : '';
    $logoRight = file_exists($logoRightPath) ? base64_encode(file_get_contents($logoRightPath)) : '';

    $studentsCollection = collect($studentsData);
    $totalStudents  = $studentsCollection->count();
    $eligibleColl   = $studentsCollection->where('eligible_for_rank', true);
    $eligibleCount  = $eligibleColl->count();
    $divisionCounts = $eligibleColl->groupBy('division')->map->count();
    $rankedStudents = $eligibleColl->filter(fn($s) => is_numeric($s['position'] ?? null))->values();
    $topTen         = $rankedStudents->take(10);
    $topPerformer   = $rankedStudents->first();
    $topAverage     = $topPerformer['average_mark'] ?? 0;

    $gradeCounts = [];
    foreach ($grades as $g) { $gradeCounts[$g->name] = 0; }
    foreach ($studentsData as $row) {
        foreach ($row['subjectsData'] as $sub) {
            $grade = $sub['grade'] ?? '';
            if (isset($gradeCounts[$grade])) $gradeCounts[$grade]++;
        }
    }
    $totalGrades = array_sum($gradeCounts) ?: 1;

    $boys  = $studentsCollection->filter(fn($s) => ($s['student']->gender ?? '') === 'male');
    $girls = $studentsCollection->filter(fn($s) => ($s['student']->gender ?? '') === 'female');
    $boysCount   = $boys->count();
    $girlsCount  = $girls->count();
    $boysAvgGPA  = $boysCount  ? number_format($boys->avg('gpa'),  2) : 'N/A';
    $girlsAvgGPA = $girlsCount ? number_format($girls->avg('gpa'), 2) : 'N/A';
    $boysDivCount  = $boys->where('eligible_for_rank', true)->groupBy('division')->map->count();
    $girlsDivCount = $girls->where('eligible_for_rank', true)->groupBy('division')->map->count();

    $subjectStats = [];
    foreach ($studentsData as $row) {
        foreach ($row['subjectsData'] as $sub) {
            $name = $sub['name'];
            if (!isset($subjectStats[$name])) {
                $subjectStats[$name] = ['marks' => [], 'grade_counts' => []];
                foreach ($grades as $g) { $subjectStats[$name]['grade_counts'][$g->name] = 0; }
            }
            if (is_numeric($sub['mark'] ?? null)) {
                $subjectStats[$name]['marks'][] = floatval($sub['mark']);
                $grade = $sub['grade'] ?? '';
                if (isset($subjectStats[$name]['grade_counts'][$grade])) {
                    $subjectStats[$name]['grade_counts'][$grade]++;
                }
            }
        }
    }
    $subjectRows = [];
    foreach ($subjectStats as $name => $st) {
        $c = count($st['marks']);
        $subjectRows[$name] = [
            'avg_mark'    => $c ? round(array_sum($st['marks']) / $c, 2) : 0,
            'grade_counts' => $st['grade_counts'],
        ];
    }
    $rankedSubjects = collect($subjectRows)->sortByDesc('avg_mark');
    $sortedSubjects = $rankedSubjects->keys()->values();
    $bestSubject    = $rankedSubjects->keys()->first();
    $worstSubject   = $rankedSubjects->keys()->last();

    $overallAvgMark = $totalStudents ? round($studentsCollection->avg('average_mark'), 2) : 0;
    $overallAvgGPA  = $eligibleCount ? round($eligibleColl->avg('gpa'), 2) : 0;

    $alphaStudents = $studentsCollection
        ->sortBy(fn($d) => $d['student']->first_name . ' ' . $d['student']->last_name)
        ->values();
@endphp

{{-- ═══════════════════════════════════════════════════════════
     HEADER
═══════════════════════════════════════════════════════════ --}}
<table class="header">
    <tr>
        <td class="logo" style="background:rgba(255,255,255,.08);border-right:1px solid rgba(255,255,255,.2)">
            @if($logoLeft)
            <img src="data:image/png;base64,{{ $logoLeft }}" alt="Logo">
            @endif
        </td>
        <td style="text-align:center">
            <div class="school-name">{{ $school->name ?? 'MEMA ASEP Learning Centre' }}</div>
            @if(!empty($school->motto))
            <div style="font-size:12px;color:#a7f3d0;font-style:italic;margin-top:3px">"{{ $school->motto }}"</div>
            @endif
            <div class="exam-title">{{ strtoupper($exam->name ?? 'EXAMINATION') }} — RESULTS</div>
            <div class="subtitle">OFFICIAL ACADEMIC PERFORMANCE REPORT &nbsp;|&nbsp; POSTED: {{ \Carbon\Carbon::now()->format('d F Y') }}</div>
        </td>
        <td class="logo" style="background:rgba(255,255,255,.08);border-left:1px solid rgba(255,255,255,.2)">
            @if($logoRight)
            <img src="data:image/png;base64,{{ $logoRight }}" alt="Logo">
            @endif
        </td>
    </tr>
</table>

{{-- ═══ INFO BAR ═══ --}}
<table class="info-bar">
    <tr>
        <td>CLASS:&nbsp; {{ $class->name ?? 'N/A' }}</td>
        <td>SESSION:&nbsp; {{ $academicSession->name ?? 'N/A' }}</td>
        <td>TOTAL STUDENTS:&nbsp; {{ $totalStudents }}</td>
        <td>ELIGIBLE:&nbsp; {{ $eligibleCount }}</td>
        <td>DATE GENERATED:&nbsp; {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}</td>
    </tr>
</table>

{{-- ═══ CHAMPION BANNER ═══ --}}
@if($topPerformer)
<table class="champion-box">
    <tr>
        <td class="champion-trophy">🏆</td>
        <td>
            <div class="champion-label">Best Overall Student</div>
            <div class="champion-name">
                {{ $topPerformer['student']->first_name }} {{ $topPerformer['student']->last_name }}
            </div>
            <div class="champion-stats">
                Average: <strong>{{ number_format($topAverage, 2) }}%</strong>
                &nbsp;|&nbsp; GPA: <strong>{{ number_format($topPerformer['gpa'] ?? 0, 2) }}</strong>
                &nbsp;|&nbsp; Division: <strong>{{ $topPerformer['division'] }}</strong>
                &nbsp;|&nbsp; Points: <strong>{{ $topPerformer['total_points'] }}</strong>
                &nbsp;|&nbsp; Position: <strong>1st</strong>
            </div>
        </td>
    </tr>
</table>
@endif

{{-- ═══ SUMMARY STATS BAR ═══ --}}
<table class="summary-bar">
    <tr>
        <td><div class="stat-number">{{ $totalStudents }}</div><div class="stat-label">Total Students</div></td>
        <td><div class="stat-number">{{ $overallAvgMark }}%</div><div class="stat-label">Class Average</div></td>
        <td><div class="stat-number">{{ $overallAvgGPA }}</div><div class="stat-label">Class GPA</div></td>
        <td><div class="stat-number">{{ $divisionCounts['I'] ?? 0 }}</div><div class="stat-label">Division I</div></td>
        <td><div class="stat-number">{{ $divisionCounts['II'] ?? 0 }}</div><div class="stat-label">Division II</div></td>
        <td><div class="stat-number">{{ $divisionCounts['III'] ?? 0 }}</div><div class="stat-label">Division III</div></td>
        <td><div class="stat-number">{{ $divisionCounts['IV'] ?? 0 }}</div><div class="stat-label">Division IV</div></td>
        <td><div class="stat-number">{{ $studentsCollection->where('eligible_for_rank', false)->count() }}</div><div class="stat-label">Incomplete</div></td>
    </tr>
</table>

{{-- ═══ THREE-COLUMN MINI STATS ═══ --}}
<table class="triple">
    <tr>
        {{-- Division Summary --}}
        <td width="33.33%">
            <div class="mini-box">
                <div class="mini-title">Division Summary</div>
                <table>
                    <thead>
                        <tr><th>DIV</th><th>TOTAL</th><th>%</th><th>MALE</th><th>FEMALE</th></tr>
                    </thead>
                    <tbody>
                        @php $totalDiv = max($eligibleCount, 1); @endphp
                        @foreach(['I','II','III','IV','0'] as $div)
                            @php
                                $cnt = $divisionCounts[$div] ?? 0;
                                $pct = round(($cnt / $totalDiv) * 100, 1);
                                $mc  = $boysDivCount[$div]  ?? 0;
                                $fc  = $girlsDivCount[$div] ?? 0;
                            @endphp
                            <tr>
                                <td class="font-bold" style="font-size:12px">{{ $div }}</td>
                                <td>{{ $cnt }}</td><td>{{ $pct }}%</td><td>{{ $mc }}</td><td>{{ $fc }}</td>
                            </tr>
                        @endforeach
                        @php
                            $incCnt = $studentsCollection->where('eligible_for_rank', false)->count();
                            $incPct = round(($incCnt / max($totalStudents,1)) * 100, 1);
                            $incM   = $boys->where('eligible_for_rank', false)->count();
                            $incF   = $girls->where('eligible_for_rank', false)->count();
                        @endphp
                        <tr>
                            <td class="font-bold" style="color:#b91c1c;font-size:12px">–</td>
                            <td>{{ $incCnt }}</td><td>{{ $incPct }}%</td><td>{{ $incM }}</td><td>{{ $incF }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </td>

        {{-- Grade Distribution --}}
        <td width="33.33%">
            <div class="mini-box">
                <div class="mini-title">Grade Distribution</div>
                <table>
                    <thead>
                        <tr><th>GRADE</th><th>COUNT</th><th>%</th><th>RANGE</th></tr>
                    </thead>
                    <tbody>
                        @foreach($grades as $g)
                            @php
                                $cnt = $gradeCounts[$g->name] ?? 0;
                                $pct = round(($cnt / $totalGrades) * 100, 1);
                            @endphp
                            <tr>
                                <td class="font-bold grade-{{ $g->name }}" style="font-size:13px">{{ $g->name }}</td>
                                <td>{{ $cnt }}</td>
                                <td>{{ $pct }}%</td>
                                <td>{{ $g->min_mark }}–{{ $g->max_mark }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </td>

        {{-- Gender Summary --}}
        <td width="33.33%">
            <div class="mini-box">
                <div class="mini-title">Gender Performance</div>
                <table>
                    <thead>
                        <tr>
                            <th>Gender</th><th>Total</th>
                            @foreach(['I','II','III','IV','0','–'] as $div)
                                <th>{{ $div === '–' ? 'Inc' : 'Div '.$div }}</th>
                            @endforeach
                            <th>Avg GPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([['label'=>'Male','coll'=>$boys,'divCnt'=>$boysDivCount,'gpa'=>$boysAvgGPA,'total'=>$boysCount],['label'=>'Female','coll'=>$girls,'divCnt'=>$girlsDivCount,'gpa'=>$girlsAvgGPA,'total'=>$girlsCount]] as $row)
                        <tr>
                            <td class="font-bold">{{ $row['label'] }}</td>
                            <td>{{ $row['total'] }}</td>
                            @foreach(['I','II','III','IV','0','–'] as $div)
                                <td>
                                    @if($div === '–')
                                        {{ $row['coll']->where('eligible_for_rank', false)->count() }}
                                    @else
                                        {{ $row['divCnt'][$div] ?? 0 }}
                                    @endif
                                </td>
                            @endforeach
                            <td><strong>{{ $row['gpa'] }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
</table>

{{-- ═══════════════════════════════════════════════════════════
     TOP 10 STUDENTS
═══════════════════════════════════════════════════════════ --}}
<div class="section-title">🎖 TOP 10 STUDENTS</div>
<table class="top10-table">
    <thead>
        <tr>
            <th style="width:50px">RANK</th>
            <th class="text-left">STUDENT NAME</th>
            <th>AVERAGE MARK</th>
            <th>TOTAL POINTS</th>
            <th>GPA</th>
            <th>DIVISION</th>
        </tr>
    </thead>
    <tbody>
        @forelse($topTen as $student)
            @php
                $rowClass = match($loop->index) { 0 => 'rank-gold', 1 => 'rank-silver', 2 => 'rank-bronze', default => '' };
                $medal    = match($loop->index) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => '' };
                $pos      = $student['position'] ?? '-';
            @endphp
            <tr class="{{ $rowClass }}">
                <td class="pos-td" style="font-size:18px;font-weight:900;color:#0d5b47">
                    {{ $medal }} {{ $pos }}
                </td>
                <td class="name-td" style="font-size:14px;font-weight:900;text-align:left">
                    {{ $student['student']->first_name }} {{ $student['student']->last_name }}
                </td>
                <td style="font-size:14px;font-weight:900">{{ number_format($student['average_mark'], 2) }}%</td>
                <td style="font-size:13px;font-weight:900">{{ $student['total_points'] }}</td>
                <td style="font-size:13px;font-weight:900">{{ number_format($student['gpa'], 2) }}</td>
                <td>
                    <span class="div-badge" style="font-size:12px;padding:3px 12px">
                        {{ $student['division'] }}
                    </span>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;color:#6b7280">No eligible students found.</td></tr>
        @endforelse
    </tbody>
</table>

{{-- ═══════════════════════════════════════════════════════════
     SUBJECT PERFORMANCE
═══════════════════════════════════════════════════════════ --}}
<div class="section-title">📚 SUBJECT PERFORMANCE ANALYSIS</div>
<table class="subject-table">
    <thead>
        <tr>
            <th style="width:35px">#</th>
            <th class="text-left">SUBJECT</th>
            <th>CLASS AVG</th>
            @foreach($grades as $g)
                <th>{{ $g->name }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($rankedSubjects as $subject => $stats)
            @php
                $rowClass = '';
                if ($subject === $bestSubject)  $rowClass = 'best-row';
                if ($subject === $worstSubject) $rowClass = 'worst-row';
            @endphp
            <tr class="{{ $rowClass }}">
                <td style="font-size:11px;font-weight:900">{{ $loop->iteration }}</td>
                <td class="text-left font-bold" style="font-size:11.5px">
                    {{ $subject }}
                    @if($subject === $bestSubject)  &nbsp;★ Best @endif
                    @if($subject === $worstSubject) &nbsp;▼ Lowest @endif
                </td>
                <td style="font-size:12px;font-weight:900">{{ $stats['avg_mark'] }}%</td>
                @foreach($grades as $g)
                    <td style="font-size:11px">{{ $stats['grade_counts'][$g->name] ?? 0 }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

{{-- ═══════════════════════════════════════════════════════════
     COMPLETE STUDENT RESULTS
═══════════════════════════════════════════════════════════ --}}
<div class="section-title">📋 COMPLETE STUDENT RESULTS</div>
<table class="results-table">
    <thead>
        <tr>
            <th style="width:28px">#</th>
            <th class="text-left" style="min-width:120px">STUDENT NAME</th>
            @foreach($sortedSubjects as $subjectName)
                <th style="font-size:9px">{{ $subjectName }}</th>
            @endforeach
            <th>AVG %</th>
            <th>PTS</th>
            <th>GPA</th>
            <th>DIV</th>
            <th>POS</th>
        </tr>
    </thead>
    <tbody>
        @foreach($alphaStudents as $i => $data)
            @php
                $isInc  = !($data['eligible_for_rank'] ?? true);
                $subMap = collect($data['subjectsData'])->keyBy('name');
                $avg    = $isInc ? '–' : number_format($data['average_mark'] ?? 0, 1) . '%';
                $pts    = $isInc ? '–' : ($data['total_points'] ?? '–');
                $gpa    = $isInc ? '–' : number_format($data['gpa'] ?? 0, 2);
                $div    = $data['division'] ?? '-';
                $rawPos = $data['position'] ?? null;
                $pos    = $isInc ? '–' : (is_numeric($rawPos) ? $rawPos : '–');
            @endphp
            <tr>
                <td style="font-size:10px;color:#6b7280">{{ $i + 1 }}</td>
                <td class="name-cell text-left">
                    {{ $data['student']->first_name }} {{ $data['student']->last_name }}
                    @if($isInc)<br><span style="font-size:8px;color:#dc2626;font-weight:700">(Incomplete)</span>@endif
                </td>
                @foreach($sortedSubjects as $subjName)
                    @php
                        $sd    = $subMap->get($subjName);
                        $mark  = $sd ? ($sd['mark']  ?? null) : null;
                        $grade = $sd ? ($sd['grade'] ?? '-')  : '-';
                        $gc    = in_array($grade, $grades->pluck('name')->toArray()) ? 'grade-'.$grade : 'grade--';
                    @endphp
                    <td class="{{ $gc }}">
                        @if($mark !== null)
                            <strong>{{ number_format($mark, 0) }}</strong>
                            <br><span style="font-size:8.5px;font-weight:900">({{ $grade }})</span>
                        @else
                            <span style="color:#9ca3af">—</span>
                        @endif
                    </td>
                @endforeach
                <td style="font-size:11px;font-weight:900">{{ $avg }}</td>
                <td style="font-size:10.5px;font-weight:900">{{ $pts }}</td>
                <td style="font-size:10.5px;font-weight:900">{{ $gpa }}</td>
                <td>
                    <span class="div-badge">{{ $div }}</span>
                </td>
                <td class="rank-cell" style="font-size:13px;font-weight:900;color:#0d5b47">{{ $pos }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- ═══ FOOTER ═══ --}}
<div class="footer">
    <strong>{{ $school->name ?? 'MEMA ASEP Learning Centre' }}</strong>
    &nbsp;|&nbsp; OFFICIAL RESULTS REPORT
    &nbsp;|&nbsp; EXAM: {{ strtoupper($exam->name ?? '') }}
    &nbsp;|&nbsp; GENERATED: {{ \Carbon\Carbon::now()->format('d F Y, H:i') }}
    &nbsp;|&nbsp; AUTHORISED SCHOOL DOCUMENT — DO NOT ALTER
</div>

</body>
</html>
