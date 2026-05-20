<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $school->name ?? 'School' }} – {{ $exam->name ?? 'Exam' }} Results</title>
    <style>
        @page { size: A3 landscape; margin: 10mm; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            color: #1f2937;
            background: #ffffff;
            line-height: 1.4;
        }

        /* ── HEADER ──────────────────────────────── */
        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background: #f0fdf4;
            border: 2px solid #1F7A63;
        }
        .header td { border: none; padding: 18px; vertical-align: middle; }
        .logo { width: 110px; text-align: center; }
        .logo img { width: 85px; height: 85px; object-fit: contain; }
        .school-name {
            font-size: 34px; font-weight: 900; color: #0d5b47;
            letter-spacing: 1px; text-transform: uppercase;
        }
        .exam-title {
            font-size: 22px; font-weight: 800; color: #1F7A63; margin-top: 6px;
        }
        .subtitle {
            font-size: 13px; color: #4b5563; margin-top: 6px; font-weight: 700;
        }

        /* ── INFO BAR ────────────────────────────── */
        .info-table {
            width: 100%; border-collapse: collapse; margin-bottom: 14px;
        }
        .info-table td {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            padding: 10px; font-size: 13px; font-weight: 800;
            color: #1F7A63; text-align: center;
        }

        /* ── TOP PERFORMER ───────────────────────── */
        .top-box {
            width: 100%; margin-bottom: 16px;
            border: 2px solid #fbbf24;
            background: #fffdf4;
        }
        .top-box td { border: none; padding: 16px; }
        .top-title {
            font-size: 16px; font-weight: 900; color: #92400e;
            text-transform: uppercase; margin-bottom: 8px;
        }
        .top-name {
            font-size: 24px; font-weight: 900; color: #111827; margin-bottom: 8px;
        }
        .top-stats {
            font-size: 13px; font-weight: 700; color: #374151;
        }

        /* ── SUMMARY ─────────────────────────────── */
        .summary {
            width: 100%; border-collapse: collapse; margin-bottom: 16px;
        }
        .summary td {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            text-align: center; padding: 18px 10px;
        }
        .summary-number {
            font-size: 28px; font-weight: 900; color: #0d5b47; margin-bottom: 4px;
        }
        .summary-label {
            font-size: 12px; font-weight: 800; color: #4b5563; text-transform: uppercase;
        }

        /* ── SECTION TITLES ──────────────────────── */
        .section-title {
            background: #1F7A63; color: #ffffff;
            border: none; padding: 10px;
            text-align: center; font-size: 14px; font-weight: 900;
            margin-top: 18px; margin-bottom: 8px; text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── TABLES (generic) ─────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #d1d5db;        /* outer border */
        }
        th {
            background: #1F7A63; color: #ffffff;
            border: 1px solid #115c45; padding: 8px 6px;
            font-size: 11px; font-weight: 900; text-transform: uppercase;
        }
        td {
            border: 1px solid #d1d5db; padding: 7px 5px;
            text-align: center; font-size: 11px; font-weight: 700;
        }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        tbody tr:nth-child(odd)  td { background: #ffffff; }

        .text-left { text-align: left; }
        .font-bold { font-weight: 900; }

        /* Rank colours */
        .rank-1 td { background: #fef9c3 !important; }
        .rank-2 td { background: #f1f5f9 !important; }
        .rank-3 td { background: #fff7ed !important; }

        /* Grade background colours (exactly like web view) */
        .grade-A { background-color: #d4edda !important; }
        .grade-B { background-color: #d1ecf1 !important; }
        .grade-C { background-color: #fff3cd !important; }
        .grade-D { background-color: #ffe5b4 !important; }
        .grade-F { background-color: #f8d7da !important; }
        .grade-- { background-color: #f9fafb !important; }

        .badge {
            display: inline-block; padding: 3px 10px;
            background: #d1fae5; border: 1px solid #a7f3d0;
            color: #064e3b; font-size: 10px; font-weight: 900;
            border-radius: 4px;
        }

        /* Three‑column mini boxes */
        .triple {
            width: 100%; border-collapse: separate;
            border-spacing: 10px; margin-bottom: 12px;
        }
        .triple td { vertical-align: top; border: none; padding: 0; }
        .mini-box {
            border: 1px solid #d1d5db; background: #ffffff;
            border-radius: 6px; overflow: hidden;
        }
        .mini-title {
            background: #1F7A63; color: #ffffff;
            font-size: 12px; font-weight: 900; padding: 8px;
            text-align: center;
        }

        /* ── BOTTOM 10 IMPROVEMENT TABLE ──────────── */
        .improvement-table {
            background: #ecfdf5;
            color: #064e3b;
            font-weight: 900;
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #6ee7b7;
        }
        .improvement-table th {
            background: #a7f3d0;
            color: #064e3b;
            font-weight: 900;
            text-transform: uppercase;
            border: 1px solid #6ee7b7;
        }
        .improvement-table td {
            border: 1px solid #6ee7b7;
            font-weight: 900;
        }

        /* ── SUBJECT PERFORMANCE TABLE WITH BOLD BORDERS ── */
        .subject-table {
            border: 3px solid #0d5b47 !important;
            width: 100%;
            border-collapse: collapse;
        }
        .subject-table th {
            background: #1F7A63;
            color: #fff;
            border: 1px solid #0d5b47;
        }
        .subject-table td {
            border: 1px solid #0d5b47;
            font-weight: 700;
        }
        .best-subject-row td {
            background: #d1fae5 !important;
            font-weight: 900;
        }
        .worst-subject-row td {
            background: #fee2e2 !important;
            font-weight: 900;
        }

        /* Complete results table */
        .results-table th { font-size: 10px; padding: 7px 4px; }
        .results-table td { font-size: 10px; padding: 5px 3px; }

        .footer {
            margin-top: 18px; border-top: 2px solid #d1d5db;
            padding-top: 10px; text-align: center;
            font-size: 11px; font-weight: 700; color: #6b7280;
        }

        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
    </style>
</head>
<body>

@php
    // Fallback if $school or $grades are not passed
    $school = $school ?? (object)[
        'name' => 'MEMA ASEP Learning Centre',
        'motto'=> 'Maadili, Elimu, Maendeleo, Amani',
        'address' => 'Kisarawe, Pwani',
        'phone' => '+255',
        'email' => 'info@mema.or.tz',
        'website' => 'www.mema.ac.tz',
        'logo_left' => 'vendor/adminlte/dist/img/MEMA.png',
        'logo_right' => 'vendor/adminlte/dist/img/MEMA.webp',
    ];
    $grades = $grades ?? collect([
        (object)['name'=>'A','min_mark'=>75,'max_mark'=>100],
        (object)['name'=>'B','min_mark'=>60,'max_mark'=>74],
        (object)['name'=>'C','min_mark'=>50,'max_mark'=>59],
        (object)['name'=>'D','min_mark'=>40,'max_mark'=>49],
        (object)['name'=>'F','min_mark'=>0,'max_mark'=>39],
    ]);

    $logoLeftPath = public_path($school->logo_left ?? 'vendor/adminlte/dist/img/MEMA.png');
    $logoRightPath = public_path($school->logo_right ?? 'vendor/adminlte/dist/img/MEMA.webp');
    $logoLeft = file_exists($logoLeftPath) ? base64_encode(file_get_contents($logoLeftPath)) : '';
    $logoRight = file_exists($logoRightPath) ? base64_encode(file_get_contents($logoRightPath)) : '';

    $studentsCollection = collect($studentsData);
    $totalStudents = $studentsCollection->count();

    $eligibleColl = $studentsCollection->where('eligible_for_rank', true);
    $eligibleCount = $eligibleColl->count();

    $divisionCounts = $eligibleColl->groupBy('division')->map->count();

    $rankedStudents = $eligibleColl->filter(fn($s) => is_numeric($s['position'] ?? null))->values();
    $topTen = $rankedStudents->take(10);

    $bottomTen = $studentsCollection
        ->whereNotIn('student.id', $topTen->pluck('student.id'))
        ->sortByDesc('total_points')
        ->take(10)
        ->sortBy('total_points')
        ->values();

    $topPerformer = $rankedStudents->first();
    $topAverage   = $topPerformer['average_mark'] ?? 0;

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
    $boysCount  = $boys->count();
    $girlsCount = $girls->count();
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
            'avg_mark' => $c ? round(array_sum($st['marks']) / $c, 2) : 0,
            'grade_counts' => $st['grade_counts'],
        ];
    }
    $rankedSubjects = collect($subjectRows)->sortByDesc('avg_mark');
    $sortedSubjects = $rankedSubjects->keys()->values();
    $bestSubject  = $rankedSubjects->keys()->first();
    $worstSubject = $rankedSubjects->keys()->last();

    $overallAvgMark = $totalStudents ? round($studentsCollection->avg('average_mark'), 2) : 0;
    $overallAvgGPA  = $eligibleCount ? round($eligibleColl->avg('gpa'), 2) : 0;
@endphp

<!-- HEADER -->
<table class="header">
    <tr>
        <td class="logo">
            @if($logoLeft)<img src="data:image/png;base64,{{ $logoLeft }}" alt="Logo">@endif
        </td>
        <td style="text-align:center;">
            <div class="school-name">{{ $school->name ?? 'MEMA ASEP Learning Centre' }}</div>
            <div class="exam-title">{{ strtoupper($exam->name ?? 'EXAMINATION') }} RESULTS</div>
            <div class="subtitle">OFFICIAL ACADEMIC PERFORMANCE REPORT</div>
        </td>
        <td class="logo">
            @if($logoRight)<img src="data:image/webp;base64,{{ $logoRight }}" alt="Logo">@endif
        </td>
    </tr>
</table>

<!-- INFO BAR -->
<table class="info-table">
    <tr>
        <td>CLASS: {{ $class->name ?? 'N/A' }}</td>
        <td>SESSION: {{ $academicSession->name ?? 'N/A' }}</td>
        <td>STUDENTS: {{ $totalStudents }}</td>
        <td>DATE: {{ \Carbon\Carbon::now()->format('d M Y') }}</td>
    </tr>
</table>

<!-- TOP PERFORMER -->
@if($topPerformer)
<table class="top-box">
    <tr>
        <td>
            <div class="top-title">BEST OVERALL STUDENT</div>
            <div class="top-name">
                {{ $topPerformer['student']->first_name }} {{ $topPerformer['student']->last_name }}
            </div>
            <div class="top-stats">
                Average: {{ number_format($topAverage, 2) }}% |
                GPA: {{ number_format($topPerformer['gpa'] ?? 0, 2) }} |
                Division: {{ $topPerformer['division'] }} |
                Points: {{ $topPerformer['total_points'] }}
            </div>
        </td>
    </tr>
</table>
@endif

<!-- SUMMARY -->
<table class="summary">
    <tr>
        <td><div class="summary-number">{{ $totalStudents }}</div><div class="summary-label">Total Students</div></td>
        <td><div class="summary-number">{{ $overallAvgMark }}%</div><div class="summary-label">Class Average</div></td>
        <td><div class="summary-number">{{ $overallAvgGPA }}</div><div class="summary-label">Class GPA</div></td>
        <td><div class="summary-number">{{ $divisionCounts['I'] ?? 0 }}</div><div class="summary-label">Division I</div></td>
    </tr>
</table>

<!-- THREE‑COLUMN: Division / Grade / Gender‑Division -->
<table class="triple">
    <tr>
        {{-- DIVISION SUMMARY --}}
        <td width="33.33%">
            <div class="mini-box">
                <div class="mini-title">DIVISION SUMMARY</div>
                <table>
                    <thead>
                        <tr><th>DIV</th><th>COUNT</th><th>%</th><th>M</th><th>F</th></tr>
                    </thead>
                    <tbody>
                        @php $totalDiv = max($eligibleCount, 1); @endphp
                        @foreach(['I','II','III','IV','0'] as $div)
                            @php
                                $cnt = $divisionCounts[$div] ?? 0;
                                $pct = round(($cnt / $totalDiv) * 100, 1);
                                $mc  = $boysDivCount[$div] ?? 0;
                                $fc  = $girlsDivCount[$div] ?? 0;
                            @endphp
                            <tr>
                                <td class="font-bold">{{ $div }}</td>
                                <td>{{ $cnt }}</td><td>{{ $pct }}%</td><td>{{ $mc }}</td><td>{{ $fc }}</td>
                            </tr>
                        @endforeach
                        @php
                            $incCnt = $studentsCollection->where('eligible_for_rank', false)->count();
                            $incPct = round(($incCnt / $totalStudents) * 100, 1);
                            $incM   = $boys->where('eligible_for_rank', false)->count();
                            $incF   = $girls->where('eligible_for_rank', false)->count();
                        @endphp
                        <tr>
                            <td class="font-bold" style="color:#b91c1c;">–</td>
                            <td>{{ $incCnt }}</td><td>{{ $incPct }}%</td><td>{{ $incM }}</td><td>{{ $incF }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </td>

        {{-- GRADE DISTRIBUTION --}}
        <td width="33.33%">
            <div class="mini-box">
                <div class="mini-title">GRADE DISTRIBUTION</div>
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
                                <td class="font-bold grade-{{ $g->name }}">{{ $g->name }}</td>
                                <td>{{ $cnt }}</td><td>{{ $pct }}%</td>
                                <td>{{ $g->min_mark }}–{{ $g->max_mark }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </td>

        {{-- GENDER SUMMARY WITH DIVISION BREAKDOWN --}}
        <td width="33.33%">
            <div class="mini-box">
                <div class="mini-title">GENDER SUMMARY</div>
                <table>
                    <thead>
                        <tr>
                            <th>Gender</th><th>Total</th>
                            @foreach(['I','II','III','IV','0','–'] as $div)
                                <th>{{ $div === '–' ? 'Inc' : 'Div '.$div }}</th>
                            @endforeach
                            <th>GPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-bold">Male</td>
                            <td>{{ $boysCount }}</td>
                            @foreach(['I','II','III','IV','0','–'] as $div)
                                <td>
                                    @php
                                        if ($div === '–') {
                                            $val = $boys->where('eligible_for_rank', false)->count();
                                        } else {
                                            $val = $boysDivCount[$div] ?? 0;
                                        }
                                    @endphp
                                    {{ $val }}
                                </td>
                            @endforeach
                            <td>{{ $boysAvgGPA }}</td>
                        </tr>
                        <tr>
                            <td class="font-bold">Female</td>
                            <td>{{ $girlsCount }}</td>
                            @foreach(['I','II','III','IV','0','–'] as $div)
                                <td>
                                    @php
                                        if ($div === '–') {
                                            $val = $girls->where('eligible_for_rank', false)->count();
                                        } else {
                                            $val = $girlsDivCount[$div] ?? 0;
                                        }
                                    @endphp
                                    {{ $val }}
                                </td>
                            @endforeach
                            <td>{{ $girlsAvgGPA }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
</table>

<!-- TOP 10 -->
<div class="section-title">TOP 10 STUDENTS</div>
<table>
    <thead>
        <tr><th>RANK</th><th>STUDENT NAME</th><th>AVERAGE</th><th>POINTS</th><th>GPA</th><th>DIVISION</th></tr>
    </thead>
    <tbody>
        @forelse($topTen as $student)
            @php
                $rowClass = match($loop->index) { 0 => 'rank-1', 1 => 'rank-2', 2 => 'rank-3', default => '' };
                $pos = $student['position'] ?? '-';
            @endphp
            <tr class="{{ $rowClass }}">
                <td>{{ $pos }}</td>
                <td class="text-left font-bold">{{ $student['student']->first_name }} {{ $student['student']->last_name }}</td>
                <td>{{ number_format($student['average_mark'], 2) }}%</td>
                <td>{{ $student['total_points'] }}</td>
                <td>{{ number_format($student['gpa'], 2) }}</td>
                <td><span class="badge">{{ $student['division'] }}</span></td>
            </tr>
        @empty
            <tr><td colspan="6">No eligible students found.</td></tr>
        @endforelse
    </tbody>
</table>

<!-- BOTTOM 10 (no overlap, light green) -->
<div class="section-title">STUDENTS NEEDING IMPROVEMENT (BOTTOM 10)</div>
<table class="improvement-table">
    <thead>
        <tr><th>RANK</th><th>STUDENT NAME</th><th>AVERAGE</th><th>POINTS</th><th>GPA</th><th>DIVISION</th></tr>
    </thead>
    <tbody>
        @forelse($bottomTen as $student)
            @php
                $pos = is_numeric($student['position'] ?? null) ? $student['position'] : '–';
            @endphp
            <tr>
                <td>{{ $pos }}</td>
                <td class="text-left">{{ $student['student']->first_name }} {{ $student['student']->last_name }}</td>
                <td>{{ number_format($student['average_mark'], 2) }}%</td>
                <td>{{ $student['total_points'] }}</td>
                <td>{{ number_format($student['gpa'], 2) }}</td>
                <td><span class="badge">{{ $student['division'] }}</span></td>
            </tr>
        @empty
            <tr><td colspan="6">All students are in the top 10 or no data available.</td></tr>
        @endforelse
    </tbody>
</table>

<!-- SUBJECT PERFORMANCE (all subjects, best green, worst red, bold borders) -->
<div class="section-title">SUBJECT PERFORMANCE</div>
<table class="subject-table">
    <thead>
        <tr>
            <th>RANK</th><th>SUBJECT</th><th>AVG %</th>
            @foreach($grades as $g)
                <th>{{ $g->name }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($rankedSubjects as $subject => $stats)
            @php
                $rowClass = '';
                if ($subject === $bestSubject) {
                    $rowClass = 'best-subject-row';
                } elseif ($subject === $worstSubject) {
                    $rowClass = 'worst-subject-row';
                }
            @endphp
            <tr class="{{ $rowClass }}">
                <td>{{ $loop->iteration }}</td>
                <td class="text-left font-bold">{{ $subject }}</td>
                <td><strong>{{ $stats['avg_mark'] }}%</strong></td>
                @foreach($grades as $g)
                    <td>{{ $stats['grade_counts'][$g->name] ?? 0 }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

<!-- COMPLETE STUDENT RESULTS (with per‑cell grade colours) -->
<div class="section-title">COMPLETE STUDENT RESULTS</div>
<table class="results-table">
    <thead>
        <tr>
            <th>#</th><th>STUDENT</th>
            @foreach($sortedSubjects as $subjectName)
                <th>{{ $subjectName }}</th>
            @endforeach
            <th>AVG</th><th>PTS</th><th>GPA</th><th>DIV</th><th>POS</th>
        </tr>
    </thead>
    <tbody>
        @php
            $alphaStudents = $studentsCollection
                ->sortBy(fn($d) => $d['student']->first_name . ' ' . $d['student']->last_name)
                ->values();
        @endphp
        @foreach($alphaStudents as $i => $data)
            @php
                $isInc   = !($data['eligible_for_rank'] ?? true);
                $subMap  = collect($data['subjectsData'])->keyBy('name');
                $avg     = $isInc ? '–' : number_format($data['average_mark'] ?? 0, 2) . '%';
                $pts     = $isInc ? '–' : ($data['total_points'] ?? '–');
                $gpa     = $isInc ? '–' : number_format($data['gpa'] ?? 0, 2);
                $div     = $data['division'] ?? '-';
                $rawPos  = $data['position'] ?? null;
                $pos     = $isInc ? '–' : (is_numeric($rawPos) ? $rawPos . '/' . $totalStudents : '–');
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-left font-bold">
                    {{ $data['student']->first_name }} {{ $data['student']->last_name }}
                    @if($isInc) <span style="font-size:8px; color:#b91c1c;">(Inc)</span> @endif
                </td>
                @foreach($sortedSubjects as $subjName)
                    @php
                        $sd    = $subMap->get($subjName);
                        $mark  = $sd ? ($sd['mark'] ?? null) : null;
                        $grade = $sd ? ($sd['grade'] ?? '-') : '-';
                        $gradeClass = in_array($grade, $grades->pluck('name')->toArray()) ? 'grade-'.$grade : 'grade--';
                    @endphp
                    <td class="{{ $gradeClass }}">
                        {{ $mark !== null ? number_format($mark, 0) : '—' }}
                        <small style="color:#444;">({{ $grade }})</small>
                    </td>
                @endforeach
                <td>{{ $avg }}</td>
                <td>{{ $pts }}</td>
                <td>{{ $gpa }}</td>
                <td><span class="badge">{{ $div }}</span></td>
                <td>{{ $pos }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- FOOTER -->
<div class="footer">
    {{ $school->name ?? 'MEMA ASEP Learning Centre' }} |
    OFFICIAL RESULTS REPORT |
    GENERATED ON {{ \Carbon\Carbon::now()->format('d F Y, H:i') }}
</div>

</body>
</html>