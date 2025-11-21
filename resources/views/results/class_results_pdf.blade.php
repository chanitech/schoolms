<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $class->name ?? 'Class' }} - {{ $exam->name ?? 'Exam' }} Results</title>
    <style>
        @page { margin: 20px; }
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.3; }
        h1 { font-size: 28px; margin: 5px 0; }
        h2 { font-size: 24px; margin: 5px 0; }
        h3 { font-size: 18px; margin: 5px 0; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: auto; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background-color: #f0f0f0; font-weight: bold; }
        tr { page-break-inside: avoid; page-break-after: auto; }

        .header { text-align: center; margin-bottom: 15px; }
        .school-logo { height: 120px; margin-bottom: 5px; }
        .table-success { background-color: #d4edda; }
        .table-danger { background-color: #f8d7da; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .watermark {
            position: fixed;
            top: 40%;
            left: 25%;
            width: 50%;
            text-align: center;
            font-size: 100px;
            color: rgba(200, 200, 200, 0.2);
            transform: rotate(-45deg);
            z-index: -1000;
        }
    </style>
</head>
<body>
<div class="watermark">CONFIDENTIAL</div>

<div class="header">
    
    <h1>{{ $school->name ?? 'School Name' }}</h1>
    <h3>{{ $school->address ?? '' }} | {{ $school->phone ?? '' }}</h3>
    <h2>{{ $class->name ?? '' }} - {{ $exam->name ?? '' }} Results</h2>
</div>

@php
    $gradeColors = ['A'=>'#d4edda','B'=>'#d1ecf1','C'=>'#fff3cd','D'=>'#ffe5b4','F'=>'#f8d7da'];

    // Division counts
    $divisionCounts = collect($studentsData)->groupBy('division')->map->count();

    // Subject averages
    $allSubjects = collect($studentsData)->flatMap(fn($s) => $s['subjectsData'])->groupBy('subject');
    $subjectAverages = $allSubjects->map(fn($subs, $name) => [
        'average_mark' => number_format(collect($subs)->avg(fn($x)=>is_numeric($x['mark'])?$x['mark']:0), 2),
        'average_gpa'  => number_format(collect($subs)->avg(fn($x)=>floatval($x['point'])), 2),
        'type' => $subs->first()['type'] ?? '—'
    ])->sortKeys();

    $bestSubject = $subjectAverages->sortByDesc('average_mark')->keys()->first();
    $worstSubject = $subjectAverages->sortBy('average_mark')->keys()->first();
@endphp

{{-- ================= Student Results Table ================= --}}
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Student Name</th>
            @foreach($subjects as $subject)
                <th>{{ $subject->name }}</th>
            @endforeach
            <th>Total Marks (Best 7)</th>
            <th>Average (Best 7)</th>
            <th>Total Points</th>
            <th>GPA</th>
            <th>Division</th>
            <th>Position</th>
        </tr>
    </thead>
    <tbody>
        @foreach($studentsData as $i => $data)
            @php
                $marksCollection = collect($data['subjectsData']);

                // Separate core and elective subjects, only numeric marks
                $coreSubjects = $marksCollection->where('type','core')
                    ->filter(fn($s)=>is_numeric($s['mark']))
                    ->sortByDesc(fn($s)=>floatval($s['mark']));

                $electives = $marksCollection->where('type','elective')
                    ->filter(fn($s)=>is_numeric($s['mark']))
                    ->sortByDesc(fn($s)=>floatval($s['mark']));

                // Take Best 7: core first, then electives
                $bestSubjects = $coreSubjects->take(7);
                if($bestSubjects->count() < 7){
                    $bestSubjects = $bestSubjects->merge($electives->take(7 - $bestSubjects->count()));
                }

                $totalMarks = $bestSubjects->sum(fn($s)=>floatval($s['mark']));
                $average = $bestSubjects->count() ? number_format($totalMarks/$bestSubjects->count(),2) : '0.00';
                $totalPoints = $bestSubjects->sum(fn($s)=>floatval($s['point']));
            @endphp
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $data['student']->full_name }}</td>
                @foreach($subjects as $subject)
                    @php
                        $sub = $data['subjectsData'][$subject->id] ?? ['mark'=>'-','grade'=>'-','point'=>0];
                        $bg = $gradeColors[$sub['grade']] ?? '#fff';
                    @endphp
                    <td style="background-color: {{ $bg }}">
                        {{ is_numeric($sub['mark']) ? floatval($sub['mark']) : '-' }} ({{ $sub['grade'] }})
                    </td>
                @endforeach
                <td>{{ $totalMarks }}</td>
                <td>{{ $average }}</td>
                <td>{{ $totalPoints }}</td>
                <td>{{ number_format($data['gpa'] ?? 0,2) }}</td>
                <td>{{ $data['division'] ?? 'Unknown' }}</td>
                <td>{{ $data['position'] ?? ($i+1) }}/{{ count($studentsData) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- ================= Division Summary ================= --}}
<h3>Division Summary</h3>
<table>
    <thead>
        <tr><th>Division</th><th>Number of Students</th></tr>
    </thead>
    <tbody>
        <tr><td>I</td><td>{{ $divisionCounts['I'] ?? 0 }}</td></tr>
        <tr><td>II</td><td>{{ $divisionCounts['II'] ?? 0 }}</td></tr>
        <tr><td>III</td><td>{{ $divisionCounts['III'] ?? 0 }}</td></tr>
        <tr><td>IV</td><td>{{ $divisionCounts['IV'] ?? 0 }}</td></tr>
        <tr><td>0</td><td>{{ $divisionCounts['0'] ?? 0 }}</td></tr>
    </tbody>
</table>

{{-- ================= Subject Performance Summary ================= --}}
<h3>Subject Performance Summary</h3>
<table>
    <thead>
        <tr><th>Subject</th><th>Type</th><th>Average Mark</th><th>Average GPA</th></tr>
    </thead>
    <tbody>
        @foreach($subjectAverages as $subject => $stats)
            @php
                $rowClass = $subject === $bestSubject ? 'table-success' : ($subject === $worstSubject ? 'table-danger' : '');
            @endphp
            <tr class="{{ $rowClass }}">
                <td>{{ $subject }}</td>
                <td>{{ ucfirst($stats['type']) }}</td>
                <td>{{ $stats['average_mark'] }}</td>
                <td>{{ $stats['average_gpa'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
